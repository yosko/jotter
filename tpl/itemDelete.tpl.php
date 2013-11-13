<?php include DIR_TPL.'header.tpl.php'; ?>
    <h2>Delete <?php echo ($isDir?'Directory':'Note'); ?></h2>
    <p>
        You are about to delete a <?php if($isDir) { echo 'Directory and everything inside it'; } else { echo 'Note'; } ?>.
        There is no turning back!
    </p>
    <form method="post" action="?nb=<?php echo $notebookName; ?>&amp;action=delete&amp;item=<?php echo isset($_GET['item'])?$_GET['item']:''; ?>">
        <input id="delete" name="delete" type="submit" value="Delete it">
    </form>
<?php include DIR_TPL.'footer.tpl.php'; ?>