<?php
include PATH_TEMPLATE.'header.tpl.php';

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
    
<?php } elseif($option == 'editUser') { ?>
    
<?php } else { ?>
    <h2>Config</h2>

<?php
}

include PATH_TEMPLATE.'footer.tpl.php';
?>