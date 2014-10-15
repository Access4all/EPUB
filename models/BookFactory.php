<?php

function changeFileExtension (&$info) {
return ($info['fileName'] = preg_replace('/\.\w+$/', '.xhtml', $info['fileName']));
}

class BookFactory {

static $CLASSMAPPING = array(
'application/epub+zip' => 'EPUBBookFactory',
'application/xhtml+xml' => 'XHTMLBookFactory',
'text/html' => 'HTMLBookFactory',
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
//todo
}}
return null; // Not supported
}

}

class XHTMLBookFactory {
function createResourcesFromFile ($book, &$info, $file) {
return array(array(new BookPage($info), $file));
}}

class HTMLBookFactory {
function createResourcesFromFile ($book, &$info, $file) {
$doc = DOM::loadHTMLString( $file->getContents() );
changeFileExtension($info);
$file->release();
$info['contents'] = $doc->saveXML();
$info['mediaType'] = 'application/xhtml+xml';
return array(array(new BookPage($info), new MemoryFile($info)));
}}

class EPUBBookFactory {
function createBookFromFile ($bookshelf, &$info, $file) {
global $booksdir;
$fs = new ZipFileSystem($file->getRealFileName());
$b = new Book(array('fs'=>$fs));
$name = @$info['title'];
if (!$name) $name = $b->getTitle();
$name = Misc::toValidName($name);
$epubFile = "$booksdir/$name.epub";
@copy($file->getRealFileName(), $epubFile);
return new Book(array('name'=>$name));
}}

?>