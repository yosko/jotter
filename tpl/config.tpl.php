<?php
include DIR_TPL.'header.tpl.php';

if($option == 'myPassword') { ?>
    <h2>Change my password</h2>
    <form id="NewPasswordForm" method="post" action="">
        <div>
            <label for="password">New password</label>
            <input type="password" name="password" id="password" autofocus="autofocus">
<?php if(isset($error['emptyPassword']) && $error['emptyPassword']) { ?>
            <span class="error">Please enter a password</span>
<?php } elseif(isset($error['save']) && $error['save']) { ?>
            <span class="error">Unknown error while saving password</span>
<?php } //error ?>
        </div>
        <input type="submit" name="submitNewPassword" id="submitNewPassword" value="Save password" />
    </form>
    
<?php } elseif($option == 'addUser') { ?>
    <form id="userForm" method="post" action="">
        <p>
            <label for="login">Login</label>
            <input type="text" autofocus="autofocus" name="login" id="login" value="<?php echo isset($_POST['login'])?$_POST['login']:''; ?>">
<?php if(isset($errors['emptyLogin']) && $errors['emptyLogin']) { ?>
            <span class="error">Login must not be empty</span>
<?php } elseif(isset($errors['notAvailable']) && $errors['notAvailable']) { ?>
            <span class="error">Login not available</span>
<?php } ?>
        </p>

        <p>
            <label for="password">Password</label>
            <input type="password" name="password" id="password">
<?php if(isset($errors['emptyPassword']) && $errors['emptyPassword'] && !empty($login)) { ?>
            <span class="error">Password must not be empty</span>
<?php } ?>
        </p>

        <input type="submit" name="submitUserForm" id="submitUserForm" value="Save user" />
    </form>
<?php } elseif($option == 'deleteUser') { ?>
    <h2>Delete user <?php echo $login; ?></h2>
    <p>
        You are about to delete a user.
        There is no turning back!
    </p>
    <form method="post" action="">
        <input id="deleteUserSubmit" name="deleteUserSubmit" type="submit" value="Delete user <?php echo $login; ?>">
    </form>
<?php } else { ?>
    <h2>Config</h2>

<?php
}

include DIR_TPL.'footer.tpl.php';
?>