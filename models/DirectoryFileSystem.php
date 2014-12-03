<?php
class DirectoryFileSystem {
var $dir;
public function __construct ($d) { 
if (substr($d,-1)!='/') $d.='/';
$this->dir = $d; 
}

function getFromName ($name) {
return @file_get_contents($this->dir .$name);
}

function addFromFile ($name, $orig) {
$fn = $this->dir .$name;
if (false === @copy($orig, $fn)) {
if (@mkdir(dirname($fn), 0777, true)) return @copy($orig, $fn);
}
else return true;
return false;
}

function addFromString ($name, $data) {
$fn = $this->dir .$name;
if (false===@file_put_contents($fn, $data)) {
if (@mkdir(dirname($fn), 0777, true)) @file_put_contents($fn, $data);
}}

function moveFile ($oldName, $newName) {
if (@rename($this->dir .$oldName, $this->dir .$newName)) return true;
$dir = dirname($this->dir .$newName);
return @mkdir($dir, 0777, true) && @rename($this->dir .$oldName, $this->dir .$newName);
}

function deleteName ($name) {
$fn = $this->dir .$name;
if (!@unlink($fn)) return false;
$dir = dirname($fn);
$this->removeDirectoryIfEmpty($dir);
return true;
}

function removeDirectoryIfEmpty ($dirname) {
$dir = $this->dir .$dirname;
if (Misc::isDirEmpty($dir)) return @rmdir($dir);
else return false;
}

function isExtracted () { return true; }
function close () {}

function directEcho ($name) {
@ob_end_clean();
@readfile($this->dir .$name);
exit();
}

}
?>