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
}

?>