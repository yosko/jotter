<?php

define( 'URL',
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'?'https':'http')
    .'://'
    .$_SERVER['HTTP_HOST']
    .rtrim(dirname($_SERVER['SCRIPT_NAME']),'/')
    .'/'
);
define( 'DS', DIRECTORY_SEPARATOR );
define( 'PATH_TEMPLATE', Utils::convertPath(ROOT.'/tpl/') );
define( 'URL_TEMPLATE', URL.'tpl/' );
define( '', '' );

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
        $previous = null;
        $element = &$array;
        //get to the level of the given element (and keep its parent)
        foreach($nodes as $node) {
            $previous = &$element;
            $element = &$element[$node];
        }

        if($unset) {
            unset($previous[$node]);
            return $array;
        } elseif($get) {
            return $element;
        } else {
            $previous[$node] = $value;
            //make sure new element is in the right place
            natcasesort($previous);
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
}

?>