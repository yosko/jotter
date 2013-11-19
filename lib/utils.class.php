<?php

define( 'DS', DIRECTORY_SEPARATOR );

class Utils {
    /**
     * Convert a path to respect the OS specific directory separator
     * @param  string $path Unix style path
     * @return string       Converted path (depend on the system)
     */
    public static function convertPath($path) {
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        return $path;
    }

    /**
     * Access an array element via its path (and possibly update/delete it)
     * @param  array   $array Source array
     * @param  string  $path  Path to element (ex: 'subarray/subsubarray/item')
     * @param  misc    $value Value that should be given to the element (complete array will be returned)
     * @param  boolean $get   True to return the element ($value will be ignored)
     * @param  boolean $unset True to unset element ($value & $get will be ignored, complete array will be returned)
     * @return misc           Complete array modified
     *                        Just the pointed element if asked to
     *                        NULL returned if element not found
     */
    protected static function handleArrayItemFromPath($array, $path, $value, $get = false, $unset = false) {
        $nodes = explode('/', $path);
        $null = null;
        $previous = null;
        $element = &$array;
        //get to the level of the given element (and keep its parent)
        foreach($nodes as $node) {
            $previous = &$element;
            if(isset($element[$node]))
                $element = &$element[$node];
            else {
                $element = &$null;
            }
        }

        if($unset) {
            unset($previous[$node]);
            return $array;
        } elseif($get) {
            return $element;
        } else {
            $previous[$node] = $value;
            //make sure new element is in the right place
            Utils::natcaseksort($previous);
            return $array;
        }
    }

    public static function getArrayItem($array, $path) {
        return self::handleArrayItemFromPath($array, $path, null, true);
    }

    public static function setArrayItem($array, $path, $value) {
        return self::handleArrayItemFromPath($array, $path, $value);
    }

    public static function unsetArrayItem($array, $path) {
        return self::handleArrayItemFromPath($array, $path, null, false, true);
    }

    /**
     * Sort array by keys in "natural" order & case-insensitively
     * Combine uksort (sort by keys) with strnatcasecmp (natural case-insensitive comparison) 
     * @param  array $array unsorted array
     */
    public static function natcaseksort(&$array) {
        uksort($array, 'strnatcasecmp');
    }

    /**
     * Load a text file
     * @param  string $file path to file
     * @return string       file content
     */
    public static function loadFile($file) {
        if (file_exists( $file )) {
            $data = file_get_contents($file);
            return $data;
        } else {
            // touch($file);
            return false;
        }
    }

    /**
     * Save a text file
     * @param  string $file    path to file
     * @param  string $content file content
     * @return boolean         true on success
     */
    public static function saveFile($file, $content) {
        if (!file_exists( $file )) {
            //in case the directory doesn't yet exist
            if(!file_exists( dirname($file) ))
                $success = mkdir(dirname($file), 0700, true);
            //create file
            touch($file);
        }
        $fp = fopen( $file, 'w' );
        if($fp) {
            fwrite($fp, $content);
            fclose($fp);
        }
        return $fp !== false;
    }

    /**
     * Load a (compressed) JSON file
     * @param  string  $file     Path to file
     * @param  boolean $compress If data should be gzip uncompressed before decoding it
     * @return misc              File content decoded
     */
    public static function loadJson($file, $uncompress = false) {
        if($data = self::loadFile($file)) {
            if($uncompress)
                $data = gzinflate($data);
            $data = json_decode($data, true);
        }
        return $data;
    }

    /**
     * Save data into a compressed JSON file
     * @param  string  $file     Path to file
     * @param  misc    $data     Content to save into file
     * @param  boolean $compress Compress (or not) file content in gzip
     * @return boolean           true on success
     */
    public static function saveJson($file, $data, $compress = false) {
        if(version_compare(PHP_VERSION, '5.4.0') >= 0)
            $json = json_encode($data, JSON_PRETTY_PRINT);
        else
            $json = json_encode($data);

        if($compress)
            $json = gzdeflate($json);

        return self::saveFile($file, $json);
    }

    /**
     * Recursive directory remove
     * taken from http://www.php.net/manual/en/function.rmdir.php#110489
     * @param  string $dir path to directory
     * @return boolean     true on success
     */
    public static function rmdirRecursive($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            if(is_dir("$dir/$file"))
                self::rmdirRecursive("$dir/$file");
            else
                unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}

?>