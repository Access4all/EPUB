<?php
require_once('core/kernel.php');
loadTranslation('install');

function identity ($x) { return $x; }
function installdata ($x) { return $_SESSION['installdata'][$x]; }

class InstallView {

function index () {
global $root, $lang, $langs;
$t = 'getTranslation';
$pageTitle = getTranslation('InstallTitle');
require('instHeader.php');
echo <<<END
<form action="" method="post">
<p><label for="language">{$t('SelectLanguage')}:</label>
<select id="language" name="language">
END;
foreach($langs as $code=>$name) {
$selected = ($lang==$code?'selected="selected" ':'');
echo "<option value=\"$code\" $selected>$name</option>";
}
echo <<<END
</select></p>
<p>
<button disabled="disabled" type="button" onclick="window.location.href+='?prev';">{$t('Back')}</button>
<button type="button" onclick="window.location.reload();">{$t('Refresh')}</button>
<button type="submit">{$t('Next')}</button>
</p></Form>
END;
require('instFooter.php');
}

function checksPage ($ok, $detail) {
global $root, $lang;
$pageTitle = getTranslation('ChecksTitle');
require('instHeader.php');
$t = 'getTranslation';
echo <<<END
<table>
<thead><tr>
<th>{$t('VerifPoint')}</th>
<th>{$t('VerifFound')}</th>
<th>{$t('OKQ')}</th>
</tr></thead><tbody>
END;
foreach($detail as $name=>$info) {
list($result, $msg) = $info;
$result = getTranslation($result?'OK':'Failed');
$name = getTranslation($name);
if (!$msg) $msg = '&nbsp;';
echo <<<END
<tr>
<th scope="row">$name</th>
<td>$msg</td>
<td>$result</td>
</tr>
</tr>
END;
}
$okdisabled=($ok?'':'disabled="disabled" ');
$okmsg=getTranslation($ok?'okmsg1':'nokmsg1');
echo <<<END
</tbody></table>
<form action="" method="post">
<p>$okmsg</p>
<p>
<button type="button" onclick="window.location.href+='?prev';">{$t('Back')}</button>
<button type="button" onclick="window.location.reload();">{$t('Refresh')}</button>
<button type="submit" name="next" value="next" $okdisabled>{$t('Next')}</button>
</p></form>
END;
require('instFooter.php');
}

function configForm () {
global $root, $booksdir, $lang, $langs;
$pageTitle = getTranslation('InstallTitle');
require('instHeader.php');
$t='getTranslation';
$_ = 'identity';
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
<h2>{$t('Database')}</h2>
<p><label for="dbhost">{$t('Dbhost')}:</label>
<input type="text" id="dbhost" name="dbhost" value="{$_(DB_HOST)}" /></p>
<p><label for="dbname">{$t('Dbname')}:</label>
<input type="text" id="dbname" name="dbname" value="{$_(DB_NAME)}" /></p>
<p><label for="dbuser">{$t('Dbuser')}:</label>
<input type="text" id="dbuser" name="dbuser" value="{$_(DB_USER)}" /></p>
<p><label for="dbpassword">{$t('Dbpassword')}:</label>
<input type="password" id="dbpassword" name="dbpassword" value="{$_(DB_PASSWORD)}" /></p>
<p><label for="dbtableprefix">{$t('Dbtableprefix')}:</label>
<input type="text" id="dbtableprefix" name="dbtableprefix" value="{$_(DB_TABLE_PREFIX)}" /></p>
<p>{$t('DbNotes')}</p>
<h2>{$t('OtherConfig')}</h2>
<p><label for="root">{$t('RootDir')}:</label>
<input type="text" id=2root" name="root" value="$root" /></p>
<p><label for="booksdir">{$t('Booksdir')}:</label>
<input type="text" id="booksdir" name="booksdir" value="$booksdir" /></p>
<p>{$t('O2Notes')}</p>
<p>
<button type="button" onclick="window.location.href+='?prev';">{$t('Back')}</button>
<button type="reset">{$t('Reset')}</button>
<button type="submit" name="next" value="next">{$t('Next')}</button>
</p></form>
END;
require('instFooter.php');
}

function success () {
global $root;
$pageTitle = getTranslation('InstallFinished');
$t = 'getTranslation';
require('instHeader.php');
echo <<<END
<p>{$t('InstallSuccess')}</p>
<p><a href="$root/">{$t('GoToBookshelf')}</a></p>
END;
require('instFooter.php');
}


}
	?>