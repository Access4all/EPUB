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
return @copy($orig, $this->dir .$name);
}

function addFromString ($name, $data) {
$fn = $this->dir .$name;
if (false===@file_put_contents($fn, $data)) {
if (@mkdir(dirname($fn), 0777, true))
@file_put_contents($fn, $data);
}}

function moveFile ($oldName, $newName) {
return @rename($this->dir .$oldName, $this->dir .$newName);
}

function deleteName ($name) {
$fn = $this->dir .$name;
return @unlink($fn);
}

function isExtracted () { return true; }
function close () {}

function directEcho ($name) {
@readfile($this->dir .$name);
}

}
?>