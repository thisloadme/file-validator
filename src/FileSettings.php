<?php

namespace Riyantobudi\Support;

class FileSettings
{
    /**
     * Check if a file uploaded is a valid object
     * 
     * @param object|null $file object file uploaded
     * @param int $minSizeInBytes minimal bytes file uploaded
     * @return boolean
     */
    public static function isDoUploadFile($file, $minSizeInBytes = 20)
    {
        $isInvalidUpload = !($file instanceof \Illuminate\Http\UploadedFile) ||
            $file->getSize() < $minSizeInBytes ||
            empty($file->getClientOriginalExtension());

        return !$isInvalidUpload;
    }

    /**
     * Check if an extension is not allowed to handle
     * 
     * @param string $extension file extension
     * @param array $bunchOfNotAllowed list of not allowed extension
     * @return boolean
     */
    public static function isExtensionNotAllowed($extension, $bunchOfNotAllowed = [])
    {
        $bunchOfNotAllowed = empty($bunchOfNotAllowed) ? 
            ['exe', 'bat', 'cmd', 'vbs', 'js', 'ps1', 'dll', 'hta', 'jar', 'reg', 'scr', 'cpp', 'php', 'aspx', 'sql', 'iso', 'html', 'css', 'swf', 'py', 'rb', 'cgi', 'sh', 'msi', 'ocx', 'sys', 'drv', 'cpl', 'msp', 'ink', 'pif', 'msc', 'mst', 'com'] : 
            $bunchOfNotAllowed;
        return in_array($extension, $bunchOfNotAllowed);
    }

    /**
     * All file type rules
     * 
     * @param string $type file type
     * @return array
     */
    public static function getFileTypeRules($type)
    {
        switch ($type) {
            case 'video':
                return ['file' => 'mimes:mp4,3gp|max:' . (30 * 1024)];
                break;
            case 'audio':
                return ['file' => 'mimes:mp3,wav|max:' . (10 * 1024)];
                break;
            case 'document':
                return ['file' => 'mimes:pdf|max:' . (25 * 1024)];
                break;
            case 'spreadsheet':
                return ['file' => 'mimes:xls,xlsx|max:' . (30 * 1024)];
                break;
            case 'word':
                return ['file' => 'mimes:doc,docx|max:' . (30 * 1024)];
                break;
            case 'powerpoint':
                return ['file' => 'mimes:ppt,pptx|max:' . (30 * 1024)];
                break;
            case 'image':
                return ['file' => 'mimes:jpeg,bmp,png,gif,jpg,webp|max:' . (6.4 * 1024)];
                break;
            case 'text':
                return ['file' => 'mimes:txt,csv|max:' . (25 * 1024)];
                break;
            case 'zip':
                return ['file' => 'mimes:zip|max:' . (30 * 1024)];
                break;
            default:
                return [];
                break;
        }
    }

    /**
     * Merge all rules created into a rule
     * 
     * @param array $bunchOfRules multiple rules
     * @return array
     */
    public static function mergeFileTypeRules($bunchOfRules)
    {
        $mimes = [];
        $max = 0;
        foreach ($bunchOfRules as $val) {
            $explodedRule = Utility::cleanExplode($val['file'], '|');
            foreach ($explodedRule as $rule) {
                $explodedSubRule = Utility::cleanExplode($rule, ':');
                $ruleName = $explodedSubRule[0];
                $valRule = $explodedSubRule[1];
                switch ($ruleName) {
                    case 'mimes':
                        $mimes = array_merge($mimes, Utility::cleanExplode($valRule, ','));
                        break;
                    case 'max':
                        $max = intval($valRule) > $max ? intval($valRule) : $max;
                        break;
                }
            }
        }

        $mimes = implode(',', array_unique($mimes));
        return ['file' => "mimes:$mimes|max:$max"];
    }
}