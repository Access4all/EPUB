<?php
require_once('core/kernel.php');

class BookResource {

function __construct ($a=null) {
if ($a) autofill($this,$a);
}

function getEditorType () { return 'Text'; }
function getAdditionalPageOptions() { return null; }

function getContents () {
return $this->book->getFileSystem()->getFromName($this->fileName);
}

function updateContents ($contents) {
return $this->book->getFileSystem()->addFromString( $this->fileName, $contents); 
}

}
?>