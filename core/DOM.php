<?php
define('DOM_LIBXML_OPTIONS', LIBXML_COMPACT | LIBXML_NOBLANKS | LIBXML_NSCLEAN );//| LIBXML_NOERROR | LIBXML_NOWARNING);

class DOM {

static function newDocument () {
$doc = new DOMDocument2();
$doc->registerNodeClass('DOMElement', 'DOMElement2');
$doc->registerNodeClass('DOMDocumentFragment', 'DOMDocumentFragment2');
$doc->formatOutput = true;
$doc->preserveWhiteSpace = false;
$doc->strictErrorChecking = false;
$doc->validateOnParse = false;
$doc->substituteEntities = true;
$doc->resolveExternals = false;
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
if (PHP_VERSION_ID>=50400) $doc->loadHTML($data, DOM_LIBXML_OPTIONS);
else $doc->loadHTML($data);
return $doc;
}

static function decodeEntities ($str) {
global $entities;
if (!@$entities) $entities = unserialize(base64_decode(file_get_contents('./core/entities.dat')));
return str_replace(array_keys($entities), array_values($entities), $str);
}

static function nodeListToArray ($nl) {
$ar = array();
foreach($nl as $x) $ar[]=$x;
return $ar;
}

static function HTMLToXML ($html) {
// XML doesn't support HTML entities except amp/lt/gt/quot/apos; since we are in UTF-8 all the time, it never hurts to replace them by their effective character
$html = DOM::decodeEntities(trim($html));
// Make sure the following tags are in their self-closed form; e.g. <br> without any closing tag is allowed in HTML5, but not in XML
$html = preg_replace_callback( '#<((?:img|br|hr|wbr|source|track)\b.*?)>#ms', function($m){ 
if (substr($m[1], -1)!='/') $m[1].=' /';
return "<$m[1]>";
}, $html);
// Similarly, ensure that there is no superfluous closing tag for those which need to be always self-closed, i.e. remove </br>
$html = preg_replace('@</(?:img|br|hr|wbr|source|track)>@', '', $html);
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
function appendElement ($tagName, $attrs = null, $text=null) {
$el = $this->createElement($tagName);
if ($attrs) $el->setAttributes($attrs);
if ($text) $el->appendChild($el->ownerDocument->createTextNode($text));
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

function getElementById ($id) {
// Standard getElementById method is bogus, cf. comment in DOMElement2::getElementById
return $this->documentElement->getElementById($id);
}

function __call ($name, $args) {
$root = $this->getRootElement();
if (!$root) return null;
return call_user_func_array(array($root, $name), $args);
}
}

class DOMDocumentFragment2 extends DOMDocumentFragment {

function insertElementBefore ($tagName, $ref, $attrs=null, $text=null) {
$el = $this->ownerDocument->createElement($tagName);
if ($attrs) $el->setAttributes($attrs);
if ($text) $el->appendChild($el->ownerDocument->createTextNode($text));
$this->insertBefore($el, $ref);
return $el;
}

function __call ($name, $args) {
$re = null;
for ($i=0; $i<$this->childNodes->length; $i++) {
$child = $this->childNodes->item($i);
$nre = call_user_func_array(array($child, $name), $args);
if ($nre!==null) $re=$nre;
}
return $re;
}

}//DOMDocumentFragment2 class

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

function getElementById ($id) {
// The standard method getElmentById don't work, unless a call to setIdAttribute is called on each element having an id first, and this has to be done each time a lookup is needed; PHP's DOM implementation is really stupid.
// We need to go through the document anyway to call setIDAttribute; so find the ID directly wont be slower.
foreach($this->getElementsByTagName('*') as $e) {
if ($e->hasAttribute('id') && $e->getAttribute('id')==$id) return $e;
}
return null;
}

function getHeadingLevel () {
if (preg_match('/h(\d)$/i', $this->nodeName, $m)) return floor($m[1]);
else return 0;
}

function isHeading () {
return $this->getHeadingLevel()>0;
}

function isSectionMainHeading () {
$level = $this->getHeadingLevel();
if ($level<=0) return false;
if (!in_array($this->parentNode->nodeName, array('section', 'aside', 'main', 'header', 'footer', 'div'))) return false;
foreach($this->parentNode->getOutline() as $e) if ($e!==$this && $e->getHeadingLevel()<=$level) return false;
return true;
}

function isPageMainHeading () {
$level = $this->getHeadingLevel();
if ($level<=0) return false;
foreach($this->ownerDocument->documentElement->getOutline() as $e) if ($e!==$this && $e->getHeadingLevel()<=$level) return false;
return true;
}

function getOutline () {
return $this->getElements(function($e){ return $e->isHeading(); });
}

function getNextHeading () {
$level = $this->getHeadingLevel();
if ($level<=0) trigger_error("getNextHeading can be called only on heading elements");
$found = false;
foreach($this->ownerDocument->documentElement->getOutline() as $e) {
if ($e===$this) { $found=true; continue; }
if (!$found) continue;
$l = $e->getHeadingLevel();
if ($l>0 && $l<=$level) return $e;
}
return null;
}

function shiftHeadingLevels ($delta) {
if ($delta==0) return;
if (($level = $this->getHeadingLevel())>0) {
$level+=$delta;
if ($level<1 || $level>6) trigger_error("Heading level out of range: $level", E_USER_WARNING);
else $this->renameElement("h$level");
}
for ($i=$this->childNodes->length -1; $i>=0; $i--) {
$child = $this->childNodes->item($i);
if (!($child instanceof DOMElement2)) continue;
$child->shiftHeadingLevels($delta);
}
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

function appendElement ($tagName, $attrs = null, $text=null) {
$el = $this->ownerDocument->createElement($tagName);
if ($attrs) $el->setAttributes($attrs);
if ($text) $el->appendChild($el->ownerDocument->createTextNode($text));
$this->appendChild($el);
return $el;
}

function appendElementNs ($ns, $tagName, $attrs = null, $text=null) {
$el = $this->ownerDocument->createElementNs($ns, $tagName);
if ($attrs) $el->setAttributes($attrs);
if ($text) $el->appendChild($el->ownerDocument->createTextNode($text));
$this->appendChild($el);
return $el;
}

function insertElementBefore ($tagName, $ref, $attrs=null, $text=null) {
$el = $this->ownerDocument->createElement($tagName);
if ($attrs) $el->setAttributes($attrs);
if ($text) $el->appendChild($el->ownerDocument->createTextNode($text));
$this->insertBefore($el, $ref);
return $el;
}

function renameElement ($newTagName, $attrs=null, $text=null) {
$el = $this->parentNode->insertElementBefore($newTagName, $this, $attrs, $text);
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

function extractPartialNodeContents ($from, $to) {
if ($from==null&&$to==null) return $this->extractNodeContents();
if (($from!=null&&$from->parentNode!==$this) || ($to!=null&&$to->parentNode!==$this)) trigger_error("From and to parameters must have this as parentNode", E_USER_WARNING);
$frag = $this->ownerDocument->createDocumentFragment();
$beforeBegin = ($to!=null);
for ($i=$this->childNodes->length -1; $i>=0; $i--) {
$child = $this->childNodes->item($i);
if ($child===$to) { $beforeBegin=false; continue; }
if ($beforeBegin) continue;
$this->removeChild($child);
$frag->insertBefore($child, $frag->firstChild);
if ($child===$from) break;
}
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

/*function subenclose ($tagName, $args=null) {
$frag = $this->extractNodeContents();
$this->appendElement($tagName, $args) ->appendChild($frag);
}

function surround ($tagName, $args=null) {
$el = $this->parentNode->insertElementBefore($tagName, $this, $args);
$el->appendChild($this);
}*/

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