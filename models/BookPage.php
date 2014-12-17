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

function getDoc () {
if (!@$this->doc) {
$this->doc = DOM::loadXMLString( trim( DOM::decodeEntities($this->book->getContentsByFileName($this->fileName))));
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
//Php DOM hack:  Make sure that the following tags are never self-closed; having them self-closed can make trouble in certain browsers
foreach(array('script', 'iframe') as $tgn) {
foreach($doc->getElementsByTagName($tgn) as $el) {
if (!$el->hasChildNodes()) $el->appendText('');
}}
$this->book->getFileSystem()->addFromString(
$this->fileName,
$doc->saveXML()
);//
}

function saveCloseDoc () {
$this->saveDoc();
$this->closeDoc();
}

function getEditorType () { return 'HTML'; }

function updateContents ($contents) {
if ($this->mediaType!='application/xhtml+xml') return parent::updateContents($contents);
$contents = preg_replace('@epub:\w+=@', 'xmlns:epub="'.NS_EPUB.'" $0', $contents); // Empyrically solve the appendXML/HTML DOM function when having attributes from other namespaces
$doc = $this->getDoc();
$body = $doc->getFirstElementByTagName('body');
$body->removeAllChilds();
$body->appendHTML($contents);
$cssFound = false;
$cssMasterFile = $this->book->getOption('cssMasterFile', 'EPUB/css/epub3.css');
foreach($doc->getElementsByTagName('link') as $link) {
if ($link->getAttribute('rel')!='stylesheet') continue;
$href = $link->getAttribute('href');
$href = pathResolve($this->fileName, $href);
if ($href==$cssMasterFile) { $cssFound=true; break; }
}
if (!$cssFound) {
$head = $doc->getFirstElementByTagName('head');
$head->appendElement('link', array('rel'=>'stylesheet', 'href'=>pathRelativize($this->fileName, $cssMasterFile)));
}
$this->saveDoc();
}

function updatePageSettings (&$info) {
$doc = $this->getDoc();
$bookOpfModified = false;
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
if ($this->linear == isset($info['linear'])) {
$this->linear = !isset($info['linear']);
$this->book->spineModified = true;
$this->book->setOption('tocNeedRegen', true);
$bookOpfModified = true;
}
$this->saveDoc();
if ($bookOpfModified) $this->book->saveOpf();
}

function initNewPage ($b) {}

}
?>