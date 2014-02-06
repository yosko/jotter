<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Jotter - Notebook Manager</title>
    <link rel="stylesheet" href="<?php echo URL_TPL; ?>style.css">
    <link rel="icon" type="image/png" href="<?php echo URL_TPL; ?>img/jotter-icon-16.png"/>
    <script src="<?php echo URL_TPL; ?>js/main.js"></script>
<?php if($isNote && $isEditMode) { ?>
    <script src="<?php echo URL_TPL; ?>js/editor.js"></script>
<?php if($isWysiwyg) { ?>
    <script src="<?php echo URL_TPL; ?>js/ext/jquery-2.0.3.min.js"></script>
    <script src="<?php echo URL_TPL; ?>js/ext/jquery.hotkeys.js"></script>
    <script src="<?php echo URL_TPL; ?>js/ext/bootstrap.min.js"></script>
    <script src="<?php echo URL_TPL; ?>js/ext/bootstrap-wysiwyg.js"></script>
    <script src="<?php echo URL_TPL; ?>js/editor-wysiwyg.js"></script>
<?php
    } //isWysiwyg
} //isNote & isEditMode
?>
</head>
<body>
<div id="toolbar">
    <div class="toolbar" id="panel-toolbar">
        <ul class="actions">
<?php if($user['isLoggedIn']) { ?>

            <li>
                <a href="<?php echo URL; ?>" title="List of notebooks">
                    <img src="<?php echo URL_TPL; ?>img/jotter-icon-16.png" alt="Notebooks">
                </a>
            </li>
            <li>
                <a href="<?php echo URL; ?>?action=config" title="Configure Jotter">
                    <img src="<?php echo URL_TPL; ?>img/wrench-screwdriver.png" alt="Config">
                </a>
            </li>
            <li>
                <a href="<?php echo URL; ?>?action=logout" title="Log out">
                    <img src="<?php echo URL_TPL; ?>img/door-open-out.png" alt="Logout">
                </a>
            </li>
<?php } ?>
        </ul>
    </div>
    <div class="toolbar" id="item-toolbar" data-role="editor-toolbar" data-target="#editor">
        <ul class="actions btn-info">
<?php if($isNote && $isEditMode) { ?>
            <li>
                <a href="#" id="save-button" class="disabled" title="Save this note">
                    <img src="<?php echo URL_TPL; ?>img/disk-black.png" alt="Save note">
                </a>
            </li>
<?php if($isWysiwyg) { ?>
            <li>
                <a href="#" id="headingDropDown" class="ajax-formatter" data-toggle="dropdown" title="Title">
                    <img src="<?php echo URL_TPL; ?>img/edit-heading.png" alt="Link">
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" data-edit="bold" title="Bold (Ctrl+B)">
                    <img src="<?php echo URL_TPL; ?>img/edit-bold.png" alt="Bold">
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" data-edit="italic" title="Italic (Ctrl+I)">
                    <img src="<?php echo URL_TPL; ?>img/edit-italic.png" alt="Italic">
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" data-edit="insertunorderedlist" title="List">
                    <img src="<?php echo URL_TPL; ?>img/edit-list.png" alt="List">
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" data-edit="insertorderedlist" title="Ordered list">
                    <img src="<?php echo URL_TPL; ?>img/edit-list-order.png" alt="Ordered List">
                </a>
            </li>
            <li>
                <a href="#" id="linkDropdown" class="ajax-formatter" data-toggle="dropdown" title="Link">
                    <img src="<?php echo URL_TPL; ?>img/chain--plus.png" alt="Link">
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" data-edit="unlink" title="Remove link">
                    <img src="<?php echo URL_TPL; ?>img/chain--minus.png" alt="Remove link">
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" id="picture-button" title="Insert image (or drag &amp; drop it in your text)">
                    <img src="<?php echo URL_TPL; ?>img/image.png" alt="Image">
                </a>
                <input type="file" id="hidden-picture-button" data-target="#picture-button" data-edit="insertImage" />
            </li>
            <li>
                <a href="#" class="ajax-formatter" id="mdash-button" title="Insert em dash">
                    &mdash;
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" id="source-button" title="View source">
                    <img src="<?php echo URL_TPL; ?>img/edit-code.png" alt="Source">
                </a>
            </li>
<?php
    } // isWysiwyg
    else {
?>
            <li>
                <a href="<?php echo URL; ?>?action=markdown" target="blank" id="markdown-button" title="Show Markdown syntax help">
                    <img src="<?php echo URL_TPL; ?>img/edit-markdown.png" alt="Markdown">
                </a>
            </li>
<?php
    } // not isWysiwyg
} // $isNote & isEditMode
?>

        </ul>
