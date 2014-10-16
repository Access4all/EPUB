<?php
require_once('core/kernel.php');

global $allowedTags, $allowedEmptyTags, $allowedAttrs, $allowedClasses;
$allowedTags = preg_split("/[ \r\n,;]+/", <<<END
html head body title meta
p h1 h2 h3 h4 h5 h6 pre blockquote div
a b i s abbr strong em img code kbd samp var br hr span
ul ol dl li dt dd
table thead tfoot tbody tr td th caption
audio video header footer section aside figure figcaption
END
);//

$allowedEmptyTags = array('br', 'hr', 'meta', 'img');
$allowedAttrs = array(
'#' => array('id', 'class', 'role', 'aria-label', 'aria-level', 'aria-describedby'),
'a' => array('href', 'rel', 'rev', 'hreflang', 'title', 'type'),
'abbr' => array('title'),
'img' => array('src', 'width', 'height', 'alt'),
'meta' => array('name', 'value', 'http-equiv', 'content', 'charset'),
'ol' => array('start', 'type'),
);//

$allowedClasses = array();

class HTMLCleaner {

private function cleanElement ($el) {
global $allowedTags, $allowedEmptyTags, $allowedAttrs, $allowedClasses;
if ($el->childNodes) for ($i=$el->childNodes->length -1; $i>=0; $i--) $this->cleanElement($el->childNodes->item($i));
if ($el->attributes) for ($i=$el->attributes->length -1; $i>=0; $i--) {
$attr = $el->attributes->item($i);
$remove=true;
$name = $attr->name;
$value = $attr->value;
if (in_array($name, $allowedAttrs['#'])) $remove = false;
if (isset($allowedAttrs[$el->nodeName]) && in_array($name, $allowedAttrs[$el->nodeName])) $remove=false;
if ($name=='class') {
$classNames = array();
foreach(explode(' ', $value) as $cn) {
if (in_array($cn, $allowedClasses)) $classNames[]=$cn;
}
if (count($classNames)<=0) $remove=true;
else $attr->value = implode(' ', $classNames);
}
if ($remove) $el->removeAttributeNode($attr);
}
$remove = false;
$rename = null;
switch($el->nodeType){
case 1:
if (!in_array($el->nodeName, $allowedTags)) $remove=true;
if (!$el->hasChildNodes() && !in_array($el->nodeName, $allowedEmptyTags)) $remove = true;
if ($el->nodeName=='p' && in_array($el->parentNode->nodeName, array('li', 'dt', 'dd', 'td', 'th', 'p'))) $remove=true;
if ($el->attributes->length<=0 && ($el->nodeName=='div' || $el->nodeName=='span')) $remove=true;
if ($el->nodeName=='b') $rename='strong';
else if ($el->nodeName=='i') $rename='em';
break;	
case 3:
if (($el->length<=0 || strlen(trim(str_replace(chr(160),' ', utf8_decode($el->data))))<=0)) $remove=true;
break;
default: $remove=true; break;
}
if ($remove) {
if ($el->hasChildNodes()) $el->removePreservingChildren();
else $el->parentNode->removeChild($el);
}
else if ($rename) {
$newEl = $el->ownerDocument->createElement($rename);
$el->parentNode->insertBefore($newEl, $el);
$childs = $el->extractNodeContents();
$newEl->appendChild($childs);
$el->parentNode->removeChild($el);
}}

function cleanDocument ($doc) {
$this->cleanElement($doc->documentElement);
}

}
?>