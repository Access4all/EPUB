<?php
require_once('core/kernel.php');

class SQLLoginProvider extends LoginProvider {

public function saveUser ($user) {
global $db;
if ($user->id<0) $user->id = $db->insert('insert into '.DB_TABLE_PREFIX.'Users (name, password, uflags, displayName) values (%s, %s, %d, %s)', $user->name, $user->password, $user->uflags, $user->displayName);
else $db->exec('replace into '.DB_TABLE_PREFIX.'Users (id, name, password, uflags, displayName) values (%d, %s, %s, %d, %s)', $user->id, $user->name, $user->password, $user->uflags, $user->displayName);
return true;
}

public function getUser (&$info) {
global $db;
$name = $info['login'];
$password = $info['password'];
$re = $db->query('select * from '.DB_TABLE_PREFIX.'Users where name = %s and password = sha1(%s)', $name, "$name$password$name");
if (!$re || $re->rowCount()<1) return null;
$re->setFetchMode(PDO::FETCH_CLASS, 'User');
return $re->fetch();
}

public function getUserByName ($name) {
global $db;
$re = $db->query('select * from '.DB_TABLE_PREFIX.'Users where name = %s or displayName = %s', $name, $name);
if (!$re || $re->rowCount()<1) return null;
$re->setFetchMode(PDO::FETCH_CLASS, 'User');
return $re->fetch();
}

public function getAllUsers () {
global $db;
$re = $db->query('select * from '.DB_TABLE_PREFIX.'Users order by name asc');
if (!$re || $re->rowCount()<1) return array();
$re->setFetchMode(PDO::FETCH_CLASS, 'User');
return $re->fetchAll();
}

public function getLoginFormURL () {
global $root;
$p = urlencode($_SERVER['REQUEST_URI']);
return "$root/login/index?u=$p";
}

}
?>