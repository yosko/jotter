<?php
/**
 * Jotter - open-source organized note-taking web app
 *
 * @license     LGPL v3 (http://www.gnu.org/licenses/lgpl.html)
 * @author      Yosko <webmaster@yosko.net>
 * @version     v0.1
 * @link        https://github.com/yosko/jotter
 */
define( 'VERSION', '0.1' );
define( 'ROOT', __DIR__ );

// external libraries
// https://github.com/yosko/easydump
require_once( ROOT.'/lib/ext/easydump.php');
// https://github.com/michelf/php-markdown/
require_once( ROOT.'/lib/ext/Markdown.php');
require_once( ROOT.'/lib/ext/MarkdownExtra.php');
// https://github.com/nickcernis/html-to-markdown
require_once( ROOT.'/lib/ext/HTML_To_Markdown.php');
// https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP/
require_once( ROOT.'/lib/ext/srand.php');
// https://github.com/yosko/yoslogin/
require_once( ROOT.'/lib/ext/yoslogin.class.php');

//Jotter libraries
require_once( ROOT.'/lib/utils.class.php');
require_once( ROOT.'/lib/jotter.class.php');
require_once( ROOT.'/lib/login.class.php');

$jotter = new Jotter();
$errors = array();
$isNote = false;
$isConfigMode = false;
$isEditMode = false;
$isDir = false;
$appInstalled = file_exists(ROOT.'/notebooks/users.json');

//check if user is logged in
$logger = new Login( 'jotter' );

//user is trying to log in
if( !empty($_POST['submitLoginForm']) ) {
    //install app and create first user
    if(!$appInstalled) {
        $logger->createUser(
            htmlspecialchars(trim($_POST['login'])),
            htmlspecialchars(trim($_POST['password']))
        );
    }

    $user = $logger->logIn(
        htmlspecialchars(trim($_POST['login'])),
        htmlspecialchars(trim($_POST['password'])),
        isset($_POST['remember'])
    );

//logging out
} elseif( !empty($_GET['action']) && $_GET['action'] == 'logout' ) {
    $logger->logOut();
} else {
    $user = $logger->authUser();
}


