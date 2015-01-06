<?php
require_once('core/kernel.php');
loadTranslation('install');

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

function main ($ok, $detail) {
global $root, $lang;
$pageTitle = getTranslation('InstallTitle') . ' - ' .getTranslation('ChecksTitle');
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
<button type="submit" $okdisabled>{$t('Next')}</button>
</p></form>
END;
require('instFooter.php');
}

}
?>