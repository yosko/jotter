<?php include DIR_TPL.'header.tpl.php'; ?>
<?php if($isWysiwyg) { ?>

    <article id="editor"><?php echo htmlspecialchars_decode($note); ?></article>
<?php } else { ?>

    <textarea id="editor"><?php echo htmlspecialchars_decode($note); ?></textarea>
<?php } ?>
<?php include DIR_TPL.'footer.tpl.php'; ?>
