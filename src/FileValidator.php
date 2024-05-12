<?php

namespace Riyantobudi\Support;

use Illuminate\Support\Facades\Validator;
use ZipArchive;

class FileValidator
{
    /**
     * Validate file eligibility to being uploaded
     * 
     * @param object|null $fileObj file object uploaded
     * @param array $fileType type file uploaded, valin input ['image', 'document, 'video', 'audio', 'spreadsheet', 'word', 'powerpoint', 'text', 'zip']
     * @param boolean $stopExecutable stop executable file
     * @return array
     */
    public static function validateFileEligibilityToUpload($fileObj = null, $fileType = [], $stopExecutable = true)
    {
        try {
            if (!empty($fileObj)) {
                $isUploadFile = FileSettings::isDoUploadFile($fileObj);
                if ($isUploadFile) {
                    $ogFileName = $fileObj->getClientOriginalName();
                    $ogExtension = $fileObj->getClientOriginalExtension();
                    $fileMime = $fileObj->getMimeType();
    
                    if ($stopExecutable && FileSettings::isExtensionNotAllowed($ogExtension)) {
                        return [
                            'code' => 400,
                            'message' => "File '$ogFileName' is not allowed!",
                        ];
                    }
    
                    if (strpos($fileMime, 'json') !== false && $ogExtension != 'json') {
                        return [
                            'code' => 400,
                            'message' => "File '$ogFileName' already broken, please change it to other file!",
                        ];
                    }
    
                    $validateFileObj = self::validateFileTypeRules($fileType, $fileObj);
                    if ($validateFileObj['code'] != 200) {
                        return [
                            'code' => 400,
                            'message' => $validateFileObj['message'],
                        ];
                    }
    
                    $extractEkstensiFromMime = Utility::cleanExplode($fileMime, '/')[1] ?? null;
                    if (strlen($extractEkstensiFromMime) > 5) {
                        $extractEkstensiFromMime = null;
                    }
    
                    $fileExtension = ($extractEkstensiFromMime ?? $ogExtension);
                    if ($fileExtension == 'zip') {
                        $validasiZip = self::validateZipFileContentSecured($fileObj);
                        if ($validasiZip['code'] != 200) {
                            return [
                                'code' => 400,
                                'message' => $validasiZip['message'],
                            ];
                        }
                    }
    
                    return [
                        'code' => 200,
                        'message' => "File eligible to upload!",
                    ];
                }
            }
    
            return [
                'code' => 400,
                'message' => "File not eligible to upload!",
            ];
        } catch (\Exception $exception) {
            return [
                'code' => 500,
                'message' => 'Aw! something wrong here!',
            ];
        }
    }

    /**
     * Validate file type rules
     * 
     * @param string|array $type type of file
     * @param object|null $file file uploaded
     * @return array
     */
    public static function validateFileTypeRules($type, $file)
    {
        try {
            $ogName = $file->getClientOriginalName();

            $params = [
                'file' => $file
            ];

            $bunchOfType = is_array($type) ? $type : [$type];

            $bunchOfRules = [];
            foreach ($bunchOfType as $type) {
                $rules = FileSettings::getFileTypeRules($type);
                $bunchOfRules[] = $rules;
            }

            if (count($bunchOfRules) == 1) {
                $rules = $bunchOfRules[0];
            } else {
                $rules = FileSettings::mergeFileTypeRules($bunchOfRules);
            }

            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                $message = "";
                foreach ($validator->getMessageBag()->getMessages() as $num => $item) {
                    foreach ($item as $key => $value) {
                        $message .= "$num:$value <br>";
                    }
                }

                return [
                    'code' => 400,
                    'message' => $ogName . ':' . $message,
                ];
            }

            return [
                'code' => 200,
                'message' => 'Success validate file!',
            ];
        } catch (\Exception $exception) {
            return [
                'code' => 500,
                'message' => 'Aw! something wrong here!',
            ];
        }
    }

    /**
     * Validate zip file contain only secure items
     * 
     * @param object|null|string $file zip file uploaded
     * @param boolean $deleteSourceIfNotSecure delete zip file from server if contain not secure items
     * @param string $temporaryFolder folder to temporarily extract zip content
     * @return array
     */
    public static function validateZipFileContentSecured($file, $deleteSourceIfNotSecure = true, $temporaryFolder = 'extracted_zip')
    {
        try {
            $isFromUploadFile = FileSettings::isDoUploadFile($file);
            if ($isFromUploadFile) {
                $zip = new ZipArchive;
                $res = $zip->open($file);
                $fileName = $file->getClientOriginalName();
            } else {
                $fileAsliPath = $file;
                $zip = new ZipArchive;
                $res = $zip->open($fileAsliPath);
                $fileName = $file;
            }

            if ($res !== TRUE) {
                return [
                    'code' => 400,
                    'message' => 'Failed to validate zip file!',
                ];
            }

            $folderContainExtractedZip = $temporaryFolder . DIRECTORY_SEPARATOR . $fileName;
            $zip->extractTo($folderContainExtractedZip);
            $zip->close();

            $fileInsideZip = scandir($folderContainExtractedZip);
            $fileInsideZip = array_values(array_filter($fileInsideZip, fn ($item) => !in_array($item, ['.', '..'])));

            $isZipSecure = true;
            foreach ($fileInsideZip as $name) {
                $exploded = Utility::cleanExplode($name, '.');
                $ekstensi = end($exploded);

                if (is_executable($folderContainExtractedZip . DIRECTORY_SEPARATOR . $name)) {
                    $isZipSecure = false;
                    break;
                } elseif (FileSettings::isExtensionNotAllowed($ekstensi)) {
                    $isZipSecure = false;
                    break;
                }
            }

            Utility::recursivelyDeleteFolder($folderContainExtractedZip);
            if (!$isZipSecure && $deleteSourceIfNotSecure && !$isFromUploadFile) {
                unlink($fileAsliPath);
            }

            if (!$isZipSecure) {
                return [
                    'code' => 400,
                    'message' => 'Zip cannot contain executable file!',
                ];
            }

            return [
                'code' => 200,
                'message' => 'Great! Zip file secure!',
            ];
        } catch (\Exception $exception) {
            return [
                'code' => 400,
                'message' => 'Aw! something wrong here!',
            ];
        }
    }
}
