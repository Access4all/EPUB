<?php
class User {
var $id, $uflags, $name;

public function isEnabled () { return $this->uflags&1; }
public function isAdmin () { return $this->uflags&2; }

}
?>