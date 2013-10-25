<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>YosNote - Notebook Manager</title>
    <link rel="stylesheet" href="<?php echo URL; ?>tpl/style.css">
</head>
<body>
<div id="app">
<?php if(isset($notebook['tree'])) { ?><nav id="panel">
    <ul class="actions">
        <li><a href="<?php echo URL; ?>?nb=<?php echo $notebookName; ?>&amp;item=<?php echo $itemPath; ?>&amp;action=addnote" title="Add a new note inside the current directory"><img src="<?php echo URL; ?>tpl/img/document--plus.png" alt="Add note"></a></li>
        <li><a href="<?php echo URL; ?>?nb=<?php echo $notebookName; ?>&amp;item=<?php echo $itemPath; ?>&amp;action=adddir" title="Add a new directory inside the current directory"><img src="<?php echo URL; ?>tpl/img/folder--plus.png" alt="Add directory"></a></li>
    </ul>
    <h1><?php echo urldecode($notebookName); ?></h1>
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
</nav><?php } ?>
<section id="content">
    <ul class="actions">
        <li class="right"><a href="?nb=<?php echo $notebookName; ?>&amp;item=<?php echo $itemPath; ?>&amp;action=edit" title="Edit (rename) this note"><img src="<?php echo URL; ?>tpl/img/document--pencil.png" alt="Edit note"></a></li>
        <li class="right"><a href="?nb=<?php echo $notebookName; ?>&amp;item=<?php echo $itemPath; ?>&amp;action=delete" title="Delete this note"><img src="<?php echo URL; ?>tpl/img/document--minus.png" alt="Delete note"></a></li>
        <li class="right"><a href="?nb=<?php echo $notebookName; ?>&amp;item=<?php echo $itemPath; ?>&amp;action=edit" title="Edit (rename) this directory"><img src="<?php echo URL; ?>tpl/img/folder--pencil.png" alt="Edit directory"></a></li>
        <li class="right"><a href="?nb=<?php echo $notebookName; ?>&amp;item=<?php echo $itemPath; ?>&amp;action=delete" title="Delete this directory (and all the notes inside it)"><img src="<?php echo URL; ?>tpl/img/folder--minus.png" alt="Delete directory"></a></li>
    </ul>
