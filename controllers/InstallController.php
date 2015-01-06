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
$a = array('indexPage', 'checks');
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
$inst = new Installer();
list($ok, $detail) = $inst->checkAll();
$iv->main($ok, $detail);
}

}
?>