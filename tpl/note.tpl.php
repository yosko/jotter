<?php include DIR_TPL.'header.tpl.php'; ?>
<?php if($isWysiwyg) { ?>

    <article id="editor"><?php echo $note; ?></article>
<?php } else { ?>

    <textarea id="editor"><?php echo $note; ?></textarea>
<?php } ?>
<?php include DIR_TPL.'footer.tpl.php'; ?>