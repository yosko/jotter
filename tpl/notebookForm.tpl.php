<?php
$editNotebook = isset($notebook);
?>
<h2><?php echo $editNotebook?'Edit Notebook':'New Notebook'; ?></h2>
<form method="post" action="?action=add">
    <label for="name">Name</label>
    <input id="name" name="name" type="text"<?php if($editNotebook) { echo $notebook['name']; } ?>>
</form>