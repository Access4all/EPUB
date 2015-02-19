<?php
require_once('BookFactory.php');
require_once('core/kernel.php');
define('NS_XHTML', 'http://www.w3.org/1999/xhtml');
define('NS_EPUB', 'http://www.idpf.org/2007/ops');
define('NS_DC', 'http://purl.org/dc/elements/1.1/');

// Book user flags
define('BF_READ', 1);
define('BF_WRITE', 2);
define('BF_ADMIN', 4);

// Book flags
define('BF_TEMPLATE', 1);

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
else  {
$bs = Bookshelf::getInstance();
$b = $bs->getBookByName($bookName);
}
if (isset($b->opfLastChange) && $b->isExtracted()) $b->checkOpfCache();
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

function canRead () { return $this->eflags&BF_READ; }
function canWrite () { return $this->eflags&BF_WRITE; }
function canAdministrate () { return $this->eflags&BF_ADMIN; }

function getParticipatingUsers () {
$a = array();
if (isset($this->pusers, $this->pusersflags)) {
$ux = explode(',', $this->pusers);
$uf = explode(',', $this->pusersflags);
for ($i=0; $i<count($ux); $i++) {
$o = new stdClass();
$o->displayName = $ux[$i];
$o->eflags = floor($uf[$i]);
$a[] = $o;
}}
return $a;
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

function ensureExtracted () {
return $this->isExtracted() || $this->extract();
}

function isExtracted () {
return $this->getFileSystem() ->isExtracted();
}

function extract () {
$fs = $this->getFileSystem();
if ($fs->isExtracted()) return true;
global $booksdir;
$dn = "$booksdir/{$this->name}/";
$fs->extractTo($dn);
$this->close();
$this->getFileSystem();
@unlink("$booksdir/{$this->name}.epub");
return true;
}

function export ($format) {
global $booksdir, $root;
if ($this->getOption('tocNeedRegen', false)) $this->updateTOC();
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
$container = DOM::loadXMLString($fs->getFromName('META-INF/container.xml') );
$rootfile = $container->getFirstElementByTagName('rootfile');
$this->opfFileName = urldecode($rootfile->getAttribute('full-path'));
}
return $this->opfFileName;
}

private function checkOpfCache () {
if (isset($this->opfLastChange) && $this->opfLastChange < $this->getFileSystem() ->getLastModified( $this->getOpfFileName()) ) {
$this->readOpf();
}
if (isset($this->boLastChange) && $this->boLastChange < $this->getFileSystem() ->getLastModified($this->getBOFileName()) ) {
$this->readBO();
}}

