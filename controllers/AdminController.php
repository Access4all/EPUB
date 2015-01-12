<?php
require_once('core/kernel.php');
checkLogged();
global $user;
if (!$user->isAdmin()) exit403();
loadTranslation('users');

class AdminController {

function index () {
$lp = LoginProvider::getInstance();
if (isset($_POST['username'])) {
$pwd = substr(sha1(time()), 3, 6);
$u = new User();
$u->id=0;
$u->displayName = $_POST['username'];
$u->name = Misc::toValidName($u->displayName);
$u->password = sha1($u->name.$pwd.$u->name);
$u->uflags = 1;
if (isset($_POST['uadmin'])) $u->uflags|=2;
if ($lp->saveUser($u)) {
$_SESSION['alertmsg'] = str_replace(array('%1', '%2'), array($u->name, $pwd), getTranslation('UserAdded'));
}
else $_SESSION['alertmsg'] = getTranslation('UserAddedE');
header("Location:{$_SERVER['REQUEST_URI']}");
exit();
}
$userList = $lp->getAllUsers();
$av = new AdminView();
$av->index($userList);
}

}
?>