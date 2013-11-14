<?php include DIR_TPL.'header.tpl.php'; ?>
    <h2><img src="<?php echo URL_TPL; ?>img/jotter.png" alt="Jotter"></h2>
    <h2>Notebooks</h2>
    <ul>
<?php
if(!empty($notebooks[$user['login']])) {
    foreach($notebooks[$user['login']] as $name => $notebook) {
?>

        <li><a href="<?php echo URL.'?nb='.$name; ?>"><?php echo urldecode($name); ?></a></li>
<?php } } ?>

        <li><a href="?action=add">Start a new notebook</a></li>
    </ul>
<?php include DIR_TPL.'footer.tpl.php'; ?>