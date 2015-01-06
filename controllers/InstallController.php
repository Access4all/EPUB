<?php
require_once('core/kernel.php');
loadTranslation('install');

class InstallController {

function index ($name) {
$iv = new InstallView();
$step = @floor($_SESSION['istep']);
if (isset($_GET['prev'])) {
$step = max(0, $step -1);
$_SESSION['istep'] = $step;
$_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REWQUEST_URI'], '?'));
header("Location:{$_SERVER['REQUEST_URI']}");
exit();
}
$a = array('indexPage', 'checks', 'cfgform');
$f = $a[$step];
$this->$f($iv);
}

function indexPage ($iv) {
if (isset($_POST['language'])) {
$_SESSION['language'] = $_POST['language'];
$_SESSION['istep']=1;
header("Location:{$_SERVER['REQUEST_URI']}");
exit();
}
$iv->index();
}

function checks ($iv) {
if (isset($_POST['next'])) {
$_SESSION['istep']=2;
header("Location:{$_SERVER['REQUEST_URI']}");
exit();
}
$inst = new Installer();
list($ok, $detail) = $inst->checkAll();
$iv->checksPage($ok, $detail);
}

function cfgform ($iv) {
global $root;
if ($_POST && count($_POST)>0) {
$inst = new Installer();
$result = $inst->install($_POST);
if ($result===true){
header("Location:$root/install/success");
exit();
}
else {
$_SESSION['installdata'] = $_POST;
$_SESSION['alertmsg'] = $result;
header("Location:{$_SERVER['REQUEST_URI']}");
exit();
}}
$iv->configForm();
}

function success () {
$iv = new InstallView();
$iv->success();
}

}
?>