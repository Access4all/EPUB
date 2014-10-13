<?php
require_once('core/kernel.php');

class BookPage extends BookResource {

function __construct ($a=null) {
parent::__construct($a);
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

function getLanguage () {
$doc = $this->getDoc();
$html = $doc->documentElement;
if (!$html || !$html->hasAttribute('lang')) return @$this->book->language;
else return $html->getAttribute('lang');
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
if ($this->mediaType!='application/xhtml+xml') return parent::updateContents($contents);
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
$cssFound = false;
foreach($doc->getElementsByTagName('link') as $link) {
if ($link->getAttribute('rel')!='stylesheet') continue;
$href = $link->getAttribute('href');
$href = pathResolve($this->fileName, $href);
if ($href=='EPUB/css/epub3.css') { $cssFound=true; break; }
}
if (!$cssFound) {
$head = $doc->getFirstElementByTagName('head');
$head->appendElement('link', array('rel'=>'stylesheet', 'href'=>pathRelativize($this->fileName, 'EPUB/css/epub3.css')));
}
$this->saveDoc();
}

function updatePageSettings ($info) {
$doc = $this->getDoc();
if (isset($info['title'])) {
$title = $doc->getFirstElementByTagName('title');
$title->nodeValue = trim($info['title']);
}
if (isset($info['language'])) {
$lng = $info['language'];
$html = $doc->documentElement;
$html->setAttribute('lang', $lng);
$html->setAttribute('xml:lang', $lng);
}
$this->saveDoc();
}

}
?>