<?php if($isNote && $isEditMode && $isWysiwyg) { ?>
        <div id="insertLink">
            <input placeholder="http://" type="text" data-edit="createLink"/>
            <button type="button">Add</button>
        </div>
        <ul class="actions" id="headingButtons">
            <li>
                <a href="#" class="ajax-formatter" data-edit="formatBlock h1" title="Title level 1">
                    <img src="<?php echo URL_TPL; ?>img/edit-heading-1.png" alt="Level 1">
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" data-edit="formatBlock h2" title="Title level 2">
                    <img src="<?php echo URL_TPL; ?>img/edit-heading-2.png" alt="Level 2">
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" data-edit="formatBlock h3" title="Title level 3">
                    <img src="<?php echo URL_TPL; ?>img/edit-heading-3.png" alt="Level 3">
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" data-edit="formatBlock h4" title="Title level 4">
                    <img src="<?php echo URL_TPL; ?>img/edit-heading-4.png" alt="Level 4">
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" data-edit="formatBlock h5" title="Title level 5">
                    <img src="<?php echo URL_TPL; ?>img/edit-heading-5.png" alt="Level 5">
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" data-edit="formatBlock h6" title="Title level 6">
                    <img src="<?php echo URL_TPL; ?>img/edit-heading-6.png" alt="Level 6">
                </a>
            </li>
            <li>
                <a href="#" class="ajax-formatter" data-edit="formatBlock p" title="Turn title into a paragraph">
                    <img src="<?php echo URL_TPL; ?>img/edit-heading-minus.png" alt="Paragraph">
                </a>
            </li>
        </ul>
<?php } // $isNote ?>

    </div>
