<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>YosNote - Notebook Manager</title>
    <link rel="stylesheet" href="<?php echo URL; ?>tpl/style.css">
<?php if($isNote && $isEditMode) { ?>
    <script src="<?php echo URL; ?>tpl/js/ext/jquery-2.0.3.min.js"></script>
    <script src="<?php echo URL; ?>tpl/js/ext/jquery.hotkeys.js"></script>
    <script src="<?php echo URL; ?>tpl/js/ext/bootstrap.min.js"></script>
    <script src="<?php echo URL; ?>tpl/js/ext/bootstrap-wysiwyg.js"></script>
    <script src="<?php echo URL; ?>tpl/js/main.js"></script>
<?php } ?>
</head>
<body>
<div id="app">
<nav id="panel">
    <div class="toolbar">
        <ul class="actions">
<?php if(isset($notebook['tree'])) { ?>

            <li>
                <a href="<?php echo URL; ?>?nb=<?php echo $notebookName; ?>&amp;item=<?php echo $itemPath; ?>&amp;action=addnote" title="Add a new note inside the current directory">
                    <img src="<?php echo URL; ?>tpl/img/document--plus.png" alt="Add note">
                </a>
            </li>
            <li>
                <a href="<?php echo URL; ?>?nb=<?php echo $notebookName; ?>&amp;item=<?php echo $itemPath; ?>&amp;action=adddir" title="Add a new directory inside the current directory">
                    <img src="<?php echo URL; ?>tpl/img/folder--plus.png" alt="Add directory">
                </a>
            </li>
<?php } ?>

            <li class="secondary">
                <a href="<?php echo URL; ?>" title="List of notebooks">
                    <img src="<?php echo URL; ?>tpl/img/folders-stack.png" alt="Notebooks">
                </a>
            </li>
<?php if($user['isLoggedIn']) { ?>

            <li class="secondary">
                <a href="<?php echo URL; ?>?action=logout" title="Log out">
                    <img src="<?php echo URL; ?>tpl/img/door-open-out.png" alt="Logout">
                </a>
            </li>
<?php } ?>
        </ul>
    </div>
<?php if(isset($notebook['tree'])) { ?>

    <h1><a href="?nb=<?php echo $notebookName; ?>"><?php echo urldecode($notebookName); ?></a></h1>
<?php

function Tree2Html($tree, $nbName, $selectedPath, $parents = array()) {
    $level = count($parents);
    $html = str_repeat("\t", $level*2)."<ul";
    if($level == 0) {
        $html .= " id=\"root\" class=\"open\"";
    } else {
        $html .= " class=\"closed\"";
    }
    $html .= ">\r\n";
    
    foreach ($tree as $key => $value) {
        $isArray = is_array($value);
        $isNote = substr($key, -3) == '.md';
        if($isArray || $isNote) {
            //path to element
            $path = (!empty($parents)?implode('/', $parents).'/':'').$key;

            $html .= str_repeat("\t", $level*2+1)."<li class=\"".($isArray?"directory":"file").($path == $selectedPath?' selected':'')."\">";
            $html .= '<a href="'.URL.'?nb='.$nbName.'&amp;item='.$path.'">';
            $html .= basename($key, '.md');
            $html .= '</a>';

            //if array, show its children
            if($isArray) {
                $html .= "\r\n";
                $html .= Tree2Html($value, $nbName, $selectedPath, array_merge($parents, (array)$key));
                $html .= str_repeat("\t", $level*2+1);
            }

            $html .= "</li>\r\n";
        }
    }

    $html .= str_repeat("\t", $level*2)."</ul>\r\n";
    return $html;
}

echo Tree2Html($notebook['tree'], $notebookName, isset($_GET['item'])?$_GET['item']:'');

?>
<?php } ?>
</nav>
<section id="content">
    <div class="toolbar" id="item-toolbar" data-role="editor-toolbar" data-target="#editor">
        <ul class="actions btn-info">
