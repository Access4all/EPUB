<?php
define('DOM_LIBXML_OPTIONS', LIBXML_COMPACT | LIBXML_NOBLANKS | LIBXML_NSCLEAN );//| LIBXML_NOERROR | LIBXML_NOWARNING);

class DOM {

static function newDocument () {
$doc = new DOMDocument2();
$doc->registerNodeClass('DOMElement', 'DOMElement2');
$doc->formatOutput = true;
$doc->preserveWhiteSpace = false;
return $doc;
}

static function loadXMLFile ($file) {
$doc = DOM::newDocument();
$doc->load($file, DOM_LIBXML_OPTIONS);
return $doc;
}

static function loadXMLString ($data) {
$doc = DOM::newDocument();
$doc->loadXML($data, DOM_LIBXML_OPTIONS);
return $doc;
}

static function loadHTMLString ($data) {
$doc = DOM::newDocument();
@$doc->loadHTML($data, DOM_LIBXML_OPTIONS);
return $doc;
}
}

class DOMElementIterator  implements Iterator {
var $it;
var $func;
var $idx;
function __construct ($it1, $c1) {
$this->func = $c1;
$this->it = $it1;
$this->idx = -1;
$this->next();
}

function valid () {  return $this->idx>=0 && $this->idx<$this->it->length; }
function current () {  return $this->it->item($this->idx);  }
function key () { return $this->idx; }
function rewind () {}
function next () {
$func = $this->func;
while(++$this->idx < $this->it->length) {
$item = $this->it->item($this->idx);
if ($func($item)) return;
}}
}

class DOMDocument2 extends DOMDocument {
function getRootElement () { return $this->documentElement; }

function __call ($name, $args) {
return call_user_func_array(array($this->documentElement, $name), $args);
}
}

class DOMElement2 extends DOMElement {

function __toString () { return $this->nodeValue; }

function getFirstElementByTagName ($tag) {
$nl = $this->getElementsByTagName($tag);
if (!$nl || $nl->length<=0) return null;
return $nl->item(0);
}

function getFirstElementByTagNameNs ($ns, $tag) {
$nl = $this->getElementsByTagNameNs($ns, $tag);
if (!$nl || $nl->length<=0) return null;
return $nl->item(0);
}

function getFirstElement ($func) {
foreach($this->getElements($func) as $e) return $e;
return null;
}

function getElements ($func) {
return new DOMElementIterator( $this->getElementsByTagName('*'), $func);
}

function setAttributes ($attrs) {
foreach($attrs as $name=>$value) $this->setAttribute($name, $value);
}

function appendElement ($tagName, $attrs = null) {
$el = $this->ownerDocument->createElement($tagName);
if ($attrs) $el->setAttributes($attrs);
$this->appendChild($el);
return $el;
}

function appendElementNs ($ns, $tagName, $attrs = null) {
$el = $this->ownerDocument->createElementNs($ns, $tagName);
if ($attrs) $el->setAttributes($attrs);
$this->appendChild($el);
return $el;
}

function insertElementBefore ($tagName, $ref, $attrs=null) {
$el = $this->ownerDocument->createElement($tagName);
if ($attrs) $el->setAttributes($attrs);
$this->insertBefore($el, $ref);
return $el;
}

function addText ($text) {
$tn = $this->ownerDocument->createTextNode($text);
$this->appendChild($tn);
return $this;
}

function removeAllChilds ($tagName = null) {
for ($i=$this->childNodes->length -1; $i>=0; $i--) {
$child = $this->childNodes->item($i);
if (!$tagName || $tagName==$child->nodeName) $this->removeChild($child);
}}

function saveHTML () { return $this->ownerDocument->saveHTML($this); }
function saveXML () { return $this->ownerDocument->saveXML($this); }
}

?>