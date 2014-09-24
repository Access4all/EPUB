<?php
class DOM {

static function loadXMLFile ($file) {
$doc = new DOMDocument();
$doc->load($file);
return $doc;
}

static function loadXMLString ($data) {
$doc = new DOMDocument();
$doc->loadXML($data);
return $doc;
}

static function firstChildNs ($doc, $ns, $tag) {
if (!$doc) return null;
$nl = $doc->getElementsByTagNameNs($ns, $tag);
if (!$nl || $nl->length<=0) return null;
return $nl->item(0);
}

static function firstChild ($doc, $tag) {
if (!$doc) return null;
$nl = $doc->getElementsByTagName($tag);
if (!$nl || $nl->length<=0) return null;
return $nl->item(0);
}

static function nodeValue ($node) {
return $node? $node->nodeValue : '';
}


static function attrContains ($val, $needle) {
return preg_match("/\\b\\Q$needle\\E\\b/u", $val);
}


}
?>