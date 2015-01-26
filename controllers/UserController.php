<?php
require_once('core/kernel.php');
checkLogged();
loadTranslation('users');

class UserController {

function signoff () {
global $root;
session_destroy();
header("Location:$root");
exit();
}

function index () {
$this->profileForm();
}

function edit ($userId) {
global $user;
if (!$user->isADmin()) exit403();
$lp = LoginProvider::getInstance();
$u = $lp->getUserByName($userId);
if (!$u) exit404();
if ($u->isAdmin()) exit403();
if (isset($_POST['curpass'])) {
$error = null;
if ($_POST['newpass']!=$_POST['newpass2']) $error = 'PasswordDifferent';
else {
if ($_POST['newpass']) $u->password = sha1($u->name.$_POST['newpass'].$u->name);
$u->displayName = $_POST['dname'];
$lp->saveUser($u);
$error = 'UserChangedOK';
}
if ($error) $_SESSION['alertmsg'] = getTranslation($error);
header("Location:{$_SERVER['REQUEST_URI']}");
exit();
}
$uv = new UserView();
$uv->profileForm($u);
}

function profileForm () {
global $user;
if (isset($_POST['curpass'])) {
$error = null;
if (sha1($user->name.$_POST['curpass'].$user->name)!=$user->password) $error = 'WrongPassword';
else if ($_POST['newpass']!=$_POST['newpass2']) $error = 'PasswordDifferent';
else {
if ($_POST['newpass']) $user->password = sha1($user->name.$_POST['newpass'].$user->name);
$user->displayName = $_POST['dname'];
$lp = LoginProvider::getInstance();
$lp->saveUser($user);
$error = 'UserChangedOK';
}
if ($error) $_SESSION['alertmsg'] = getTranslation($error);
header("Location:{$_SERVER['REQUEST_URI']}");
exit();
}
$uv = new UserView();
$uv->profileForm($user);
}

}
?>