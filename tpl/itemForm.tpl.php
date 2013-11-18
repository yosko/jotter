<?php
include DIR_TPL.'header.tpl.php';
$editItem = $_GET['action'] == 'edit';
if(!isset($isDir))
    $isDir = $_GET['action'] == 'adddir';
?>
    <h2><?php echo ($editItem?'Edit ':'New ').($isDir?'Directory':'Note'); ?></h2>
    <form method="post" action="?nb=<?php echo $notebookName; ?>&amp;action=<?php echo $_GET['action']; ?>&amp;item=<?php echo isset($_GET['item'])?$_GET['item']:''; ?>">
        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="<?php echo isset($item['name'])?$item['name']:''; ?>" autofocus="autofocus">
    <?php if(isset($errors['empty']) && $errors['empty']) { ?>
        <div class="error">Please enter a name for your new item.</div>
    <?php } elseif(isset($errors['sameName']) && $errors['sameName']) { ?>
        <div class="error">The item already has that name.</div>
    <?php } elseif(isset($errors['alreadyExists']) && $errors['alreadyExists']) { ?>
        <div class="error">An item already exists with this name in this directory. Please enter another one.</div>
    <?php } ?>
    </form>
<?php include DIR_TPL.'footer.tpl.php'; ?>