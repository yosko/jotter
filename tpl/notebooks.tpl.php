<h2>Notebooks</h2>
<ul><?php foreach($notebooks as $name => $notebook) { ?>

    <li><a href="<?php echo URL; ?>?nb=<?php echo $name; ?>"><?php echo $name; ?></a> (user: <?php echo $notebook['user']; ?>)</li><?php } ?>

    <li><a href="?action=add">Start a new notebook</a></li>
</ul>