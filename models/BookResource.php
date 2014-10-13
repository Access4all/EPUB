<?php
require_once('core/kernel.php');

class BookResource {

function __construct ($a=null) {
if ($a) autofill($this,$a);
}

function updateContents ($contents) {
return $this->book->getFileSystem()->addFromString( $this->fileName, $contents); 
}

}
?>