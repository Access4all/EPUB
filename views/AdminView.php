<?php
require_once('core/kernel.php');
loadTranslation('users');

class AdminView {

function index ($userList) {
global $root, $lang, $langs;
$t = 'getTranslation';
$h = 'htmlspecialchars';
$pageTitle = getTranslation('AdminAUTitle');
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
<h2>{$t('UsersList')}</h2>
<ul>
END;
foreach($userList as $u) {
$url = "$root/user/{$u->name}/edit";
echo "<li><a href=\"$url\">{$u->name} ({$u->displayName})</a></li>";
}
echo <<<END
</ul>
<h2>{$t('AddNewUser')}</h2>
<form action="" method="post">
<p>
<label for="username">{$t('Login')}: </label>
<input type="text" id="username" name="username" />
<input type="checkbox" name="uadmin" id="uadmin" /><label for="uadmin">{$t('Administrator')}</label>
<button type="submit">{$t('AddBtn')}</button>
</p>
</form>
END;
require('lgnFooter.php');
}

}
	?>