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

require_once( ROOT.'/lib/yosnote.class.php');
require_once( ROOT.'/lib/utils.class.php');
require_once( ROOT.'/lib/easydump.php');

$yosnote = new YosNote();
$errors = array();

//notebook pages
if( !empty($_GET['nb']) ) {
    $notebookName = urlencode($_GET['nb']);

    $notebook = $yosnote->loadNotebook($notebookName);

    // rename current notebook
    if( !empty($_GET['action']) && $_GET['action'] == 'edit' ) {
        d('edit notebook');

    // delete current notebook
    } elseif( !empty($_GET['action']) && $_GET['action'] == 'delete' && empty($_GET['item']) ) {
        d('delete notebook');

    // add a subdirectory to the current directory
    } elseif( !empty($_GET['action']) && $_GET['action'] == 'adddir' ) {
        //item is optional
        d('add directory to notebook');

    // add a note to the current directory
    } elseif( !empty($_GET['action']) && $_GET['action'] == 'addnote' ) {
        //item is optional
        d('add note to notebook');

    // notebook item
    } elseif( !empty($_GET['item']) ) {

        //TODO check if item contains .. or if it doesn't exist

        $itemPath = $_GET['item'];
        $item = Utils::getArrayItem($notebook['tree'], $itemPath);

        // rename current item
        if( !empty($_GET['action']) && $_GET['action'] == 'rename' ) {
            d('rename note');

        // delete current item
        } elseif( !empty($_GET['action']) && $_GET['action'] == 'delete' ) {
            d('delete note');

        // save current note (via json request?)
        } elseif( !empty($_GET['action']) && $_GET['action'] == 'save' ) {
            d('save note');

        //show item
        } else {
            if($item == true) {
                $note = $yosnote->loadNote($notebookName, $_GET['item']);
                include( ROOT.'/tpl/note.tpl.php' );
            } else {
                include( ROOT.'/tpl/notebook.tpl.php' );
            }
        }

    } else {
        include( ROOT.'/tpl/notebook.tpl.php' );
    }


//add a notebook
} elseif( !empty($_GET['action']) && $_GET['action'] == 'add' ) {
    // user wants to make a new notebook
    if(isset($_POST['name'])) {
        $notebook = array(
            'name' => urlencode($_POST['name']),
            'user' => 1
        );

        //load the complete list of notebooks
        $notebooks = $yosnote->loadNotebooks();

        $errors['empty'] = empty($notebook['name']);
        $errors['alreadyExists'] = isset($notebooks[$notebook['name']]);
        if(!in_array(true, $errors)) {
            $notebooks = $yosnote->setNotebook($notebook['name'], $notebook['user']);

            header('Location: '.URL.'?nb='.$notebook['name']);
            exit;
        }
    }

    include( ROOT.'/tpl/notebookForm.tpl.php' );

//logging out
} elseif( !empty($_GET['action']) && $_GET['action'] == 'logout' ) {
    d('log out');

//logging in
} elseif( !empty($_POST['submitLoginForm']) ) {
    d('log in');

//homepage: notebooks list
} else {
    $notebooks = $yosnote->loadNotebooks();
    include( ROOT.'/tpl/notebooks.tpl.php' );
}

?>