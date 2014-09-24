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

function getStream ($fileName) {
return @fopen($this->dir .$name);
}

function isExtracted () { return true; }

}
?>