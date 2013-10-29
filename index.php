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
$isNote = false;
$isDir = false;

//notebook pages
if( !empty($_GET['nb']) ) {
    $itemPath = '';
    $notebookName = urlencode($_GET['nb']);

    $notebook = $yosnote->loadNotebook($notebookName);

    // rename current notebook
    if( !empty($_GET['action']) && $_GET['action'] == 'edit' ) {
        d('edit notebook');

    // delete current notebook
    } elseif( !empty($_GET['action']) && $_GET['action'] == 'delete' && empty($_GET['item']) ) {
        d('delete notebook');

    // add a subdirectory or a note to the current directory
    } elseif( !empty($_GET['action']) && ($_GET['action'] == 'adddir' || $_GET['action'] == 'addnote') ) {
        if(isset($_POST['name'])) {
            $item['name'] = $_POST['name'];
            $path = $item['name'];

            if(!empty($_GET['item'])) {
                if(!is_dir(ROOT.'/notebooks/'.$notebookName.'/'.$_GET['item'])) {
                    if(dirname($_GET['item']) != '.') {
                        $path = dirname($_GET['item']).'/'.$path;
                    }
                } else {
                    if(!empty($_GET['item'])) {
                        $path = $_GET['item'].'/'.$path;
                    }
                }
            }

            $errors['empty'] = empty($item['name']);
            $errors['alreadyExists'] = !is_null(Utils::getArrayItem($notebook['tree'], $path));
            if(!in_array(true, $errors)) {
                if($_GET['action'] == 'addnote') {
                    $path .= '.md';
                    $yosnote->setNote($path);
                }
                else {
                    $yosnote->setDirectory($path);
                }

                header('Location: '.URL.'?nb='.$notebookName.'&item='.$path);
                exit;
            }
        }

        include( ROOT.'/tpl/itemForm.tpl.php' );

    // notebook item
    } elseif( !empty($_GET['item']) ) {
        $itemPath = $_GET['item'];
        if(strpos($itemPath, '..') === false){
            $item = Utils::getArrayItem($notebook['tree'], $itemPath);
            $isNote = $item === true;
            if(!$isNote) {
                $dirPath = ROOT.'/notebooks/'.$notebookName.'/'.$itemPath;
                $isDir = file_exists($dirPath) && is_dir($dirPath);
            }
        }

        // rename current item
        if( !empty($_GET['action']) && $_GET['action'] == 'edit' ) {
            d('rename item');
            include( ROOT.'/tpl/itemForm.tpl.php' );

        // delete current item
        } elseif( !empty($_GET['action']) && $_GET['action'] == 'delete' ) {
            //confirmation was sent
            if(isset($_POST['delete'])) {
                if($isNote) {
                    $yosnote->unsetNote($itemPath);
                } elseif($isDir) {
                    $yosnote->unsetDirectory($itemPath);
                }

                header('Location: '.URL.'?nb='.$notebookName.'&item='.(dirname($itemPath)!='.'?dirname($itemPath):''));
                exit;
            }

            include( ROOT.'/tpl/itemDelete.tpl.php' );

        // save current note (via json request?)
        } elseif( !empty($_GET['action']) && $_GET['action'] == 'save' ) {
            d('save note');

        //show item
        } else {
            if($isNote) {
                //we are dealing with a note: load it
                $note = $yosnote->loadNote($_GET['item']);
                include( ROOT.'/tpl/note.tpl.php' );
            } elseif($isDir) {
                //for a directory, just show the notebook's "hompage"
                include( ROOT.'/tpl/notebook.tpl.php' );
            } else {
                //TODO: show error
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