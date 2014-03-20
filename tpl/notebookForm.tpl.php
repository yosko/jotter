<?php
include DIR_TPL.'header.tpl.php';
$editNotebook = isset($notebook);
?>
    <h2><?php echo $editNotebook?'Edit Notebook':'New Notebook'; ?></h2>
    <form method="post" action="?action=add">
        <p>
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="<?php echo isset($notebook['name'])?$notebook['name']:''; ?>" autofocus="autofocus">
<?php if(isset($errors['empty']) && $errors['empty']) { ?>
            <div class="error">Please enter a name for your new notebook.</div>
<?php } elseif(isset($errors['alreadyExists']) && $errors['alreadyExists']) { ?>
            <div class="error">A notebook already exists with this name. Please enter another one.</div>
<?php } ?>
        </p>
<?php if($editNotebook) { ?>
        <p>Notebook set to use Markdown or not?</p>
<?php } else { ?>
        <p>
            <label>Editor</label>
            <ul>
                <li>
                    <input type="radio" name="editor" id="wysiwyg" value="wysiwyg" checked="checked">
                    <label for="wysiwyg"><abbr title="What You See Is What You Get">WYSIWYG</abbr></label>
                </li>
                <li>
                    <input type="radio" name="editor" id="markdown" value="markdown">
                    <label for="markdown">Markdown</label>
                </li>
            </ul>
            <input type="checkbox" name="safe-wysiwyg" id="safe-wysiwyg" checked>
            <label for="safe-wysiwyg">
                Make <abbr title="What You See Is What You Get">WYSIWYG</abbr> <strong>more safe</strong>:
                this makes sure to remove unsafe content when saving text pasted from a webpage
                (you might loose a bit more formatting in the process).
            </label>
        </p>
<?php } ?>
        <input type="submit" value="Create notebook">
    </form>
<?php include DIR_TPL.'footer.tpl.php'; ?>