<?php
require_once('core/kernel.php');
loadTranslation('install');

class Installer {

function checkAll () {
$checks = array();
$ok=true;
foreach(array(
'checkPhpVersion',
'checkModRewrite',
) as $c) {
$msg='';
$result = $this->$c($msg);
$ok = $ok && $result;
$checks[$c] = array($result, $msg);
}
return array($ok, $checks);
}

function checkPhpVersion (&$msg) {
$msg = phpversion();
if (!defined('PHP_VERSION_ID')) {
list($major, $minor, $build) = explode('.', $msg);
define('PHP_VERSION_ID', $major*10000+$minor*100+$build);
}
return PHP_VERSION_ID>=50300;
}

function checkModRewrite (&$msg) {
$mods = apache_get_modules();
return in_array('mod_rewrite', $mods);
}


}
?>