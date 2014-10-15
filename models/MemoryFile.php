<?php
class MemoryFile extends ExternalFileSource {
function __construct ($info=null) {  if ($info) autofill($this, $info);  }
function getFileName () { return @$this->fileName; }
function getContents ()  { return @$this->contents; }
function copyTo ($fs, $fn) { return @$fs->addFromString($fn, $this->contents ); }
function getMediaType () { return $this->mediaType; }
function release () {  }
}
?>