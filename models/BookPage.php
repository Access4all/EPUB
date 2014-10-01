<?php
require_once('core/kernel.php');

class BookPage {

function __construct ($a=null) {
if ($a) autofill($this,$a);
}

function __wakeup () {
$this->doc = null;
}

function __sleep () {
if (@$this->doc) $this->doc = null;
return array_keys(get_object_vars($this));
}

function getDoc () {
if (!@$this->doc) {
$this->doc = DOM::loadXMLString( $this->book->getContentsByFileName($this->fileName) );
}
return $this->doc;
}

function getTitle () {
$doc = $this->getDoc();
$title = $doc->getFirstElementByTagName('title');
if (!$title) return 'Untitled document';
else return strval($title);	
}

function saveDoc () {
$doc = $this->getDoc();
$this->book->getFileSystem()->addFromString(
$this->fileName,
$doc->saveXML()
);//
}

function updateContents ($contents) {
if ($this->mediaType!='application/xhtml+xml') { $this->book->getFileSystem()->addFromString( $this->fileName, $contents); return; }
$doc = $this->getDoc();
$frag = $doc->createDocumentFragment();
$frag->appendXML($contents);
$body = $doc->getFirstElementByTagName('body');
$body->removeAllChilds();
$body->appendChild($frag);
$this->saveDoc();
}

function updatePageSettings ($info) {
$doc = $this->getDoc();
if (isset($info['title'])) {
$title = $doc->getFirstElementByTagName('title');
$title->nodeValue = trim($info['title']);
}
$this->saveDoc();
}

}
?>