</div>
<div id="app">
<nav id="panel">
<?php if(isset($notebook['tree'])) { ?>
    
    <form action="">
        <select name="nb" id="notebookSelect">
            <option value="!nothing!">&raquo; select a notebook &laquo;</option>
            <option value="!new!">&raquo; create a new notebook &laquo;</option>
<?php
if(!empty($notebooks[$user['login']])) {
    foreach($notebooks[$user['login']] as $key => $value) {
?>

            <option value="<?php echo $key; ?>"><?php echo urldecode($key); ?></option>
<?php
    }
}
?>
        </select>
    </form>
    <div class="item-menu">
        <img class="dropdown-arrow" src="<?php echo URL_TPL; ?>img/arbo-parent-open.png" alt="v">
        <ul class="dropdown closed">
        <!--<li><a href="<?php echo URL; ?>?nb=<?php echo $notebookName; ?>&amp;action=edit" title="Edit notebook">Edit</a></li>-->
        <li><a href="<?php echo URL; ?>?nb=<?php echo $notebookName; ?>&amp;action=delete" title="Delete notebook">
            <img class="icon" src="<?php echo URL_TPL; ?>img/folders-stack-minus.png" alt="">
            Delete
        </a></li>
        </ul>
    </div>
    <h3<?php if(empty($_GET['item'])) { echo ' id="selected"'; } ?> data-path="">
        <a class="item" id="notebookTitle" href="?nb=<?php echo $notebookName; ?>" data-name="<?php echo $notebookName; ?>"><?php echo urldecode($notebookName); ?></a>
    </h3>
<?php

function Tree2Html($tree, $nbName, $selectedPath, $parents = array()) {
    $level = count($parents);
    $html = str_repeat("\t", $level*2)."<ul";
    if($level == 0) {
        $html .= ' id="root" class="subtree open"';
    } else {
        $html .= ' class="subtree open"';
    }
    $html .= ">\r\n";
    
    foreach ($tree as $key => $value) {
        $isArray = is_array($value);
        $isNote = substr($key, -3) == '.md';
        if($isArray || $isNote) {
            //path to element
            $path = (!empty($parents)?implode('/', $parents).'/':'').$key;

            $html .= str_repeat("\t", $level*2+1)
                .'<li class="'.($isArray?"directory":"file").'"'
                .($path == $selectedPath?' id="selected"':'')
                .' data-path="'.$path.'">';

            //if array, show open/close button
            if($isArray) {
                $html .= '<a class="arrow open" href="#"><img src="'.URL_TPL.'img/arbo-parent-open.png" alt="-"></a>';
            }
            $html .= "\r\n".str_repeat("\t", $level*2+2);
            $html .= '<div class="item-menu">';
            $html .= '<img class="dropdown-arrow" src="'.URL_TPL.'img/arbo-parent-open.png" alt="v">';
            $html .= '<ul class="dropdown closed">';
            $html .= '<li><a href="'.URL.'?nb='.$nbName.'&amp;item='.$path.'&amp;action=edit" title="Edit &quot;'.$path.'&quot;">';
            $html .= '<img class="icon" src="'.URL_TPL.'img/'.($isNote?'document':'folder').'--pencil.png" alt=""> Edit</a></li>';
            $html .= '<li><a href="'.URL.'?nb='.$nbName.'&amp;item='.$path.'&amp;action=delete" title="Delete &quot;'.$path.'&quot;">';
            $html .= '<img class="icon" src="'.URL_TPL.'img/'.($isNote?'document':'folder').'--minus.png" alt=""> Delete</a></li>';
            $html .= '</ul>';
            $html .= '</div>';

            $html .= "\r\n".str_repeat("\t", $level*2+2);
            $html .= '<a draggable="true" class="item" href="'.URL.'?nb='.$nbName.'&amp;item='.$path.'">';
            $html .= basename($key, '.md');
            $html .= '</a>';

            //if array, show its children
            if($isArray) {
                $html .= "\r\n";
                $html .= Tree2Html($value, $nbName, $selectedPath, array_merge($parents, (array)$key));
            }

            $html .= "\r\n".str_repeat("\t", $level*2+1);
            $html .= "</li>\r\n";
        }
    }

    $html .= str_repeat("\t", $level*2)."</ul>\r\n";
    return $html;
}

echo Tree2Html($notebook['tree'], $notebookName, isset($_GET['item'])?$_GET['item']:'');

?>
    <ul class="buttons">
        <li>
            <a href="<?php echo URL; ?>?nb=<?php echo $notebookName; ?>&amp;item=<?php echo $itemPath; ?>&amp;action=addnote" title="Add a new note inside the current directory">
                <img src="<?php echo URL_TPL; ?>img/document--plus.png" alt="">
                Add note
            </a>
        </li>
        <li>
            <a href="<?php echo URL; ?>?nb=<?php echo $notebookName; ?>&amp;item=<?php echo $itemPath; ?>&amp;action=adddir" title="Add a new directory inside the current directory">
                <img src="<?php echo URL_TPL; ?>img/folder--plus.png" alt="">
                Add directory
            </a>
        </li>
    </ul>

<?php } // notebook tree ?>
<?php if($isConfigMode) { ?>
    <ul>
        <li><a href="<?php echo URL; ?>?action=config&amp;option=myPassword">Change my password</a></li>
        <li><a href="<?php echo URL; ?>?action=config&amp;option=addUser">Add user</a></li>
<?php if(count($users) > 1) { ?>
        <li>
            Delete users:
            <ul>
<?php
foreach($users as $value) {
    if($value['login'] != $user['login']) {
?>
                <li><a href="<?php echo URL; ?>?action=config&amp;option=deleteUser&amp;user=<?php echo $value['login']; ?>"><?php echo $value['login']; ?></a></li>
<?php
    } // login = current user
} //foreach
?>
            </ul>
        </li>
    </ul>
<?php } // count($users) > 1 ?>
<?php } // isConfigMode ?>
</nav>
<section id="content">
<?php if($isNote || $isDir) { ?>

    <header class="path"><?php echo $_GET['item']; ?></header>
<?php } // $isNote || $isDir ?>
