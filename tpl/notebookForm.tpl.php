<?php
$editNotebook = isset($notebook);
?>
<h2><?php echo $editNotebook?'Edit Notebook':'New Notebook'; ?></h2>
<form method="post" action="?action=add">
    <label for="name">Name</label>
    <input id="name" name="name" type="text" value="<?php echo isset($notebook['name'])?$notebook['name']:''; ?>">
<?php if(isset($errors['empty']) && $errors['empty']) { ?>
    <div class="error">Please enter a name for your new notebook.</div>
<?php } elseif(isset($errors['alreadyExists']) && $errors['alreadyExists']) { ?>
    <div class="error">A notebook already exists with this name. Please enter another one.</div>
<?php } ?>
</form>