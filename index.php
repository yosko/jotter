<?php
/**
 * YosNote - open-source organized note-taking web app
 *
 * @license     LGPL v3 (http://www.gnu.org/licenses/lgpl.html)
 * @author      Yosko <webmaster@yosko.net>
 * @version     v0.1
 * @link        https://github.com/yosko/yosnote
 */
define( 'VERSION', '0.1' );
define( 'ROOT', __DIR__ );
define( 'URL',
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'?'https':'http')
    .'://'
    .$_SERVER['HTTP_HOST']
    .rtrim(dirname($_SERVER['SCRIPT_NAME']),'/')
    .'/'
);

require_once( ROOT.'/lib/yosnote.class.php');
require_once( ROOT.'/lib/easydump.php');

$yosnote = new YosNote();

d($_GET, $_POST);

//notebook pages
if( !empty($_GET['nb']) ) {
    // rename current notebook
    
    // delete current notebook
    
    // notebook item
    
    // rename current item
    
    // delete current item
    
    // add a note to the current directory
    
    // add a subdirectory to the current directory
    
    // save current note (via json request?)
    

//add a notebook
} elseif( !empty($_GET['action']) && $_GET['action'] == 'add' ) {

    include( ROOT.'/tpl/notebookForm.tpl.php' );

//logging out
} elseif( !empty($_GET['action']) && $_GET['action'] == 'logout' ) {

//logging in
} elseif( !empty($_POST['submitLoginForm']) ) {

//homepage: notebooks list
} else {
    $notebooks = $yosnote->loadNotebooks();
    include( ROOT.'/tpl/notebooks.tpl.php' );
}

?>