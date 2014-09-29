<?php
class DOM {

static function newDocument () {
$doc = new DOMDocument2();
$doc->registerNodeClass('DOMNode', 'DOMNode2');
$doc->registerNodeClass('DOMElement', 'DOMElement2');
return $doc;
}

static function loadXMLFile ($file) {
$doc = DOM::newDocument();
$doc->load($file);
return $doc;
}

static function loadXMLString ($data) {
$doc = DOM::newDocument();
$doc->loadXML($data);
return $doc;
}

static function loadHTMLString ($data) {
$doc = DOM::newDocument();
@$doc->loadHTML($data);
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
function getRootElement () { 
if (!@$this->root) {
foreach($this->childNodes as $node) {
if ($node->nodeType==1) {
$this->root = $node;
break;
}}
}
return $this->root;
}

function __call ($name, $args) {
return call_user_func_array(array($this->getRootElement(), $name), $args);
}
}

class DOMNode2 extends DOMNode {
public function __toString () { return $this->nodeValue; }
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

function saveHTML () { return $this->ownerDocument->saveHTML($this); }
function saveXML () { return $this->ownerDocument->saveXML($this); }
}

?>