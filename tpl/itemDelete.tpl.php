<?php include DIR_TPL.'header.tpl.php'; ?>
    <h2>Delete <?php echo ($isDir?'Directory':($isNote?'Note':'Notebook')); ?></h2>
    <p>
        You are about to delete <?php if($isDir) { echo 'a directory and everything inside it'; } elseif($isNote) { echo 'a note'; } else { echo 'the current notebook and everything inside it'; } ?>.
        There is no turning back!
    </p>
    <form method="post" action="?nb=<?php echo $notebookName; ?>&amp;action=delete<?php echo isset($_GET['item'])?'&amp;item='.$_GET['item']:''; ?>">
        <input id="delete" name="delete" type="submit" value="Delete it">
    </form>
<?php include DIR_TPL.'footer.tpl.php'; ?>