<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'epuba4all');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DEBUG', true);
define('LOCAL', $_SERVER['REMOTE_ADDR']=='127.0.0.1' || 0===strpos($_SERVER['SERVER_ADDR'], '192.168.') || 0===strpos($_SERVER['SERVER_ADDR'], '10.0.'));

error_reporting(E_ALL&~E_STRICT);
function exceptionHandler ($e) {
@ob_end_clean();
if (LOCAL || DEBUG) die(nl2br($e));
else exit500();
}
set_exception_handler('exceptionHandler');
mb_internal_encoding('UTF-8');

function exit403 () { 
global $db, $lang, $root;
require_once('403.php'); 
}
function exit404 () { 
global $db, $lang, $root;
require_once('404.php'); 
}
function exit500 () { 
global $db, $lang, $root;
require_once('500.php'); 
}
function exit503 () { 
global $db, $lang, $root;
require_once('503.php'); 
}

function autoloadfunc ($className) {
$fn = "core/$className.php";
if (file_exists($fn)) { require_once($fn); return; }
$fn = "controllers/$className.php";
if (file_exists($fn)) { require_once($fn); return; }
$fn = "models/$className.php";
if (file_exists($fn)) { require_once($fn); return; }
$fn = "views/$className.php";
if (file_exists($fn)) { require_once($fn); return; }
}
spl_autoload_register('autoloadfunc');

function autofill ($obj, $a) {
foreach($a as $k=>$v) $obj->$k=$v;
}

$db = null; 
try {
if (!defined('NODB')) require_once('gdba.php');
if (LOCAL) $db = GDBA::MySQL(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
$db->exec('set names utf8');
} catch (Exception $e) { exit503(); }

session_start();
$langs = array('en'=>'English');
$lang = 'en';
if (isset($_GET['language'])) $lang = $_GET['language'];
else if (isset($_SESSION['language'])) $lang = $_SESSION['language'];
else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
}
if (!array_key_exists($lang, $langs)) $lang='en';
$_SESSION['language'] = $lang;
$root = dirname($_SERVER['PHP_SELF']);
$booksdir = './data';
ob_start();

function parse_ini_file_2 ($filename) {
$t = array();
foreach (file($filename, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES) as $row) {
if (preg_match('%^\s*(.*?)\s*=\s*"?(.*)"?\s*$%', $row, $match)) {
$t[$match[1]] = trim($match[2]);
}}
return $t;
}

$transl = array();
function loadTranslation ($a) {
global $transl, $lang;
$a = "./lang/$lang/$a.txt";
if (file_Exists($a)) {
$t = parse_ini_file_2($a);
$transl = array_merge($transl, $t);
return true;
}
return false;
}
function getTranslation ($a) {
global $transl;
return isset($transl[$a])? $transl[$a] : "~$a~";
}

function pathResolve ($ref, $file) {
$parts = explode('/', dirname($ref).'/'.$file);
while(($i=array_search('..', $parts))>0) {
array_splice($parts, $i -1, 2);
}
return implode('/', $parts);
}

function pathRelativize ($ref, $file) {
$parts1 = explode('/', dirname($ref));
$parts2 = explode('/', $file);
$common=0;
while($common<count($parts1) && $common<count($parts2) && $parts1[$common]==$parts2[$common]) $common++;
array_splice($parts2, 0, $common);
array_splice($parts1, 0, $common);
for ($i=0; $i<count($parts1); $i++) array_unshift($parts2, '..');
return implode('/', $parts2);
}

?>