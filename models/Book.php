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
$this->authors = $a;
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
return implode(', ', $this->authors);
}

function updateBookSettings ($info) {
if (isset($info['title'])) {
$this->title = trim($info['title']);
}
if (isset($info['authors'])) {
$this->authors = preg_split("/\r\n|\n|\r|\s*[,;]\s*/", $info['authors'], -1, PREG_SPLIT_NO_EMPTY);
}
$this->metadataModified = true;
$this->saveOpf();
}

function saveOpf () {
$opfFile = $this->getOpfFileName();
$opf = DOM::loadXMLString($this->getFileSystem()->getFromName($opfFile));
if (@$this->metadataModified) {
$this->metadataModified = false;
$metadata = $opf->getFirstElementByTagName('metadata');
$metadata->getFirstElementByTagNameNs(NS_DC, 'title') ->nodeValue = $this->title;
$metadata->removeAllChilds('dc:creator');
foreach($this->authors as $a) $metadata->appendElementNs(NS_DC, 'dc:creator') ->addText(trim($a));
}
if (@$this->spineModified) {
$this->spineModified=false;
$spine = $opf->getFirstElementByTagName('spine');
$spine->removeAllChilds();
foreach($this->spine as $id) $spine->appendElement('itemref', array('idref'=>$id));
}
if (@$this->manifestModified) {
$this->manifestModified=false;
$manifest = $opf->getFirstElementByTagName('manifest');
$dir = dirname($this->getOpfFileName());
$dirlen = 1+strlen($dir);
$manifest->removeAllChilds();
foreach($this->itemIdMap as $p){
$href = substr($p->fileName, $dirlen);
$attrs = array('id'=>$p->id, 'media-type'=>$p->mediaType, 'href'=>$href);
if (@$p->props) $attrs['properties'] = implode(' ', $p->props);
$manifest->appendElement('item', $attrs);
}}
$this->getFileSystem()->addFromString($this->getOpfFileName(), $opf->saveXML() );
}

function addNewPage (&$info, $pageFrom = null, $contents = null) {
$fn = $info['fileName'];
if (!empty($info['id']) && !preg_match('/^[-a-zA-Z_0-9]+$/', $info['id'])) return 'Invalid ID';
if (!empty($info['fileName']) && !preg_match('#^[-a-zA-Z_0-9]+(?:/[-a-zA-Z_0-9]+)\.[a-zA-Z]{1,5}$#', $info['fileName'])) return 'Invalid file name';
if (empty($info['title'])) $info['title'] = 'untitled'.time();
if (empty($info['fileName'])) {
$path = ($pageFrom? dirname($pageFrom->fileName) : dirname($this->getOpfFileName())) .'/';
$fn = Misc::toValidName($info['title']).'.xhtml' ;
$fn = $info['fileName'] = "$path$fn";
}
if (empty($info['id'])) $info['id'] = Misc::toValidName($info['title']);
if (!$contents) $contents = <<<END
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE HTML><html>
<head>
<title>{$info['title']}</title>
</head><body>
</body></html>
END;
$this->getSpine();
$this->getItemById(null);
$this->getItemByFileName(null);
$this->spineModified = true;
$this->manifestModified = true;
$p = new BookPage(array('fileName'=>$info['fileName'], 'id'=>$info['id'], 'mediaType'=>'application/xhtml+xml'));
$p->book = $this;
$this->itemIdMap[$info['id']] = $p;
$this->itemFileNameMap[$info['fileName']] = $p;
if ($pageFrom) array_splice($this->spine, 1+array_search($pageFrom->id, $this->spine), 0, array($info['id']));
else $this->spine[] = $info['id'];
$this->getFileSystem()->addFromString($fn, $contents);
$this->saveOpf();
return $p;
}

function addNewResource (&$info, $srcFile, $pageFrom = null) {
$fn = $info['fileName'];
$srcBaseName = basename($srcFile);
if (!empty($info['id']) && !preg_match('/^[-a-zA-Z_0-9]+$/', $info['id'])) return 'Invalid ID';
if (!empty($info['fileName']) && !preg_match('#^[-a-zA-Z_0-9]+(?:/[-a-zA-Z_0-9]+)\.[a-zA-Z]{1,5}$#', $info['fileName'])) return 'Invalid file name';
if (empty($info['fileName'])) {
$path = ($pageFrom? dirname($pageFrom->fileName) : dirname($this->getOpfFileName())) .'/';
$fn = $info['fileName'] = "$path$srcBaseName";
}
if (empty($info['id'])) {
$noext = substr($srcBaseName, 0, strrpos($srcBaseName,'.'));
$info['id'] = Misc::toValidName($noext);
}
//suite!
die(nl2br(print_r($info,true)));
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