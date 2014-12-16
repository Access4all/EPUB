<?php
class UploadedFile extends ExternalFileSource {
var $fileName;
function __construct ($fn) { $this->fileName = $fn; }
function getFileName () { return $this->fileName; }
function getContents ()  { return @file_get_contents($this->getFileName()); }
function copyTo ($fs, $fn) { return $fs->addFromFile($fn, $this->getRealFileName() ); }
function release () { return @unlink($this->getFileName()); }
}
?>