<?php
include PATH_TEMPLATE.'header.tpl.php';
?>
    <h2><?php echo $appInstalled?'Login':'Install'; ?></h2>
<?php if(!$appInstalled) { ?>
    <p>You are about to install Jotter and create your first user account.</p>
    <p>Checking requirements:</p>
    <ul>
        <li class="<?php echo $phpMinVersion?'success':'error'; ?>">
            PHP <?php echo PHP_VERSION; ?> installed (required at least PHP <?php echo $phpMinVersion; ?>):
            <?php echo $phpMinVersion?'OK':'KO'; ?>
        </li>
        <li class="<?php echo $isWritable?'success':'error'; ?>">
            Write access to create <code>notebooks/</code> directory:
            <?php echo $isWritable?'OK':'KO'; ?>
        </li>
    </ul>
    <p>Enter the desired login and password. You will be immediatly logged in:</p>
<?php } // if !$appInstalled ?>
    <form id="loginForm" method="post" action="">
        <p>
            <label for="login">Login</label>
            <input type="text" autofocus="autofocus" name="login" id="login" value="<?php echo isset($_POST['login'])?$_POST['login']:''; ?>">
<?php if(isset($user['error']['unknownLogin']) && $user['error']['unknownLogin']) { ?>
            <span class="error">Unknown login</span>
<?php } ?>
        </p>

        <p>
            <label for="password">Password</label>
            <input type="password" name="password" id="password">
<?php if(isset($user['error']['wrongPassword']) && $user['error']['wrongPassword']) { ?>
            <span class="error">Wrong password</span>
<?php } ?>
        </p>

        <p>
            <input type="checkbox" name="remember" id="remember" value="remember">
            <label for="remember">Remember me</label>
        </p>

        <input type="submit" name="submitLoginForm" id="submitLoginForm" value="Se connecter" />
    </form>
<?php include PATH_TEMPLATE.'footer.tpl.php'; ?>