<?php if($isNote || $isDir) { ?>

            <li>
                <a href="?nb=<?php echo $notebookName; ?>&amp;item=<?php echo $itemPath; ?>&amp;action=edit" title="Edit (rename) this <?php echo $isNote?'note':'directory'; ?>">
                    <img src="<?php echo URL; ?>tpl/img/<?php echo $isNote?'document':'folder'; ?>--pencil.png" alt="Edit <?php echo $isNote?'note':'directory'; ?>">
                </a>
            </li>
            <li>
                <a href="?nb=<?php echo $notebookName; ?>&amp;item=<?php echo $itemPath; ?>&amp;action=delete" title="Delete this <?php echo $isNote?'note':'directory'; ?>">
                    <img src="<?php echo URL; ?>tpl/img/<?php echo $isNote?'document':'folder'; ?>--minus.png" alt="Delete <?php echo $isNote?'note':'directory'; ?>">
                </a>
            </li>
<?php if($isNote && $isEditMode) { ?>
            <li class="secondary">
                <a href="#" id="save-button" class="disabled" title="Save this note">
                    <img src="<?php echo URL; ?>tpl/img/disk-black.png" alt="Save note">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-toggle="dropdown" title="Title">
                    <img src="<?php echo URL; ?>tpl/img/edit-heading.png" alt="Link"> &#x25BC;
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-toggle="dropdown" title="Alignement">
                    <img src="<?php echo URL; ?>tpl/img/edit-alignment.png" alt="Align"> &#x25BC;
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="bold" title="Bold (Ctrl+B)">
                    <img src="<?php echo URL; ?>tpl/img/edit-bold.png" alt="Bold">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="italic" title="Italic (Ctrl+I)">
                    <img src="<?php echo URL; ?>tpl/img/edit-italic.png" alt="Italic">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="underline" title="Underline (Ctrl+U)">
                    <img src="<?php echo URL; ?>tpl/img/edit-underline.png" alt="Underline">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="strikethrough" title="Strike">
                    <img src="<?php echo URL; ?>tpl/img/edit-strike.png" alt="Strike">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="insertunorderedlist" title="List">
                    <img src="<?php echo URL; ?>tpl/img/edit-list.png" alt="List">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="insertorderedlist" title="Ordered list">
                    <img src="<?php echo URL; ?>tpl/img/edit-list-order.png" alt="Ordered List">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="indent" title="Indent text (Tab)">
                    <img src="<?php echo URL; ?>tpl/img/edit-indent.png" alt="Indent">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="outdent" title="Outdent text (Shift+Tab)">
                    <img src="<?php echo URL; ?>tpl/img/edit-outdent.png" alt="Outdent">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-toggle="dropdown" title="Link">
                    <img src="<?php echo URL; ?>tpl/img/chain--plus.png" alt="Link">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="unlink" title="Remove link">
                    <img src="<?php echo URL; ?>tpl/img/chain--minus.png" alt="Remove link">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" title="Insert image (or drag &amp; drop it in your text)">
                    <img src="<?php echo URL; ?>tpl/img/image.png" alt="Image">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" id="mdash-button" title="Insert em dash">
                    &mdash;
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" id="source-button" title="View source">
                    <img src="<?php echo URL; ?>tpl/img/edit-code.png" alt="Source">
                </a>
            </li>
<?php
    } // $isNote
} // $isNote || $isDir
?>

        </ul>
<?php if($isNote && $isEditMode) { ?>
        <div id="insertLink">
            <input placeholder="http://" type="text" data-edit="createLink"/>
            <button type="button">Add</button>
        </div>
        <ul class="actions">
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="formatBlock h1" title="Title level 1">
                    <img src="<?php echo URL; ?>tpl/img/edit-heading-1.png" alt="Level 1">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="formatBlock h2" title="Title level 2">
                    <img src="<?php echo URL; ?>tpl/img/edit-heading-2.png" alt="Level 2">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="formatBlock h3" title="Title level 3">
                    <img src="<?php echo URL; ?>tpl/img/edit-heading-3.png" alt="Level 3">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="formatBlock h4" title="Title level 4">
                    <img src="<?php echo URL; ?>tpl/img/edit-heading-4.png" alt="Level 4">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="formatBlock h5" title="Title level 5">
                    <img src="<?php echo URL; ?>tpl/img/edit-heading-5.png" alt="Level 5">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="formatBlock h6" title="Title level 6">
                    <img src="<?php echo URL; ?>tpl/img/edit-heading-6.png" alt="Level 6">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="formatBlock p" title="Turn title into a paragraph">
                    <img src="<?php echo URL; ?>tpl/img/edit-heading-minus.png" alt="Paragraph">
                </a>
            </li>
        </ul>
        <ul class="actions">
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="justifyleft" title="Align left (Ctrl+L)">
                    <img src="<?php echo URL; ?>tpl/img/edit-alignment.png" alt="Align left">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="justifycenter" title="Align center (Ctrl+E)">
                    <img src="<?php echo URL; ?>tpl/img/edit-alignment-center.png" alt="Align center">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="justifyright" title="Align right (Ctrl+R)">
                    <img src="<?php echo URL; ?>tpl/img/edit-alignment-right.png" alt="Align right">
                </a>
            </li>
            <li class="secondary">
                <a href="#" class="ajax-formatter" data-edit="justifyfull" title="Justify text (Ctrl+J)">
                    <img src="<?php echo URL; ?>tpl/img/edit-alignment-justify.png" alt="Justify">
                </a>
            </li>
        </ul>
<?php } // $isNote ?>

    </div>
<?php if($isNote || $isDir) { ?>

    <header class="path"><?php echo $_GET['item']; ?></header>
<?php } // $isNote || $isDir ?>
