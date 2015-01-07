<?php
class User {
var $id, $uflags, $name;

public function isEnabled () { return $this->uflags&1; }
public function isAdmin () { return $this->uflags&2; }

public function save () {
global $db;
if ($this->id<0) $this->id = $db->insert('insert into '.DB_TABLE_PREFIX.'Users (name, password, uflags) values (%s, %s, %d)', $this->name, $this->password, $this->uflags);
else $db->exec('replace into '.DB_TABLE_PREFIX.'Users (id, name, password, uflags) values (%d, %s, %s, %d)', $this->id, $this->name, $this->password, $this->uflags);
}

public static function getByNameAndPassword ($name, $password) {
global $db;
$re = $db->query('select * from '.DB_TABLE_PREFIX.'Users where name = %s and password = sha1(%s)', $name, "$name$password$name");
if (!$re || $re->rowCount()<1) return null;
$re->setFetchMode(PDO::FETCH_CLASS, 'User');
return $re->fetch();
}

}
?>