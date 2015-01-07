<?php
// GDBA : Generalized DataBase Access
// Copyright © 2011-2014 Quentin Cosendey
class SQLException extends Exception {
protected $query;
public function __construct ($query, $errno, $errstr, $file, $line) {
parent::__construct($errstr, $errno);
$this->query = $query;
$this->file = $file;
$this->line = $line;
}
public final function getSQL () { return $this->query; }
}

class GDBA extends PDO {
private $rqCount = 0;
private $sqlTime = 0;

public function __construct ($a,$b=null,$c=null) { parent::__construct($a,$b,$c); }
public static function MySQL ($host, $dbname, $user, $pass) {
return new GDBA("mysql:host=$host;dbname=$dbname", $user, $pass);
}
public static function SQLite ($filename, $_1=null, $_2=null, $_3=null) {
return new GDBA("sqlite:$filename");
}

private function throwSQLException ($sql) {
$t = $this->errorInfo();
$errno = $t[1];
$errstr = $t[2];
$trace = debug_backtrace();
$file = __FILE__;
$line = __LINE__;
for ($i=count($trace) -1; $i>=0; $i--) {
$t = $trace[$i];
if (isset($t['class']) && $t['class']=='GDBA') {
if (!isset($t['file'])) $t['file']='unknown';
if (!isset($t['line'])) $t['line']= -1;
$file = $t['file'];
$line = $t['line'];
break;
}}
throw new SQLException($sql, $errno, $errstr, $file, $line);
}
private function expand ($tab) {
for ($i=1, $n=count($tab); $i<$n; $i++) {
if (is_string($tab[$i])) {
if (is_numeric($tab[$i]) && !preg_match('/^0+\d+$/', $tab[$i])) $tab[$i] = (float)$tab[$i];
else $tab[$i] = $this->quote($tab[$i]);
}
else if (is_null($tab[$i])) $tab[$i] = 'NULL';
// other conversions
}
return call_user_func_array('sprintf', $tab);
}
public function query ($sql) {
if (func_num_args()>1) $sql = $this->expand(func_get_args());
else if (is_array($sql)) $sql = $this->expand($sql);
//echo "SQL: $sql <br />";
$this->rqCount++;
$t = microtime(true);
$stmt = parent::query($sql);
$this->sqlTime += microtime(true)-$t;
if (!$stmt) $this->throwSQLException($sql);
return $stmt;
}
public function exec ($sql) {
if (func_num_args()>1) $sql = $this->expand(func_get_args());
else if (is_array($sql)) $sql = $this->expand($sql);
$this->rqCount++;
$t = microtime(true);
$result = parent::exec($sql);
$this->sqlTime += microtime(true)-$t;
if (false===$result) $this->throwSQLException($sql);
return $result;
}
public function insert ($sql) {
if (func_num_args()>1) $sql = $this->expand(func_get_args());
$this->exec($sql);
return $this->lastInsertId();
}
public function count ($sql) {
if (func_num_args()>1) $sql = $this->expand(func_get_args());
$stmt = $this->query($sql);
$c = $stmt->rowCount();
$stmt->closeCursor();
return $c;
}
public function val  ($sql) {
if (func_num_args()>1) $sql = $this->expand(func_get_args());
$stmt = $this->query($sql);
$v = $stmt->fetchColumn();
$stmt->closeCursor();
return $v;
}
public function col ($sql) {
if (func_num_args()>1) $sql = $this->expand(func_get_args());
$stmt = $this->query($sql);
$stmt->setFetchMode(PDO::FETCH_COLUMN);
$a = $stmt->fetchAll();
$stmt->closeCursor();
return $a;
}
public function getRequestCount () { return $this->rqCount; }
public function getRequestTime () { return $this->sqlTime; }
}
?>
