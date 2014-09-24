<?php
require_once('core/kernel.php');

class Book{
var $fs;

function __construct ($a=null) {
if ($a) autofill($this,$a);
}

function getFileSystem () {
if (!$this->fs) {
global $booksdir;
$this->fs = null;
$name = $this->name;
$fn = "$booksdir/$name";
if (is_dir($fn)) $this->fs = new DirectoryFileSystem($fn);
$fn = "$booksdir/$name.epub";
if (is_file($fn)) $this->fs = new ZipFileSystem($fn);
}
return $this->fs;
}

function close () {
if (@$this->fs) $this->fs->close();
$this->fs=null;
}

function exists () {
return !!$this->getFileSystem();
}

function delete () {
global $booksdir;
$this->close();
$name = $this->name;
$fn = "$booksdir/$name";
if (is_dir($fn)) {
return Misc::rmdirRecursive($fn);
}
$fn = "$booksdir/$name.epub";
if (is_file($fn)) return @unlink($fn);
return false;
}

function extract () {
$fs = $this->getFileSystem();
if ($fs->isExtracted()) return;
global $booksdir;
$dn = "$booksdir/{$this->name}/";
$fs->extractTo($dn);
$this->close();
$this->getFileSystem();
}

function getOpfFileName () {
if (!isset($this->opfFileName)) {
$fs = $this->getFileSystem();
$container = new SimpleXMLElement($fs->getFromName('META-INF/container.xml'));
$this->opfFileName = ''.$container->rootfiles->rootfile->attributes()['full-path'];
}
return $this->opfFileName;
}

function getOpf () {
if (!isset($this->opf)) $this->opf = DOM::loadXMLString($this->getFileSystem()->getFromName($this->getOpfFileName()));
return $this->opf;
}

function getOpfRelativeFileName ($fn) {
$dir = dirname($this->getOpfFileName());
$re = "$dir/$fn";
if (substr($re,0,2)=='./') $re = substr($re,2);
return $re;
}

function readMetaData () {
$dcns = 'http://purl.org/dc/elements/1.1/';
$opf = $this->getOpf();
$metas = DOM::firstChild($opf, 'metadata');
$a = array();
foreach($metas->getElementsByTagNameNs($dcns, 'creator') as $x) $a[]=$x->nodeValue;
$this->authors = implode(', ', $a);
$this->title = DOM::nodeValue(DOM::firstChildNs($metas, $dcns, 'title') );
$this->identifier = DOM::nodeValue(DOM::firstChildNs($metas, $dcns, 'identifier') );
}

function getTitle () {
if (!@$this->title) $this->readMetaData();
return $this->title;
}

function getAuthors () {
if (!@$this->authors) $this->readMetaData();
return $this->authors;
}

function getNavFileName () {
if (!@$this->navFileName) {
$opf = $this->getOpf();
$manifest = DOM::firstChild($opf, 'manifest');
if (!$manifest) return null;
foreach($manifest->getElementsByTagName('item') as $item) {
$prop = $item->getAttribute('properties');
if ($prop && DOM::attrContains($prop, 'nav')) {
$this->navFileName = $this->getOpfRelativeFileName( $item->getAttribute('href') );
}}
}
return @$this->navFileName;
}

function getNavItem () {
return $this->getItemByFileName($this->getNavFileName());
}

function getNcxFileName () {
$spine = DOM::firstChild($opf, 'spine');
if (!$spine) return null;
$ncxId = $spine->getAttribute('toc');
if (!$ncxId) return null;
$ncxItem = $this->getItemById($ncxId);
if (!$ncxItem) return null;
$ncxFileName = $this->getOpfRelativeFileName( $ncxItem->getAttribute('href') );
return $ncxFileName;
}

function getFirstPageFileName () {
$spine = $this->getSpine();
if (count($spine)<=0) return null;
$spine0 = $spine[0];
$re = $this->getOpfRelativeFileName( $spine0 ->getAttribute('href') );
return $re;
}

function getStreamByFileName ($fileName) {
return $this->getFileSystem() ->getStream($fileName);
}

function getContentsByFileName ($fileName) {
return $this->getFileSystem() ->getFromName($fileName);
}

function getItemById ($id) {
if (!@$this->itemIdMap) {
$map = array();
$manifest = DOM::firstChild($this->getOpf(), 'manifest');
if (!$manifest) return null;
foreach($manifest->getElementsByTagName('item') as $item) {
$theId = $item->getAttribute('id');
$map[$theId]=$item;
}
$this->itemIdMap = $map;
}
if (!isset($this->itemIdMap[$id])) return null;
return $this->itemIdMap[$id];
}

function getItemByFileName ($fileName) {
if (!@$this->itemFileNameMap) {
$map = array();
$manifest = DOM::firstChild($this->getOpf(), 'manifest');
if (!$manifest) return null;
foreach($manifest->getElementsByTagName('item') as $item) {
$fn = $this->getOpfRelativeFileName( $item->getAttribute('href') );
$map[$fn]=$item;
}
$this->itemFileNameMap = $map;
}
if (!isset($this->itemFileNameMap[$fileName])) return null;
return $this->itemFileNameMap[$fileName];
}

function getFileNameFromItem ($item) {
return $this->getOpfRelativeFileName( $item->getAttribute('href') );
}

function getSpine () {
if (!@$this->spine) {
$a = array();
$spine = DOM::firstChild($this->getOpf(), 'spine');
if (!$spine) return null;
foreach($spine->getElementsByTagName('itemref') as $itemref) {
$id = $itemref->getAttribute('idref');
if (!$id) continue;
$a[] = $this->getItemById($id);
}
$this->spine = $a;
}
return $this->spine;
}

}
?>