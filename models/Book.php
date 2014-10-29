<?php
require_once('BookFactory.php');
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
$this->options=null;
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
@unlink("$booksdir/{$this->name}.epub");
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
$tmp = $container->rootfiles->rootfile->attributes();
$this->opfFileName = ''.$tmp['full-path'];
}
return $this->opfFileName;
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
$this->language = strval( $metas->getFirstElementByTagNameNs(NS_DC, 'language') );

// read manifest
$manifest = $opf->getFirstElementByTagName('manifest');
if (!$manifest) return null;
$idmap = array();
$fnmap = array();
foreach($manifest->getElementsByTagName('item') as $item) {
$mediaType = $item->getAttribute('media-type');
$itemid = $item->getAttribute('id');
$className = $this->getOption("PageClass:$itemid", 'BookPage');
$o = null;
if ($mediaType!='application/xhtml+xml') $className = 'BookResource';
$o = new $className();
$o->book = $this;
$o->id = $itemid;
$o->href = $item->getAttribute('href');
$o->mediaType = $mediaType;
$o->props = $item->getAttribute('properties');
$o->fileName = pathResolve($this->getOpfFileName(), $o->href);
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
$item = $this->itemIdMap[$id];
if (!$item) continue;
$a[] = $id;
$item->linear = $itemref->getAttribute('linear')!='no';
}
$this->spine = $a;
}

function getBOFileName () {
return 'META-INF/editor-options.ini';
}

function getOption ($name, $def = null) {
if (!@$this->options) $this->readBO();
return isset($this->options[$name])? $this->options[$name] : $def;
}

function setOption ($name, $value) {
if (!@$this->options) $this->readBO();
$this->options[$name]=$value;
}

private function readBO () {
$this->options = array();
foreach(@preg_split('/\r\n|\n|\r/', $this->getFileSystem()->getFromName($this->getBOFileName())) as $line) {
if (preg_match('/^\s*([-a-zA-Z_0-9:]+)\s*=\s*(.*?)\s*$/', $line, $m)) $this->options[$m[1]]=$m[2];
}
foreach($this->options as $key=>$value) {
if ($value==='true') $this->options[$key] = true;
else if ($value==='false') $this->options[$key] = false;
}
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
if (isset($info['title'])) $this->title = trim($info['title']);
if (isset($info['identifier'])) $this->identifier = trim($info['identifier']);
if (isset($info['language'])) $this->language = trim($info['language']);
if (isset($info['authors'])) {
$this->authors = preg_split("/\r\n|\n|\r|\s*[,;]\s*/", $info['authors'], -1, PREG_SPLIT_NO_EMPTY);
}
foreach(array( 'tocNoGen' ) as $opt) $this->setOption($opt, isset($info[$opt]));
foreach( array( 'tocMaxDepth', 'tocHeadingText'  ) as $opt) if (isset($info[$opt]) && preg_match('/^[^\r\n\t\f\b]+$/', $info[$opt])) $this->setOption($opt, $info[$opt]);
$this->metadataModified = true;
$this->saveOpf();
$this->saveBO();
}

