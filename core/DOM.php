<?php
define('DOM_LIBXML_OPTIONS', LIBXML_COMPACT | LIBXML_NOBLANKS | LIBXML_NSCLEAN );//| LIBXML_NOERROR | LIBXML_NOWARNING);

class DOM {

static function newDocument () {
$doc = new DOMDocument2();
$doc->registerNodeClass('DOMElement', 'DOMElement2');
$doc->formatOutput = true;
$doc->preserveWhiteSpace = false;
$doc->strictErrorChecking = false;
return $doc;
}

static function loadXMLFile ($file) {
$doc = DOM::newDocument();
$doc->load($file, DOM_LIBXML_OPTIONS);
return $doc;
}

static function loadXMLString ($data) {
$doc = DOM::newDocument();
if ($data) $doc->loadXML(trim($data), DOM_LIBXML_OPTIONS);
return $doc;
}

static function loadHTMLString ($data) {
$doc = DOM::newDocument();
$doc->loadHTML($data, DOM_LIBXML_OPTIONS);
return $doc;
}

static function decodeEntities ($str) {
global $entities;
if (!@$entities) $entities = unserialize(file_get_contents('./core/entities.dat'));
return str_replace(array_keys($entities), array_values($entities), $str);
}

static function nodeListToArray ($nl) {
$ar = array();
foreach($nl as $x) $ar[]=$x;
return $ar;
}

static function HTMLToXML ($html) {
$html = DOM::decodeEntities(trim($html));
$html = preg_replace_callback( '#<((?:img|br)\b.*?)>#ms', function($m){ 
if (substr($m[1], -1)!='/') $m[1].=' /';
return "<$m[1]>";
}, $html);
return $html;
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
function appendElement ($tagName, $attrs = null) {
$el = $this->createElement($tagName);
if ($attrs) $el->setAttributes($attrs);
$this->appendChild($el);
return $el;
}

function createXMLFragment ($xml) {
$frag = $this->createDocumentFragment();
$frag->appendXML($xml);
return $frag;
}

function createHTMLFragment ($html) {
return $this->createXMLFragment(DOM::HTMLToXML($html));
}

function getRootElement () {
if ($this->documentElement) return $this->documentElement;
else trigger_error("Root not of the XML document couldn't be determined; the document may be malformed or empty.", E_USER_WARNING);
}

function __call ($name, $args) {
$root = $this->getRootElement();
if (!$root) return null;
return call_user_func_array(array($root, $name), $args);
}
}

class DOMElement2 extends DOMElement {

function __toString () { return $this->nodeValue; }
function isEmpty () { return !$this->hasChildNodes(); }

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
foreach($attrs as $name=>$value) {
$prefixpos = strpos($name, ':');
if ($prefixpos===false) $this->setAttribute($name, $value);
else {
$ns = 'http://0.0.0.0/unknown-ns';
switch(substr($name, 0, $prefixpos)) {
case 'epub': $ns = NS_EPUB; break;
case 'dc': $ns = NS_DC; break;
}
$this->setAttributeNs($ns, $name, $value);
}}
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

function renameElement ($newTagName, $attrs=null) {
$el = $this->parentNode->insertElementBefore($newTagName, $this, $attrs);
while($this->firstChild) $el->appendChild($this->firstChild);
$this->parentNode->removeChild($this);
return $el;
}

function appendText ($text) {
$tn = $this->ownerDocument->createTextNode($text);
$this->appendChild($tn);
return $this;
}

function appendXML ($xml) {
$frag = $this->ownerDocument->createDocumentFragment();
$frag->appendXML($xml);
$this->appendChild($frag);
return $this;
}

function appendHTML ($html) {
return $this->appendXML(DOM::HTMLToXML($html));
}

function removeAllChilds ($tagName = null) {
for ($i=$this->childNodes->length -1; $i>=0; $i--) {
$child = $this->childNodes->item($i);
if (!$tagName || $tagName==$child->nodeName) $this->removeChild($child);
}}

function extractNodeContents ($tagName = null) {
$frag = $this->ownerDocument->createDocumentFragment();
for ($i=$this->childNodes->length -1; $i>=0; $i--) {
$child = $this->childNodes->item($i);
if (!$tagName || $tagName==$child->nodeName) {
$this->removeChild($child);
$frag->insertBefore($child, $frag->firstChild);
}}
return $frag;
}

function removePreservingChildren () {
$parent = $this->parentNode;
for ($i=$this->childNodes->length -1; $i>=0; $i--) {
$child = $this->childNodes->item($i);
$parent->insertBefore($child, $this->nextSibling);
}
$parent->removeChild($this);
return $parent;
}

function subenclose ($tagName, $args=null) {
$frag = $this->extractNodeContents();
$this->appendElement($tagName, $args) ->appendChild($frag);
}

function surround ($tagName, $args=null) {
$el = $this->parentNode->insertElementBefore($tagName, $this, $args);
$el->appendChild($this);
}

function saveHTML () { return $this->ownerDocument->saveHTML($this); }
function saveXML () { return $this->ownerDocument->saveXML($this); }

function saveInnerHTML () {
$contents = $this->saveHTML();
$contents = substr($contents, 1+strpos($contents, '>')); // remove <body>
$contents = substr($contents, 0, strrpos($contents, '<')); // remove </body>
return $contents;
}

function saveInnerXML () {
$contents = $this->saveXML();
$contents = substr($contents, 1+strpos($contents, '>')); // remove <body>
$contents = substr($contents, 0, strrpos($contents, '<')); // remove </body>
return $contents;
}

}

?>