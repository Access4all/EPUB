<?php
require_once('core/kernel.php');
define('NS_EPUB', 'http://www.idpf.org/2007/ops');
define('NS_DC', 'http://purl.org/dc/elements/1.1/');

function zipaddDir ($z, $archivePath, $dir) {
foreach(glob("$dir*") as $path) {
$file = basename($path);
if ($file=='mimetype') continue;
if(is_dir($path)) zipAddDir($z, "$archivePath$file/", "$path/");
else $z->addFile($path, "$archivePath$file");
}
}

class Book{

static function getWorkingBook ($bookName) {
if (isset($_SESSION['curBookName'], $_SESSION['curBook']) && $_SESSION['curBookName']==$bookName) $b = $_SESSION['curBook'];
else  $b = new Book(array('name'=>$bookName));
$_SESSION['curBook'] = $b;
$_SESSION['curBookName'] = $bookName;
return $b;
}

function __construct ($a=null) {
if ($a) autofill($this,$a);
}

function __wakeup () {
$this->fs = null;
}

function __sleep () {
if (@$this->fs) $this->fs->close();
$this->fs = null;
return array_keys(get_object_vars($this));
}

function getFileSystem () {
if (!@$this->fs) {
global $booksdir;
$this->fs = null;
$name = $this->name;
$fn = "$booksdir/$name";
if (is_dir($fn)) $this->fs = new DirectoryFileSystem($fn);
else {
$fn = "$booksdir/$name.epub";
if (is_file($fn)) $this->fs = new ZipFileSystem($fn);
}}
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

function export ($format) {
global $booksdir, $root;
if ($format!='epub3') return null;
$fs = $this->getFileSystem();
$fn = "$booksdir/{$this->name}.epub";
$dir = "$booksdir/{$this->name}/";
if ($fs->isExtracted() && is_dir($dir)) {
$z = new ZipArchive();
$z->open($fn, ZipArchive::CREATE | ZipArchive::CM_STORE);
$z->addFromString('mimetype', 'application/epub+zip');
zipAddDir($z, '', $dir);
$z->close();
}
if (file_exists($fn)) return array('application/epub+zip', $fn);
else return null;
}

function getOpfFileName () {
if (!isset($this->opfFileName)) {
$fs = $this->getFileSystem();
$container = new SimpleXMLElement($fs->getFromName('META-INF/container.xml'));
$this->opfFileName = ''.$container->rootfiles->rootfile->attributes()['full-path'];
}
return $this->opfFileName;
}

function getOpfRelativeFileName ($fn) {
$dir = dirname($this->getOpfFileName());
$re = "$dir/$fn";
if (substr($re,0,2)=='./') $re = substr($re,2);
return $re;
}

private function readOpf () {
$opf = DOM::loadXMLString($this->getFileSystem()->getFromName($this->getOpfFileName()));

// read book metadata
$metas = $opf->getFirstElementByTagName('metadata');
$a = array();
foreach($metas->getElementsByTagNameNs(NS_DC, 'creator') as $x) $a[]=$x->nodeValue;
$this->authors = implode(', ', $a);
$this->title = strval( $metas->getFirstElementByTagNameNs(NS_DC, 'title') );
$this->identifier = strval( $metas->getFirstElementByTagNameNs(NS_DC, 'identifier') );

// read manifest
$manifest = $opf->getFirstElementByTagName('manifest');
if (!$manifest) return null;
$idmap = array();
$fnmap = array();
foreach($manifest->getElementsByTagName('item') as $item) {
$o = new BookPage();
$o->book = $this;
$o->id = $item->getAttribute('id');
$o->href = $item->getAttribute('href');
$o->mediaType = $item->getAttribute('media-type');
$o->props = $item->getAttribute('properties');
$o->fileName = $this->getOpfRelativeFileName($o->href);
$o->props = ($o->props? explode(' ', $o->props)  :array() );
$idmap[$o->id] = $o;
$fnmap[$o->fileName] = $o;
if (in_array('nav', $o->props)) $this->navFileName = $o->fileName;
}
$this->itemIdMap = $idmap;
$this->itemFileNameMap = $fnmap;

// Read spine
$a = array();
$spine = $opf->getFirstElementByTagName('spine');
if (!$spine) return null;
foreach($spine->getElementsByTagName('itemref') as $itemref) {
$id = $itemref->getAttribute('idref');
if (!$id) continue;
$a[] = $id;
}
$this->spine = $a;
}

function getTitle () {
if (!@$this->title) $this->readOpf();
return $this->title;
}

function getAuthors () {
if (!@$this->authors) $this->readOpf();
return $this->authors;
}

function updateBookMetadata ($info) {
$opfFile = $this->getFileSystem()->getFromName($this->getOpfFileName());
$opf = DOM::loadXMLString($opfFile);
if (isset($info['title'])) {
$title = $opf->getFirstElementByTagNameNs(NS_DC, 'title');
$this->title = $title->nodeValue = $info['title'];
}
if (isset($info['authors'])) {
$authors = preg_split("/\r\n|\n|\r/", $info['authors']);
//todo
}
$this->getFileSystem()->addFromString($this->getOpfFileName(), $opf->saveXML() );
}

function getNavFileName () {
if (!@$this->navFileName) $this->readOpf();
return @$this->navFileName;
}

function getNavItem () {
return $this->getItemByFileName($this->getNavFileName());
}

function getNavRelativeFileName ($fn) {
$dir = dirname($this->getNavFileName());
$re = "$dir/$fn";
if (substr($re,0,2)=='./') $re = substr($re,2);
return $re;
}

function getFirstPageFileName () {
$spine = $this->getSpine();
if (count($spine)<=0) return null;
$spine0 = $this->getItemById($spine[0]);
return $spine0->fileName;
}

function directEchoFile ($fileName) {
return $this->getFileSystem() ->directEcho($fileName);
}

function getContentsByFileName ($fileName) {
return $this->getFileSystem() ->getFromName($fileName);
}

function getItemById ($id) {
if (!@$this->itemIdMap) $this->readOpf();
if (!isset($this->itemIdMap[$id])) return null;
return $this->itemIdMap[$id];
}

function getItemByFileName ($fileName) {
if (!@$this->itemFileNameMap) $this->readOpf();
if (!isset($this->itemFileNameMap[$fileName])) return null;
return $this->itemFileNameMap[$fileName];
}

function getSpine () {
if (!@$this->spine) $this->readOpf();
return $this->spine;
}

}
?>