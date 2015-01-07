<?php
require_once('core/kernel.php');
loadTranslation('install');

class Installer {

function checkAll () {
$checks = array();
$ok=true;
foreach(array(
'checkModRewrite',
'checkPhpVersion',
'checkDOM', 
'checkPDO', 'checkPDOMySQL',
'checkUploadMaxFilesize', 'checkPostMaxSize',
'checkRGlobals', 'checkSafeMode', 'checkMagicQuotes', 
'checkDataWritable', 'checkConfigWritable',
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

function checkDOM () {
$msg = phpversion('DOM');
return !!$msg;
}

function checkPDO (&$msg) {
$msg = phpversion('PDO');
return !!$msg;
}

function checkPDOMySQL (&$msg) {
$msg = phpversion('pdo_mysql');
return !!$msg;
}

function checkUploadMaxFilesize (&$msg) {
$msg = ini_get('upload_max_filesize');
$val = $this->filesizeToBytes($msg);
return $val >= 8*1024*1024;
}

function checkPostMaxSize (&$msg) {
$msg = ini_get('post_max_size');
$val = $this->filesizeToBytes($msg);
return $val >= 8*1024*1024;
}


function checkDataWritable () {
return is_writable('./data/');
}

function checkConfigWritable () {
return is_writable('core/config.php');
}

function checkRGlobals (&$msg) {
$msg = ini_get('register_globals');
return !$this->cfg2bool($msg);
}

function checkSafeMode (&$msg) {
$msg = ini_get('safe_mode');
return !$this->cfg2bool($msg);
}

function checkMagicQuotes (&$msg) {
$msg = ini_get('magic_quotes_gpc');
return !$this->cfg2bool($msg);
}

function cfg2bool ($b) {
switch(strtolower($b)) {
case '1': 
case 'on':
return true;
case '0':
case 'off':
return false;
default:
return false;
}}

function filesizeToBytes ($filesize) {
if (preg_match('/^(\d+(?:\.\d+)?)\s?([KMGT])B?$/i', $filesize, $m)) {
$mult = 1;
switch(strtoupper($m[2])) {
case 'K': $mult = 1024; break;
case 'M': $mult = 1024*1024; break;
case 'G': $mult = 1024*1024*1024; break;
case 'T': $mult = 1024*1024*1024*1024; break;
}
$filesize = floor($mult * $m[0]);
}
return floor($filesize);
}

function install ($info) {
global $db, $root, $booksdir, $lang, $langs;
if ($root==$info['root']) $newroot = 'dirname($_SERVER[\'PHP_SELF\'])';
else $newroot = '\''.addslashes($info['root']).'\'';
$newbooksdir = addslashes($info['booksdir']);
if (substr($newbooksdir, -1)=='/') $newbooksdir = substr($newbooksdir, 0, strlen($newbooksdir) -1);
foreach(array('dbname', 'dbhost', 'dbuser', 'dbpassword', 'dbtableprefix', 'adminName', 'adminPwd') as $x) $$x = addslashes($info[$x]);
if (!is_dir($booksdir) && !mkdir($booksdir)) return getTranslation('folderCreationFailed');
if (!is_dir("$booksdir/uploads") && !mkdir("$booksdir/uploads")) return getTranslation('folderCreationFailed');
try {
require_once('core/gdba.php');
$db = GDBA::MySQL($dbhost, $dbname, $dbuser, $dbpassword);
$sql = @file_get_contents('models/install.sql');
if (!$sql) return false;
$sql = str_replace('%', $dbtableprefix, $sql);
$sql = preg_split('/;$/m', $sql);
foreach($sql as $x) $db->exec($x);
$u = new User();
$u->name = $adminName;
$u->password = sha1("$adminName$adminPwd$adminName");
$u->uflags = 3;
$u->save();
} catch (Exception $e) { 
return getTranslation('dbCreationFailed');
}
@file_put_contents('core/config.php', <<<END
<?php
define('DEBUG', false);
define('LOCAL', false);
define('INSTALLED', true);
define('DB_HOST', '$dbhost');
define('DB_NAME', '$dbname');
define('DB_USER', '$dbuser');
define('DB_PASSWORD', '$dbpassword');
define('DB_TABLE_PREFIX', '$dbtableprefix');
\$root = $newroot;
\$booksdir = '$newbooksdir';
?>
END
);//end config.php
return true;
}


}
?>