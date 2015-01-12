<?php
require_once('core/kernel.php');
loadTranslation('users');

class UserView {

function profileForm ($user) {
global $root, $lang, $langs;
$t = 'getTranslation';
$h = 'htmlspecialchars';
$pageTitle = getTranslation('UserProfileTitle');
require('lgnHeader.php');
if (isset($_SESSION['alertmsg'])) {
$msg = $_SESSION['alertmsg'];
unset($_SESSION['alertmsg']);
echo <<<END
<div role="alert" id="alert" class="error">
<p><strong>$msg</strong></p>
</div>
END;
}
echo <<<END
<form action="" method="post">
<p><label for="uname">{$t('Login')}:</label>
<input type="text" readonly="readonly" id="uname" value="{$user->name}"" /></p>
<p><label for="curpass">{$t('CurrentPassword')}:</label>
<input type="password" name="curpass" id="curpass" /></p>
<p><label for="newpass">{$t('NewPassword')}:</label>
<input type="password" name="newpass" id="newpass" /></p>
<p><label for="newpass2">{$t('NewPassword2')}:</label>
<input type="password" name="newpass2" id="newpass2" /></p>
<p><label for="dname">{$t('DisplayName')}:</label>
<input type="text" id="dname" name="dname" value="{$h($user->displayName)}" /></p>
<p>{$t('DisplayNameHelp')}</p>
<p><button type="submit">{$t('UpdPrfBtn')}</button></p>
</form>
END;
require('lgnFooter.php');
}

}
	?>