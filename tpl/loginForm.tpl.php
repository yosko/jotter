<?php
include PATH_TEMPLATE.'header.tpl.php';
?>
    <h2>Login</h2>
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