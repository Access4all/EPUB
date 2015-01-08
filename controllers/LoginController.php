<?php
require_once('core/kernel.php');
loadTranslation('users');

class LoginController {

public function index () {
if ($_POST&&count($_POST)>0) {
global $user, $root;
$lp = LoginProvider::getInstance();
$_SESSION['user'] = $user = $lp->getUser($_POST);
if ($user) {
$redir = (isset($_GET['u'])? $_GET['u'] : $root);
header("Location:$redir");
}
else {
$_SESSION['alertmsg'] = getTranslation('InvalidLogin');
header("Location:{$_SERVER['REQUEST_URI']}");
}
exit();
}
else {
$lv = new LoginView();
$lv->index();
}}

}
?>