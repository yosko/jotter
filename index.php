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
define( 'ROOT', __DIR__.'/' );
define( 'DIR_DATA', ROOT.'data/' );
define( 'DIR_TPL', ROOT.'tpl/' );
define( 'URL',
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'?'https':'http')
    .'://'
    .$_SERVER['HTTP_HOST']
    .rtrim(dirname($_SERVER['SCRIPT_NAME']),'/')
    .'/'
);
define( 'URL_TPL', URL.'tpl/' );
define( 'DEVELOPMENT_ENVIRONMENT', true );

//display errors & warnings
if (DEVELOPMENT_ENVIRONMENT == true) {
    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors','On');
    // ini_set('log_errors', 'On');
    // ini_set('error_log', ROOT.'errors.log');
}

// external libraries
// https://github.com/michelf/php-markdown/
require_once( ROOT.'lib/ext/Markdown.php');
require_once( ROOT.'lib/ext/MarkdownExtra.php');
// https://github.com/nickcernis/html-to-markdown
require_once( ROOT.'lib/ext/HTML_To_Markdown.php');
// https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP/
require_once( ROOT.'lib/ext/srand.php');
// https://github.com/yosko/yoslogin/
require_once( ROOT.'lib/ext/yoslogin.class.php');

//Jotter libraries
require_once( ROOT.'lib/utils.class.php');
require_once( ROOT.'lib/jotter.class.php');
require_once( ROOT.'lib/login.class.php');

$jotter = new Jotter();
$errors = array();
$isNote = false;
$isConfigMode = false;
$isEditMode = false;
$isDir = false;
$appInstalled = file_exists(DIR_DATA.'users.json');

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
        $phpMinVersion = '5.3';
        $phpIsMinVersion = (version_compare(PHP_VERSION, $phpMinVersion) >= 0);
        $isWritable = (file_exists(DIR_DATA) && is_writable(DIR_DATA)) || is_writable(dirname(DIR_DATA));
    }

    include( DIR_TPL.'loginForm.tpl.php' );

//notebook pages
} elseif( !empty($_GET['nb']) ) {
    $itemPath = '';
    $notebook = false;
    $notebookName = urlencode($_GET['nb']);

    //load the complete list of notebooks
    $notebooks = $jotter->loadNotebooks();

    //only load notebook if it is owned by current user
    if(isset($notebooks[$user['login']][$notebookName])) {
        $notebook = $jotter->loadNotebook($notebookName, $user['login']);
    }

    // notebook wasn't loaded
    if($notebook == false) {
        include( DIR_TPL.'error.tpl.php' );

    // rename current notebook
    } elseif( !empty($_GET['action']) && $_GET['action'] == 'edit' && empty($_GET['item']) ) {
        d('edit notebook');

    // delete current notebook
    } elseif( !empty($_GET['action']) && $_GET['action'] == 'delete' && empty($_GET['item']) ) {
        //confirmation was sent
        if(isset($_POST['delete'])) {
            $jotter->unsetNotebook($notebookName, $user['login']);

            header('Location: '.URL);
            exit;
        }

        include( DIR_TPL.'itemDelete.tpl.php' );

    // add a subdirectory or a note to the current directory
    } elseif( !empty($_GET['action']) && ($_GET['action'] == 'adddir' || $_GET['action'] == 'addnote') ) {
        if(isset($_POST['name'])) {
            $item['name'] = $_POST['name'];
            $path = $item['name'];

            if(!empty($_GET['item'])) {
                if(!is_dir(DIR_DATA.$user['login'].'/'.$notebookName.'/'.$_GET['item'])) {
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

        include( DIR_TPL.'itemForm.tpl.php' );

    // notebook item
    } elseif( !empty($_GET['item']) && strpos($itemPath, '..') === false ) {
        $itemPath = $_GET['item'];

        $itemData = Utils::getArrayItem($notebook['tree'], $itemPath);
        $isNote = $itemData === true;
        if(!$isNote) {
            $dirPath = DIR_DATA.$user['login'].'/'.$notebookName.'/'.$itemPath;
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
            include( DIR_TPL.'itemForm.tpl.php' );

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

            include( DIR_TPL.'itemDelete.tpl.php' );

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

                include( DIR_TPL.'note.tpl.php' );
            } elseif($isDir) {
                //for a directory, just show the notebook's "hompage"
                include( DIR_TPL.'notebook.tpl.php' );
            } else {
                //TODO: show error
            }
        }

    } else {
        include( DIR_TPL.'notebook.tpl.php' );
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
        $errors['alreadyExists'] = isset($notebooks[$user['login']][$notebook['name']]);
        if(!in_array(true, $errors)) {
            $notebooks = $jotter->setNotebook($notebook['name'], $notebook['user']);

            header('Location: '.URL.'?nb='.$notebook['name']);
            exit;
        }
    }

    include( DIR_TPL.'notebookForm.tpl.php' );

//configuration page
} elseif( !empty($_GET['action']) && $_GET['action'] == 'config' ) {
    $isConfigMode = true;
    $users = $logger->getUsers();
    $option = isset($_GET['option'])?$_GET['option']:false;

    if($option == 'myPassword') {
        if (isset($_POST['password'])) {
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
        if (isset($_POST['login']) && isset($_POST['password'])) {
            $login = htmlspecialchars(trim($_POST['login']));
            $password = htmlspecialchars(trim($_POST['password']));

            $errors['emptyLogin'] = $login == '';
            $errors['emptyPassword'] = $password == '';
            $errors['notAvailable'] = false;
            foreach ($users as $key => $value) {
                if($value['login'] == $login)
                    $errors['notAvailable'] = true;
            }

            if(!in_array(true, $errors)) {
                $logger->createUser($login, $password);

                header('Location: '.URL.'?action=config');
                exit;
            }
        }
        
    } elseif($option == 'deleteUser') {
        $login = htmlspecialchars(trim($_GET['user']));

        if(isset($_POST['deleteUserSubmit'])) {
            //delete user's notebooks
            $notebooks = $jotter->loadNotebooks();
            foreach($notebooks[$user['login']] as $key => $value) {
                if($value['user'] == $login) {
                    $jotter->unsetNotebook($key);
                }
            }

            //delete user
            $logger->deleteUser($login, $password);

            header('Location: '.URL.'?action=config');
            exit;
        }
        
    } else {

    }

    include( DIR_TPL.'config.tpl.php' );


//homepage: notebooks list
} else {
    $notebooks = $jotter->loadNotebooks();
    include( DIR_TPL.'notebooks.tpl.php' );
}

?>