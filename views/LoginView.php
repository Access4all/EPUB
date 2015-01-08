<?php
require_once('core/kernel.php');
loadTranslation('users');

class LoginView {

function index () {
global $root, $lang, $langs;
$t = 'getTranslation';
$pageTitle = getTranslation('LoginTitle');
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
<p>{$t('PleaseLogin')}</p>
<p><label for="login">{$t('Login')}:</label>
<input type="text" id="login" name="login" /></p>
<p><label for="password">{$t('Password')}:</label>
<input type="password" id="password" name="password" /></p>
<p><label for="language">{$t('Language')}:</label>
<select id="language" name="language">
END;
foreach($langs as $code=>$label) {
$selected = ($code==$lang?'selected="selected" ':'');
echo "<option value=\"$code\" $selected>$label</option>";
}
echo <<<END
</select></p>
<p><button type="submit">{$t('LoginBtn')}</button></p>
</form>
END;
require('lgnFooter.php');
}

}
	?>