//login form
if(!$user['isLoggedIn']) {
    //display form as an installation process
    if(!$appInstalled) {
        $notebooksPath = ROOT.'/notebooks/';
        $phpMinVersion = '5.3';
        $phpIsMinVersion = (version_compare(PHP_VERSION, $phpMinVersion) >= 0);
        $isWritable = (file_exists($notebooksPath) && is_writable($notebooksPath)) || is_writable(dirname($notebooksPath));
    }

    include( ROOT.'/tpl/loginForm.tpl.php' );

//notebook pages
} elseif( !empty($_GET['nb']) ) {
    $itemPath = '';
    $notebook = false;
    $notebookName = urlencode($_GET['nb']);

    //load the complete list of notebooks
    $notebooks = $jotter->loadNotebooks();

    //only load notebook if it is owned by current user
    if($notebooks[$notebookName]['user'] == $user['login']) {
        $notebook = $jotter->loadNotebook($notebookName);
    }

    // notebook wasn't loaded
    if($notebook == false) {
        include( ROOT.'/tpl/error.tpl.php' );

    // rename current notebook
    } elseif( !empty($_GET['action']) && $_GET['action'] == 'edit' && empty($_GET['item']) ) {
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
                    $jotter->setNote($path);
                }
                else {
                    $jotter->setDirectory($path);
                }

                header('Location: '.URL.'?nb='.$notebookName.'&item='.$path);
                exit;
            }
        }

        include( ROOT.'/tpl/itemForm.tpl.php' );

    // notebook item
    } elseif( !empty($_GET['item']) && strpos($itemPath, '..') === false ) {
        $itemPath = $_GET['item'];

        $itemData = Utils::getArrayItem($notebook['tree'], $itemPath);
        $isNote = $itemData === true;
        if(!$isNote) {
            $dirPath = ROOT.'/notebooks/'.$notebookName.'/'.$itemPath;
            $isDir = file_exists($dirPath) && is_dir($dirPath);
        }

        // rename current item
        if( !empty($_GET['action']) && $_GET['action'] == 'edit' ) {
            //confirmation was sent
            if(isset($_POST['name'])) {
                $item['name'] = $_POST['name'];
                $path = $item['name'];
                $path = (dirname($itemPath)!='.'?dirname($itemPath).'/':'').$path;

                $errors['empty'] = empty($item['name']);
                $errors['sameName'] = $itemPath == $path.'.md';
                $errors['alreadyExists'] = !is_null(Utils::getArrayItem($notebook['tree'], $path));

                if(!in_array(true, $errors)) {
                    if($isNote) {
                        $path .= '.md';
                        $item['name'] .= '.md';
                        $jotter->setNote($itemPath, $item['name']);
                    }
                    elseif($isDir) {
                        $jotter->setDirectory($itemPath, $item['name']);
                    }

                    header('Location: '.URL.'?nb='.$notebookName.'&item='.$path);
                    exit;
                }
            }
            include( ROOT.'/tpl/itemForm.tpl.php' );

        // delete current item
        } elseif( !empty($_GET['action']) && $_GET['action'] == 'delete' ) {
            //confirmation was sent
            if(isset($_POST['delete'])) {
                if($isNote) {
                    $jotter->unsetNote($itemPath);
                } elseif($isDir) {
                    $jotter->unsetDirectory($itemPath);
                }

                header('Location: '.URL.'?nb='.$notebookName.'&item='.(dirname($itemPath)!='.'?dirname($itemPath):''));
                exit;
            }

            include( ROOT.'/tpl/itemDelete.tpl.php' );

        // save current note (via json request)
        } elseif( !empty($_GET['action']) && $_GET['action'] == 'save' ) {
            $success = false;

            if($isNote && isset($_POST['text'])) {
                //save the note
                $success = $jotter->setNoteText($itemPath, $_POST['text']);
            }

            header('Content-type: application/json');
            echo json_encode($success);
            exit;

        //show item
        } else {
            if($isNote) {
                //we are dealing with a note: load it
                $note = $jotter->loadNote($_GET['item']);

                // show editor toolbar
                $isEditMode = true;

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
            'user' => $user['login']
        );

        $errors['empty'] = empty($notebook['name']);
        $errors['alreadyExists'] = isset($notebooks[$notebook['name']]);
        if(!in_array(true, $errors)) {
            $notebooks = $jotter->setNotebook($notebook['name'], $notebook['user']);

            header('Location: '.URL.'?nb='.$notebook['name']);
            exit;
        }
    }

    include( ROOT.'/tpl/notebookForm.tpl.php' );

//configuration page
} elseif( !empty($_GET['action']) && $_GET['action'] == 'config' ) {
    $isConfigMode = true;
    $users = $logger->getUsers();
    $option = isset($_GET['option'])?$_GET['option']:false;

    if($option == 'myPassword') {
        if (isset($_POST["submitNewPassword"])) {
            $password = htmlspecialchars(trim($_POST['password']));
            $errors['emptyPassword'] = (!isset($_POST['password']) || trim($_POST['password']) == "");

            if(!in_array(true, $errors)) {
                //save password
                $errors['save'] = !$logger->setUser($user['login'], $password);

                header('Location: '.URL.'?action=config&option=myPassword');
                exit;
            }
        }
        
    } elseif($option == 'addUser') {
        
    } elseif($option == 'editUser') {
        
    } else {

    }

    include( ROOT.'/tpl/config.tpl.php' );


//homepage: notebooks list
} else {
    $notebooks = $jotter->loadNotebooks();
    include( ROOT.'/tpl/notebooks.tpl.php' );
}

?>