private function readOpf () {
$this->opfLastChange = time();
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
$bf = new BookFactory();
foreach($manifest->getElementsByTagName('item') as $item) {
$mediaType = $item->getAttribute('media-type');
$itemid = $item->getAttribute('id');
$o = $bf->createBookResourceFromManifestEntry($this, $item);
$o->book = $this;
$o->id = $itemid;
$o->href = urldecode($item->getAttribute('href'));
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
$item = @$this->itemIdMap[$id];
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

function removeOption ($name) {
if (!@$this->options) $this->readBO();
if (!isset($this->options[$name])) return;
$this->options[$name]=null;
unset($this->options[$name]);
}

private function readBO () {
$this->boLastChange = time();
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
$needBsUpdate=false;
if (isset($info['title'])) { $this->title = trim($info['title']); $needBsUpdate=true; }
if (isset($info['identifier'])) $this->identifier = trim($info['identifier']);
if (isset($info['bookLanguage'])) $this->language = trim($info['bookLanguage']);
if (isset($info['authors'])) {
$needBsUpdate=true;
$this->authors = preg_split("/\r\n|\n|\r|\s*[,;]\s*/", $info['authors'], -1, PREG_SPLIT_NO_EMPTY);
}
if (isset($info['defaultDirByType'])) foreach($info['defaultDirByType'] as $type=>$value) {
if (substr($value,-1)=='/') $value = substr($value, 0, -1);
if (strlen($value)>1) $this->setOption("defaultDirByType:$type", $value);
else $this->removeOption("defaultDirByType:$type");
}
if (isset($info['cssMasterFile'])) {
if (preg_match('/\.css/i', $info['cssMasterFile'])) $this->setOption('cssMasterFile', $info['cssMasterFile']);
else $this->removeOption('cssMasterFile');
}
if (isset($info['share'], $info['shareNew']) && $this->canAdministrate() ) {
$bs = Bookshelf::getInstance();
$bs->updateBookRightsTable($this->id, $info);
}
$newBflags = $this->bflags;
if (isset($info['template'])) $newBflags |=BF_TEMPLATE;
else $newBflags&=~BF_TEMPLATE;
if ($newBflags!=$this->bflags) {
$this->bflags = $newBflags;
$needBsUpdate=true;
}
foreach(array( 'tocNoGen' ) as $opt) $this->setOption($opt, isset($info[$opt]));
foreach( array( 'tocMaxDepth', 'tocHeadingText'  ) as $opt) if (isset($info[$opt]) && preg_match('/^[^\r\n\t\f\b]+$/', $info[$opt])) $this->setOption($opt, $info[$opt]);
$this->setOption('tocNeedRegen', true);
$this->metadataModified = true;
if ($needBsUpdate) Bookshelf::getInstance() ->updateBook($this);
$this->saveOpf();
$this->saveBO();
}

function saveOpf () {
$this->opfLastChange = time();
$opfFile = $this->getOpfFileName();
$opf = DOM::loadXMLString($this->getFileSystem()->getFromName($opfFile));
if ($opf->documentElement->getAttribute('version')!='3.0') $opf->documentElement->setAttribute('version', '3.0');
if (@$this->metadataModified) {
$this->metadataModified = false;
$metadata = $opf->getFirstElementByTagName('metadata');
$metadata->getFirstElementByTagNameNs(NS_DC, 'title') ->nodeValue = $this->title;
$metadata->getFirstElementByTagNameNs(NS_DC, 'identifier') ->nodeValue = $this->identifier;
$metadata->getFirstElementByTagNameNs(NS_DC, 'language') ->nodeValue = $this->language;
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
$manifest->removeAllChilds();
foreach($this->itemIdMap as $p){
if (!$p) continue;
$href = pathRelativize($this->getOpfFileName() , $p->fileName);
$attrs = array('id'=>$p->id, 'media-type'=>$p->mediaType, 'href'=>$href);
if (@$p->props) $attrs['properties'] = implode(' ', $p->props);
$manifest->appendElement('item', $attrs);
}}
foreach($opf->getElementsByTagName('meta') as $m) {
$prop = $m->getAttribute('property');
if ($prop && $prop=='dcterms:modified') {
$m->nodeValue = date('Y-m-d\TH:i:s\Z');
break;
}}
$this->getFileSystem()->addFromString($this->getOpfFileName(), $opf->saveXML() );
}

function saveBO () {
if (!@$this->options) return;
$this->boLastChange = time();
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
$defdir = $this->getOption('defaultDirByType:text', '');
$path = ($pageFrom? $pageFrom->fileName : ($defdir? "$defdir/#" : $this->getOpfFileName() ));
$fn = $info['title'];
$fn = preg_replace('@^(.*?)(?:[-,;:]\s.+)?$@i', '$1', $fn); // When the title is long, i.e. 'chapter 1: something', we prefer generally have chapter1.xhtml rather than chapter1-something.xhtml.
$fn = Misc::toValidName($fn); // Remove unwanted characters and accents from the file name
$fn = preg_replace('@(?<=[a-z])[-_](?=\d)@', '', $fn); // We generally prefer chapter1.xhtml as chapter-1.xhtml
$fn.='.xhtml' ;
$info['fileName'] = pathResolve($path, $fn);
}
else if (!preg_match('/\.xhtml$/i', $info['fileName'])) {
$info['fileName'].='.xhtml';
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
$defdir = $this->getOption("defaultDirByType:{$srcFile->getGenericType()}", '');
$dstReference = ($defdir&&!isset($info['forcedir'])? "$defdir/#" : ($pageFrom? $pageFrom->fileName : $this->getOpfFileName() ));
$info['fileName'] =  pathResolve( $dstReference, ($srcBaseName.$ext));
}
if (empty($info['id'])) {
$srcBaseName = basename($srcFile->getFileName());
$noext = substr($srcBaseName, 0, strrpos($srcBaseName,'.'));
$info['id'] = Misc::toValidName($noext);
}
$bf = new BookFactory();
$resources = $bf->createResourcesFromFile($this, $info, $srcFile);
if (!$resources) return null;
foreach($resources as $lst) {
list($res, $dstFile) = $lst;
$res->book = $this;
$mediaType = $dstFile->getMediaType();
$fileName = ($srcFile==$dstFile? $info['fileName'] : $dstFile->getFileName() );
$itemId = ($srcFile!=$dstFile&&$res->id? $res->id : $info['id'] );
$dstFile->copyTo($this->getFileSystem(), $fileName);
$dstFile->release();
//echo "$fileName, $mediaType, ".get_class($res).", {$srcFile->getFileName()}, {$dstFile->getFileName()}<br />";
$this->getItemById(null);
$this->getItemByFileName(null);
$this->manifestModified = true;
$this->itemIdMap[$itemId] = $res;
$this->itemFileNameMap[$fileName] = $res;
if ($res instanceof BookPage) {
$className = get_class($res);
if ($className!='BookPage') $this->setOption("PageClass:{$res->id}", $className);
$res->initNewPage($this);
$this->getSpine();
$this->spineModified = true;
if ($pageFrom) array_splice($this->spine, 1+array_search($pageFrom->id, $this->spine), 0, array($itemId));
else $this->spine[] = $itemId;
}
else if ($res instanceof Font) $this->updateCssTemplate(array());
}
$srcFile->release();
$this->saveBO();
$this->saveOpf();
//die(nl2br(print_r($resources,true)));
//die('Done');
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

function getFirstNonTOCPageFileName () {
$spine = $this->getSpine();
if (count($spine)<=0) return null;
for ($i=0; $i<count($spine); $i++) {
$item = $this->getItemById($spine[$i]);
if (@is_array($item->props) && in_array('nav', $item->props)) continue;
return $item->fileName;
}
return null;
}

function setLastOpenedFileName ($lf) {
if (is_object($lf)) $lf = $lf->fileName;
@$this->getFileSystem()->addFromString('META-INF/last', $lf);
}

function getLastOpenedFileName () {
$lf = @$this->getFileSystem()->getFromName('META-INF/last');
if ($lf) return $lf;
else return $this->getFirstNonTOCPageFileName();
}

function updateTOC () {
loadTranslation('editor');
if ($this->getOption('tocNoGen', false)) return;
$this->ensureExtracted();
$maxDepth = $this->getOption('tocMaxDepth', 4);
$navItem = $this->getNavItem();
$navdoc = null;
if ($navItem) $navdoc = $navItem->getDoc();
if (!$navItem || !$navdoc->documentElement) {
$this->ensureExtracted();
$defdir = $this->getOption('defaultDirByType:text', null);
$fn = ($defdir? "$defdir/toc.xhtml" : 'toc.xhtml');
$pInfo = array('title'=>getTranslation('TableOfContents'), 'fileName'=>$fn);
$navItem = $this->addNewEmptyPage($pInfo);
$navItem->props = array('nav');
$this->navFileName = $navItem->fileName;
$this->manifestModified=true;
$this->saveOpf();
$navdoc = $navItem->getDoc();
}
$body = $navdoc->getFirstElementByTagName('body');
$body->removeAllChilds();
$nav = $body->appendElement('nav', array('role'=>'navigation', 'epub:type'=>'toc'));
$nav->appendElement('h1', array('role'=>'heading', 'aria-level'=>1, 'data-notoc'=>true))
->appendText($this->getOption('tocHeadingText', getTranslation('TableOfContents')));
$ol = $nav->appendElement('ol');
//$nav->appendElement('h1', array('role'=>'heading', 'aria-level'=>1, 'data-notoc'=>true))
//->appendText($this->getOption('tocLofText', getTranslation('ListOfFigures')));
//$lof = $nav->appendElement('ol');
$curLevel = -1;
foreach($this->getSpine() as $spineId) {
$item = $this->getItemById($spineId);
if ($item==$navItem) continue;
if (@$item->linear===false) continue;
$modified = false;
$doc = $item->getDoc();
$url = pathRelativize($navItem->fileName, $item->fileName);
foreach($doc->getOutline() as $heading) {
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
$newOl = $ol->parentNode->parentNode;
if ($newOl->nodeName=='ol') $ol = $newOl;
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
$this->setOption('tocNeedRegen', false);
$this->saveBO();
}

function updateCssTemplate ($info) {
$contents = $this->getFileSystem() ->getFromName('META-INF/template.css');
if (!isset($info['fonts'])) {
$fonts = '';
foreach($this->getCustomFonts() as $font) {
$family = $font->getFamily();
$weight = $font->isBold()? 'bold' : 'normal';
$style = $font->isItalic()? 'italic' : 'none';
$path = pathRelativize('getTemplate/template.css', $font->fileName);
$fonts .= <<<END
@font-face {
font-family: "$family";
font-id: {$font->id};
src: url($path);
font-weight: $weight;
font-style: $style;
}
END;
}
$info['fonts'] = $fonts;
}
foreach($info as $section=>$newContents) {
$newContents = preg_replace(
array( "/\r\n|\n|\r/", '/(?<![\':])\{/', '/\}(?![\'};])/', '@;@', '/ {2,}/', "/(?:\r\n|\n|\r){3,}/"),
array(' ', "{\r\n", "}\r\n\r\n", ";\r\n", ' ', "\r\n\r\n"),
$newContents);
$strfrom = "/*<$section>*/\r\n";
$strto = "\r\n/*</$section>*/";
$from = strpos($contents, $strfrom);
$to = strpos($contents, $strto);
if ($from<0 || $to<0 || $from===false || $to===false) $contents .= "\r\n\r\n/*<$section>*/\r\n\r\n$newContents\r\n\r\n/*</$section>*/\r\n\r\n";
else $contents = substr_replace($contents, $newContents, $from+strlen($strfrom), $to-($from+strlen($strfrom)) );
}
$contents = preg_replace("/(?:\r\n|\n|\r){3,}/", "\r\n\r\n", $contents);
$this->getFileSystem() ->addFromString('META-INF/template.css', $contents);
$this->updateCSS($contents);
}

function importCssTemplate ($file) {
$re = $file->copyTo($this->getFileSystem(), 'META-INF/template.css');
if ($re) $this->updateCssTemplate(array());
return $re;
}

function updateCSS ($contents = null) {
if (!$contents) $contents = $this->getFromName('META-INF/template.css');
$finalCssFile = $this->getOption('cssMasterFile', 'EPUB/css/epub3.css');
$_this = $this; // php 5.3 don't allow $this in closures
$contents = str_replace('.editor {', 'body {', $contents);
$contents = str_replace('.editor ', '', $contents);
$contents = preg_replace_callback('@font-id: ([-a-zA-Z0-9_]+).*?src:.*?;@s', function($m)use ($_this, $finalCssFile) {
$font = $_this->getItemById($m[1]);
$url = pathRelativize($finalCssFile, $font->fileName);
return "src: url($url);";
}, $contents);
$contents = preg_replace('@#StyleData.*?\}(?=\r|\n)@msi', '', $contents);
$contents = preg_replace( '@/\*</?\w+>\*/@ms', '', $contents);
$contents = preg_replace('@ {2,}@', ' ', $contents);
$contents = preg_replace('@(?:\r\n){3,}@', "\r\n\r\n", $contents);
$contents = trim($contents);
$this->getFileSystem() ->addFromString($finalCssFile, $contents);
$item = $this->getItemByFileName($finalCssFile);
if (!$item) {
$p = new BookResource(array('id'=>'cssEpub3', 'mediaType'=>'text/css', 'fileName'=>$finalCssFile));
$p->book = $this;
$this->getItemById(null);
$this->getItemByFileName(null);
$this->manifestModified = true;
$this->itemIdMap[$info['id']] = $p;
$this->itemFileNameMap[$info['fileName']] = $p;
$this->saveOpf();
}}

function generateSinglePageDocument ($options) {
if ($this->getOption('tocNeedRegen', false)) $this->updateTOC();
$options = explode(',',$options);
$gDoc = DOM::newDocument();
$gHtml = $gDoc->appendElement('html');
$gHead = $gHtml->appendElement('head');
$gBody = $gHtml->appendElement('body');
$gHead->appendElement('meta', array('charset'=>'utf-8'));
$gHead->appendElement('title', null, $this->getTitle());
foreach($this->getSpine() as $spineId) {
$item = $this->getItemById($spineId);
$doc = $item->getDoc();
$body = $doc->getFirstElementByTagName('body');
$div = $gBody->appendElement('div', array('data-page-file'=>$item->fileName, 'id'=>"___{$item->id}__top"));
foreach($body->childNodes as $child) {
$child = $gDoc->importNode($child);
$div->appendChild($child);
}
$item->closeDoc();
}
foreach($gBody->getElementsByTagName('*') as $el) {
if ($el->hasAttribute('id')) $el->setAttribute('id', '___'.$item->id.'_'.$el->getAttribute('id'));
if ($el->hasAttribute('for')) $el->setAttribute('for', '___'.$item->id.'_'.$el->getATtribute('for'));
if ($el->hasAttribute('href')) {
$href = explode('#', $el->getAttribute('href'), 2);
if (preg_match('/\.xhtml$/i', $href[0]) && !preg_match('/^https?:/', $href[0])) {
if (count($href)<=1) $href[1] = '___'.$item->id.'__top';
else $href[1] = '___'.$item->id.'_'.$href[1];
$el->setAttribute('href', '#'.$href[1]);
}}//IF has attribute href
}//Each element
return $gDoc;
}

function getCustomFonts () {
$fonts = array();
$this->getItemById(null);
foreach($this->itemIdMap as $item) {
if ($item instanceof Font) $fonts[]=$item;
//echo get_class($item), ", {$item->mediaType}, {$item->fileName}<br />";
}
return $fonts;
}

function directEchoFile ($fileName) {
return $this->getFileSystem() ->directEcho($fileName);
}

function getContentsByFileName ($fileName) {
if (!$fileName) return null;
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

function moveSpineBefore ($it, $ref, $_=null, $__=null) {
$this->getSpine();
$insertPos = array_search($ref->id, $this->spine);
$removePos = array_search($it->id, $this->spine);
if ($removePos>$insertPos) $removePos++;
array_splice($this->spine, $insertPos, 0, $it->id);
array_splice($this->spine, $removePos, 1);
$this->spineModified=true;
$this->saveOpf();
return true;
}

function moveSpineAFter  ($it, $ref, $_=null, $__=null) {
$this->getSpine();
$insertPos = 1+array_search($ref->id, $this->spine);
$removePos = array_search($it->id, $this->spine);
if ($removePos>$insertPos) $removePos++;
array_splice($this->spine, $insertPos, 0, $it->id);
array_splice($this->spine, $removePos, 1);
$this->spineModified=true;
$this->saveOpf();
return true;
}

function moveTocAfter ($src, $ref, $srcHash, $refHash) {
$srcDoc = $src->getDoc();
$refDoc = $ref->getDoc();
$srcEl = $srcDoc->getElementById($srcHash);
$refEl = $refDoc->getElementById($refHash);
if ($srcEl->isPageMainHeading() && $refEl->isPageMainHeading() ) {
// The user is trying to move two page main headings; in that case it's easier to simply move the files in the spine, the result is identical
$re = $this->moveSpineAfter($src, $ref);
$this->updateTOC();
return $re;
}
else if (get_class($src)!='BookPage' || get_class($ref)!='BookPage') {
// If the pages being moved aren't exactly BookPage (i.e. activities), then the move isn't supported
// This is because complicated casts are required in order to do it really right
return false;
}
$ldiff = $refEl->getHeadingLevel() - $srcEl->getHeadingLevel();
if ($srcEl->isSectionMainHeading()) {
$srcEl = $srcEl->parentNode;
$srcNext = $srcEl->nextSibling;
}
else {
$srcNext = $srcEl->getNextHeading();
if ($srcNext&&$srcNext->isSectionMainHeading()) $srcNext=null;
}
$refNext = $refEl->getNextHeading();
if ($refNext&&$refNext->isSectionMainHeading()) $refNext = $refNext->parentNode;
$frag = $srcEl->parentNode->extractPartialNodeContents($srcEl, $srcNext);
$frag->shiftHeadingLevels( $ldiff );
if ($srcDoc!==$refDoc) $frag = $refDoc->importNode($frag,true);
if ($refNext) $refNext->parentNode->insertBefore($frag, $refNext);
else $refEl->parentNode->appendChild($frag);
$ref->saveCloseDoc();
if ($srcDoc!==$refDoc) $src->saveCloseDoc();
$this->updateTOC();
return true;
}

function moveTocBefore ($src, $ref, $srcHash, $refHash) {
$srcDoc = $src->getDoc();
$refDoc = $ref->getDoc();
$srcEl = $srcDoc->getElementById($srcHash);
$refEl = $refDoc->getElementById($refHash);
if ($srcEl->isPageMainHeading() && $refEl->isPageMainHeading() ) {
// The user is trying to move two page main headings; in that case it's easier to simply move the files in the spine, the result is identical
$re = $this->moveSpineBefore($src, $ref);
$this->updateTOC();
return $re;
}
else if (get_class($src)!='BookPage' || get_class($ref)!='BookPage') {
// If the pages being moved aren't exactly BookPage (i.e. activities), then the move isn't supported
// This is because complicated casts are required in order to do it really right
return false;
}
$ldiff = $refEl->getHeadingLevel() - $srcEl->getHeadingLevel();
if ($srcEl->isSectionMainHeading()) {
$srcEl = $srcEl->parentNode;
$srcNext = $srcEl->nextSibling;
}
else {
$srcNext = $srcEl->getNextHeading();
if ($srcNext&&$srcNext->isSectionMainHeading()) $srcNext=null;
}
if ($refEl->isSectionMainHeading()) $refEl = $refEl->parentNode;
$frag = $srcEl->parentNode->extractPartialNodeContents($srcEl, $srcNext);
$frag->shiftHeadingLevels( $ldiff );
if ($srcDoc!==$refDoc) $frag = $refDoc->importNode($frag, true);
$refEl->parentNode->insertBefore($frag, $refEl);
$ref->saveCloseDoc();
if ($srcDoc!==$refDoc) $src->saveCloseDoc();
$this->updateTOC();
return true;
}

function moveTocUnder ($src, $ref, $srcHash, $refHash) {
$srcDoc = $src->getDoc();
$refDoc = $ref->getDoc();
$srcEl = $srcDoc->getElementById($srcHash);
$refEl = $refDoc->getElementById($refHash);
if ($srcEl->isPageMainHeading() && $refEl->isPageMainHeading() ) {
// The user is trying to move two page main headings; in that case it's easier to simply move the files in the spine, the result is identical
$re = $this->moveSpineAfter($src, $ref);
$this->updateTOC();
return $re;
}
else if (get_class($src)!='BookPage' || get_class($ref)!='BookPage') {
// If the pages being moved aren't exactly BookPage (i.e. activities), then the move isn't supported
// This is because complicated casts are required in order to do it really right
return false;
}
$ldiff = 1 + $refEl->getHeadingLevel() - $srcEl->getHeadingLevel();
if ($srcEl->isSectionMainHeading()) {
$srcEl = $srcEl->parentNode;
$srcNext = $srcEl->nextSibling;
}
else {
$srcNext = $srcEl->getNextHeading();
if ($srcNext&&$srcNext->isSectionMainHeading()) $srcNext=null;
}
$refNext = $refEl->getNextHeading();
if ($refNext&&$refNext->isSectionMainHeading()) $refNext = $refNext->parentNode;
$frag = $srcEl->parentNode->extractPartialNodeContents($srcEl, $srcNext);
$frag->shiftHeadingLevels( $ldiff );
if ($srcDoc!==$refDoc) $frag = $refDoc->importNode($frag,true);
if ($refNext) $refNext->parentNode->insertBefore($frag, $refNext);
else $refEl->parentNode->appendChild($frag);
$ref->saveCloseDoc();
if ($srcDoc!==$refDoc) $src->saveCloseDoc();
$this->updateTOC();
return true;
}

function moveFile ($it, $ref, $_=null, $__=null) {
$newFileName = dirname($ref->fileName) .'/' .basename($it->fileName);
return $this->implMoveFile($it, $newFileName);
}

function renameFile ($it, $newName) {
$newFileName = pathResolve($it->fileName, $newName);
if (strpos($newFileName, '../')!==false) return false;
return $this->implMoveFile($it, $newFileName);
}

public function deleteFile ($it) {
$this->getItemByFileName(null);
$this->getItemById(null);
$this->getSpine();
$this->itemFileNameMap[$it->fileName] = null;
$this->itemIdMap[$it->id] = null;
$this->getFileSystem() ->deleteName( $it->fileName);
$spineIndex = array_search($it->id, $this->spine);
if ($spineIndex>=0) {
array_splice($this->spine, $spineIndex, 1);
$this->setOption('tocNeedRegen', true);
$this->removeOption("PageClass:{$it->id}");
$this->saveBO();
}
$this->manifestModified = true;
$this->spineModified = true;
$this->saveOpf();
return true;
}

private function implMoveFile ($it, $newFileName) {
$this->getItemByFileName(null);
if (!$this->getFileSystem() ->moveFile( $it->fileName, $newFileName)) return false;
$this->getFileSystem()->removeDirectoryIfEmpty(dirname($it->fileName));
$this->itemFileNameMap[$it->fileName] = null;
$this->itemFileNameMap[$newFileName] = $it;
$this->manifestModified=true;
$it->fileName = $newFileName;
$this->saveOpf();
//todo: update all files where the file is referenced
return true;
}

function deleteTocItem ($item, $hash) {
$doc = $item->getDoc();
$el = $doc->getElementById($hash);
if ($el->isPageMainHeading()) {
// The user is trying to delete the main heading of a page; it's the same as delete the whole file then
$re = $this->deleteFile($item);
$this->updateTOC();
return $re;
}
else if (get_class($item)!='BookPage') {
// If the pages on which we are deleting isn't exactly BookPage (i.e. activities), then the move isn't supported
return false;
}
if ($el->isSectionMainHeading()) {
$el = $el->parentNode;
$next = $el->nextSibling;
}
else {
$next = $el->getNextHeading();
if ($next&&$next->isSectionMainHeading()) $next=null;
}
$frag = $el->parentNode->extractPartialNodeContents($el, $next);
$item->saveCloseDoc();
$this->updateTOC();
return true;
}


public function getRightsTable () {
$bs = Bookshelf::getInstance();
return $bs->getBookRightsTable($this->id);
}


}
?>