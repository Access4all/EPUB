<?php

function changeFileExtension (&$info) {
return ($info['fileName'] = preg_replace('/\.\w+$/', '.xhtml', $info['fileName']));
}

class BookFactory {

static $CLASSMAPPING = array(
'application/epub+zip' => 'EPUBBookFactory',
'application/xhtml+xml' => 'XHTMLBookFactory',
'text/html' => 'HTMLBookFactory',
'application/zip' => 'ZipBookFactory',
'application/font-woff' => 'FontFactory',
'application/font-sfnt' => 'FontFactory',
'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'PandocFactory',
'text/markdown' => 'PandocFactory',
'text/tex' => 'PandocFactory',
);

function createResourcesFromFile ($book, &$info, $file) {
$mediaType = $file->getMediaType();
if (isset(BookFactory::$CLASSMAPPING[$mediaType])) {
$factoryClass = BookFactory::$CLASSMAPPING[$mediaType];
if (method_exists($factoryClass, 'createResourcesFromFile')) {
$factory = new $factoryClass();
return $factory->createResourcesFromFile($book, $info, $file);
}}
$info['mediaType'] = $mediaType;
return array(array(new BookResource($info), $file));
}

function createBookFromFile ($bookshelf, &$info, $file) {
$mediaType = $file->getMediaType();
if (isset(BookFactory::$CLASSMAPPING[$mediaType])) {
$factoryClass = BookFactory::$CLASSMAPPING[$mediaType];
if (method_exists($factoryClass, 'createBookFromFile')) {
$factory = new $factoryClass();
return $factory->createBookFromFile($bookshelf, $info, $file);
}
else if (method_exists($factoryClass, 'createResourcesFromFile')) {
$templateName = (isset($info['template'])? $info['template'] : 'template');
$tplFile = "./data/$templateName.epub";
$title = 'UntitledBook'.date('Y.m.d.H.i.s');
$book = $bookshelf->createBookFromFile(new LocalFile($tplFile), array('title'=>$title));
if (!$book) return null;
$book->extract();
$book->updateBookSettings(array('title'=>$title));
$book->addNewResource($info, $file);
return $book;
}}
return null; // Not supported
}

function createBookResourceFromManifestEntry ($b, $item) {
$itemid = $item->getAttribute('id');
$mediaType = $item->getAttribute('media-type');
$factoryClass = null;
$resource = null;
if (isset(BookFactory::$CLASSMAPPING[$mediaType])) $factoryClass = BookFactory::$CLASSMAPPING[$mediaType];
if ($factoryClass && method_exists($factoryClass, 'createBookResourceFromManifestEntry')) {
$factory = new $factoryClass();
$resource = $factory->createBookResourceFromManifestEntry($b, $item, $itemid, $mediaType);
}
if (!$resource) $resource = new BookResource(); // Default resource class
return $resource;
}

} // End BookFactory

class XHTMLBookFactory {
function createResourcesFromFile ($book, &$info, $file) {
$className = 'BookPage';
if (isset($info['className'])) $className = $info['className'];
return array(array(new $className($info), $file));
}
function createBookResourceFromManifestEntry  ($b, $item, $itemid, $mediaType) {
$className = $b->getOption("PageClass:$itemid", 'BookPage');
return new $className();
}}

class HTMLBookFactory {
function createResourcesFromFile ($book, &$info, $file) {
$doc = null;
$contents = $file->getContents();
if (substr($contents, 0, 3)=='ï'.'»'.'¿') $contents = substr($contents,3); // Get rid of UTF-8 BOM if present
// Check if there is an XML prolog at the beginning of the file, in which case it would be an XHTML document; this is needed in case the file is wrongly indicated as text/html and/or has .htm/.html extension
if (substr($contents, 0, 5) == '<?xml') $doc = DOM::loadXMLString($contents  ); 
else $doc = DOM::loadHTMLString($contents  );
changeFileExtension($info);
$file->release();
$cleaner = new HTMLCleaner();
$cleaner->cleanDocument($doc);
$info['contents'] = $doc->saveXML();
$info['mediaType'] = 'application/xhtml+xml';
return array(array(new BookPage($info), new MemoryFile($info)));
}}

class EPUBBookFactory {
function createBookFromFile ($bookshelf, &$info, $file) {
global $booksdir;
$fs = new ZipFileSystem($file->getRealFileName());
$b = new Book(array('fs'=>$fs));
$title = @$info['title'];
if (!$title) $title = $b->getTitle();
if (!$title) {
$title = $b->getItemByFileName($b->getFirstNonTOCPageFileName() ) ->getTitle();
if ($title=='Untitled document') $title=null;
}
if (!$title) $title = substr($file->getRealFileName(), 0, -4);
$name = Misc::toValidName($title);
$epubFile = "$booksdir/$name.epub";
$info['title'] = $title;
copy($file->getRealFileName(), $epubFile);
return new Book(array('name'=>$name, 'title'=>$title));
}}

class ZipBookFactory {
function createResourcesFromFile ($book, &$info, $zipFile) {
if (isset($info['inzip'])) return array();
$bf = new BookFactory();
$fs = new ZipFileSystem($zipFile->getRealFileName());
$files = array();
$resources = array();
for ($i=0; $i<$fs->numFiles; $i++) $files[]=$fs->getNameIndex($i);
natsort($files);
$files = array_reverse($files);
foreach($files as $fileName) {
$data = $fs->getFromName($fileName);
$file = new MemoryFile(array('fileName'=>$fileName, 'contents'=>$data));
$mt = $file->getGenericType();
$fullMt = $file->getMediaType();
if ($mt!='text' && $mt!='image' && $mt!='javascript' && $mt!='css' && $mt!='audio' && $mt!='video') continue;
$info2 = array('fileName'=>$fileName, 'mediaType'=>$fullMt, 'inzip'=>true, 'id'=>Misc::toValidName($fileName));
$res2 = $bf->createResourcesFromFile($book, $info2, $file);
if (is_array($res2)) $resources = array_merge($resources, $res2);
}
return $resources;
}}

?>