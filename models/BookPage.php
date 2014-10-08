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

function decodeEntities ($str) {
$a = array(
'&nbsp;' => utf8_encode(chr(160))
);
return str_replace(array_keys($a), array_values($a), $str);
}

function getDoc () {
if (!@$this->doc) {
$this->doc = DOM::loadXMLString( $this->decodeEntities($this->book->getContentsByFileName($this->fileName)));
}
return $this->doc;
}

function closeDoc () {
$this->doc=null;
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

function saveCloseDoc () {
$this->saveDoc();
$this->closeDoc();
}

function updateContents ($contents) {
if ($this->mediaType!='application/xhtml+xml') { $this->book->getFileSystem()->addFromString( $this->fileName, $contents); return; }
$contents = trim($this->decodeEntities($contents));
$contents = preg_replace_callback( '#<((?:img|br)\b.*?)>#ms', function($m){ 
if (substr($m[1], -1)!='/') $m[1].=' /';
return "<$m[1]>";
}, $contents);
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