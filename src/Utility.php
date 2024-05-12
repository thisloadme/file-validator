<?php

namespace Riyantobudi\Support;

class Utility
{
    /**
     * Function to explode an array while cleaning the data
     * 
     * @param string $stringData string to be exploded
     * @param array|string $separator separator, can use array for priorities separator
     * @return array
     */
    public static function cleanExplode($stringData, $separator = ';')
    {
        if (is_array($separator)) {
            $arraySep = $separator;
            foreach ($arraySep as $sep) {
                if (strpos($stringData, $sep) > -1) {
                    $separator = $sep;
                    break;
                }
            }

            if (is_array($separator)) {
                $separator = $separator[0];
            }
        }

        $tempArray = explode($separator, $stringData);
        $trimmedArray = array_map('trim', $tempArray);
        $cleanedArray = array_filter($trimmedArray, fn ($item) => !is_null($item) && $item !== '');
        return array_values($cleanedArray);
    }

    /**
     * Delete entire folder and the childs
     * 
     * @param string $dir directory path
     */
    public static function recursivelyDeleteFolder($dir)
    {
        if (is_dir($dir)) {
            $bunchOfObject = scandir($dir);
            foreach ($bunchOfObject as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object)) {
                        self::recursivelyDeleteFolder($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}
