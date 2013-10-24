<?php include PATH_TEMPLATE.'header.tpl.php'; ?>
    <h2>Notebooks</h2>
    <ul><?php foreach($notebooks as $name => $notebook) { ?>

        <li><a href="<?php echo URL.'?nb='.$name; ?>"><?php echo urldecode($name); ?></a> (user: <?php echo $notebook['user']; ?>)</li><?php } ?>

        <li><a href="?action=add">Start a new notebook</a></li>
    </ul>
<?php include PATH_TEMPLATE.'footer.tpl.php'; ?>