function saveOpf () {
$opfFile = $this->getOpfFileName();
$opf = DOM::loadXMLString($this->getFileSystem()->getFromName($opfFile));
if (@$this->metadataModified) {
$this->metadataModified = false;
$metadata = $opf->getFirstElementByTagName('metadata');
$metadata->getFirstElementByTagNameNs(NS_DC, 'title') ->nodeValue = $this->title;
$metadata->removeAllChilds('dc:creator');
if (@$this->authors) foreach($this->authors as $a) $metadata->appendElementNs(NS_DC, 'dc:creator') ->appendText(trim($a));
}
if (@$this->spineModified) {
$this->spineModified=false;
$spine = $opf->getFirstElementByTagName('spine');
$spine->removeAllChilds();
foreach($this->spine as $id) {
$item = $this->itemIdMap[$id];
$attrs = array('idref'=>$id);
if ($item&& @$item->linear===false) $attrs['linear']='no';
$spine->appendElement('itemref', $attrs);
}
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
foreach($opf->getElementsByTagName('meta') as $m) {
$prop = $m->getAttribute('property');
if ($prop && $prop=='dcterms:modified') {
$m->nodeValue = date('c');
break;
}}
$this->getFileSystem()->addFromString($this->getOpfFileName(), $opf->saveXML() );
}

function saveBO () {
if (!@$this->options) return;
$optar = array();
ksort($this->options);
foreach($this->options as $key=>$value) {
if ($value===true) $value='true';
else if ($value===false) $value='false';
$optar[] = "$key=$value";
}
$this->getFileSystem()->addFromString($this->getBOFileName(), implode("\r\n", $optar) );
}

function addNewEmptyPage (&$info, $pageFrom = null) {
if (empty($info['title'])) $info['title'] = 'untitled'.time();
if (empty($info['fileName'])) {
$path = ($pageFrom? $pageFrom->fileName : $this->getOpfFileName());
$fn = Misc::toValidName($info['title']).'.xhtml' ;
$info['fileName'] = pathResolve($path, $fn);
}
if (empty($info['id'])) $info['id'] = Misc::toValidName($info['title']);
if (empty($info['type'])) $info['type'] = 'document';
$info['mediaType'] = 'application/xhtml+xml';
$bpf = new BookPageFactory();
$info['contents'] = str_replace("\r\n", "\n", $bpf->createEmptyPage($this, $info) );
return $this->addNewResource($info, new MemoryFile($info), $pageFrom);
}

function addResourceOnce (&$info, $srcFile=null, $pageFrom=null) {
if (!empty($info['id']) && null!=$this->getItemById($info['id'])) return;
if (!$srcFile) $srcFile = new MemoryFile($info);
return $this->addNewResource($info, $srcFile, $pageFrom);
}

function addNewResource (&$info, $srcFile, $pageFrom = null) {
if (!$srcFile) return 'No file provided';
if (!empty($info['id']) && !preg_match('/^[-a-zA-Z_0-9]+$/', $info['id'])) return 'Invalid ID';
if (!empty($info['fileName']) && !preg_match('#^(?:[-a-zA-Z_0-9]+/)*[-a-zA-Z_0-9]+\.[a-zA-Z]{1,5}$#', $info['fileName'])) return 'Invalid file name';
if (empty($info['fileName'])) {
$srcBaseName = $srcFile->getFileName();
$ext = strrchr($srcBaseName, '.');
$srcBaseName = Misc::toValidName(basename($srcBaseName, $ext));
$info['fileName'] =  pathResolve( ($pageFrom? $pageFrom->fileName : $this->getOpfFileName()), ($srcBaseName.$ext));
}
if (empty($info['id'])) {
$srcBaseName = basename($srcFile->getFileName());
$noext = substr($srcBaseName, 0, strrpos($srcBaseName,'.'));
$info['id'] = Misc::toValidName($noext);
}
$bf = new BookFactory();
$resources = $bf->createResourcesFromFile($this, $info, $srcFile);
foreach($resources as $lst) {
list($res, $srcFile) = $lst;
$res->book = $this;
$mediaType = $srcFile->getMediaType();
$srcFile->copyTo($this->getFileSystem(), $info['fileName']);
$srcFile->release();
$this->getItemById(null);
$this->getItemByFileName(null);
$this->manifestModified = true;
$this->itemIdMap[$info['id']] = $res;
$this->itemFileNameMap[$info['fileName']] = $res;
if ($res instanceof BookPage) {
$className = get_class($res);
if ($className!='BookPage') $this->setOption("PageClass:{$res->id}", $className);
$res->initNewPage($this);
$this->getSpine();
$this->spineModified = true;
if ($pageFrom) array_splice($this->spine, 1+array_search($pageFrom->id, $this->spine), 0, array($info['id']));
else $this->spine[] = $info['id'];
}}
$this->saveBO();
$this->saveOpf();
return $resources[0][0];
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

function updateTOC () {
if ($this->getOption('tocNoGen', false)) return;
$maxDepth = $this->getOption('tocMaxDepth', 4);
$navItem = $this->getNavItem();
$navdoc = $navItem->getDoc();
$body = $navdoc->getFirstElementByTagName('body');
$body->removeAllChilds();
$nav = $body->appendElement('nav', array('role'=>'navigation', 'epub:type'=>'toc'));
$nav->appendElement('h2', array('role'=>'heading', 'aria-level'=>2))
->appendText($this->getOption('tocHeadingText', getTranslation('TableOfContents')));
$ol = $nav->appendElement('ol');
//$nav->appendElement('h2', array('role'=>'heading', 'aria-level'=>2))
//->appendText($this->getOption('tocLofText', getTranslation('ListOfFigures')));
//$lof = $nav->appendElement('ol');
$curLevel = -1;
foreach($this->getSpine() as $spineId) {
$item = $this->getItemById($spineId);
if ($item==$navItem) continue;
if ($item->linear===false) continue;
$modified = false;
$doc = $item->getDoc();
$url = pathRelativize($navItem->fileName, $item->fileName);
foreach($doc->getElements(function($e){ return !!preg_match('/^h\d$/i', $e->nodeName); }) as $heading) {
if ($heading->hasAttribute('data-notoc')) continue;
$level = 0+substr($heading->nodeName,1);
if ($level>$maxDepth) continue;
if (!$heading->hasAttribute('id')) { $heading->setAttribute('id', Misc::generateId($heading->nodeName) ); $modified=true; }
if (!$heading->hasAttribute('aria-level')) { $heading->setAttribute('role', 'heading'); $heading->setAttribute('aria-level', $level); $modified=true; }
$text = ($heading->hasAttribute('data-toclabel')? $heading->getAttribute('data-toclabel') : $heading->nodeValue);
$anchor = $heading->getAttribute('id');
if ($curLevel<0) $curLevel = $level;
while($level<$curLevel) {
$curLevel--;
$ol = $ol->parentNode->parentNode;
}
while ($level>$curLevel) {
$curLevel++;
if ($ol->lastChild) $ol = $ol->lastChild->appendElement('ol');
else $ol = $ol->appendElement('li')->appendElement('ol');
}
$ol->appendElement('li')->appendElement('a', array('href'=>"$url#$anchor"))->appendText($text);
}
if (@$lof) foreach($doc->getElementsByTagName('figure') as $figure) {
$caption = $figure->getFirstElementByTagName('figcaption');
if (!$caption) continue;
$text = $caption->textContent;
if (!$figure->hasAttribute('id')) { $figure->setAttribute('id', Misc::generateId('fig') ); $modified=true; }
$anchor = $figure->getAttribute('id');
$lof->appendElement('li')
->appendElement('a', array('href'=>"$url#$anchor")) ->appendText($text);
}
if ($modified) $item->saveCloseDoc();
else $item->closeDoc();
}
$navItem->saveCloseDoc();
}

function updateCssTemplate ($newContents, $dontFilter=false) {
$contents = null;
if ($dontFilter) $contents = $newContents;
else {
$newContents = trim(str_replace(
array( "\r", "\n", '{', '}', ';' ),
array( ' ', ' ', "{\r\n", "}\r\n\r\n", ";\r\n" ),
trim($newContents)));
$contents = $this->getFileSystem() ->getFromName('META-INF/template.css');
$contents = preg_replace('/^\s*\.editor.*?\{.*?\}/ms', '', $contents);
$contents = trim($contents ."\r\n\r\n" .$newContents);
}
$this->getFileSystem() ->addFromString('META-INF/template.css', $contents);
$contents = str_replace('.editor {', 'body {', $contents);
$contents = str_replace('.editor ', '', $contents);
$this->getFileSystem() ->addFromString('EPUB/css/epub3.css', $contents);
$item = $this->getItemByFileName('EPUB/css/epub3.css');
if (!$item) {
$p = new BookResource(array('id'=>'cssEpub3', 'mediaType'=>'text/css', 'fileName'=>'EPUB/css/epub3.css'));
$p->book = $this;
$this->getItemById(null);
$this->getItemByFileName(null);
$this->manifestModified = true;
$this->itemIdMap[$info['id']] = $p;
$this->itemFileNameMap[$info['fileName']] = $p;
$this->saveOpf();
}}

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

function moveSpineBefore ($it, $ref) {
$this->getSpine();
$insertPos = array_search($ref->id, $this->spine);
$removePos = array_search($it->id, $this->spine);
if ($removePos>$insertPos) $removePos++;
array_splice($this->spine, $insertPos, 0, $it->id);
array_splice($this->spine, $removePos, 1);
$this->spineModified=true;
$this->saveOpf();
}

function moveSpineAFter  ($it, $ref) {
$this->getSpine();
$insertPos = 1+array_search($ref->id, $this->spine);
$removePos = array_search($it->id, $this->spine);
if ($removePos>$insertPos) $removePos++;
array_splice($this->spine, $insertPos, 0, $it->id);
array_splice($this->spine, $removePos, 1);
$this->spineModified=true;
$this->saveOpf();
}

function moveFile ($it, $ref) {
$newFileName = dirname($ref->fileName) .'/' .basename($it->fileName);
$this->implMoveFile($it, $newFileName);
}

function renameFile ($it, $newName) {
$newFileName = dirname($it->fileName) .'/' .$newName;
$this->implMoveFile($it, $newFileName);
}

private function implMoveFile ($it, $newFileName) {
$this->getItemByFileName(null);
$this->getFileSystem() ->moveFile( $it->fileName, $newFileName);
$this->itemFileNameMap[$it->fileName] = null;
$this->itemFileNameMap[$newFileName] = $it;
$it->fileName = $newFileName;
$this->saveOpf();
//todo: update all files where the file is referenced
}


}
?>