<?php
include PATH_TEMPLATE.'header.tpl.php';
$editItem = $_GET['action'] == 'edit';
$isDir = $_GET['action'] == 'adddir';
?>
    <h2><?php echo ($editItem?'Edit ':'New ').($isDir?'Directory':'Note'); ?></h2>
    <form method="post" action="?nb=<?php echo $notebookName; ?>&amp;action=<?php echo $_GET['action']; ?>">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="<?php echo isset($item['name'])?$item['name']:''; ?>">
    <?php if(isset($errors['empty']) && $errors['empty']) { ?>
        <div class="error">Please enter a name for your new item.</div>
    <?php } elseif(isset($errors['alreadyExists']) && $errors['alreadyExists']) { ?>
        <div class="error">An item already exists with this name in this directory. Please enter another one.</div>
    <?php } ?>
    </form>
<?php include PATH_TEMPLATE.'footer.tpl.php'; ?>