<?php
require_once('core/kernel.php');

class LoginProvider {
static $lp = null;

public static function getInstance () {
if (!LoginProvider::$lp) LoginProvider::$lp = new SQLLoginProvider();
return LoginProvider::$lp;
}

public function getDefaultUser () { return null; }
public function getUser (&$info) { return null; }
public function getUserByName ($name) { return null; }
public function saveUser ($user) { return false; }
public function getLoginFormURL () {return null; }
}

?>