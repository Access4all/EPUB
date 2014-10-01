<?php
class UploadedFile extends ExternalFileSource {
var $fileName;
function __construct ($fn) {
$this->fileName = $fn;
}

function getFileName () { return $this->fileName; }
function getContents ()  { return @file_get_contents($this->getFileName()); }
function release () { @unlink($this->getFileName()); }
}
?>