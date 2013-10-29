<?php include PATH_TEMPLATE.'header.tpl.php'; ?>
    <h2>Delete <?php echo ($isDir?'Directory':'Note'); ?></h2>
    <form method="post" action="?nb=<?php echo $notebookName; ?>&amp;action=delete&amp;item=<?php echo isset($_GET['item'])?$_GET['item']:''; ?>">
        <input id="delete" name="delete" type="submit" value="Delete it">
    </form>
<?php include PATH_TEMPLATE.'footer.tpl.php'; ?>