<?php
require_once('core/kernel.php');
checkLogged();
loadTranslation('bookshelf');

class BookController {

function index ($name) {
$this->view($name);
}

function export ($bookName, $format) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
if (!$b->canRead()) exit403();
@list($contentType, $fileName) = $b->export($format);
if (!$fileName || !$contentType) {
global $root;
loadTranslation('bookshelf');
$_SESSION['alertmsg'] = getTranslation('ExportFailed');
$_SESSION['failed'] = true;
header("Location:$root/bookshelf/index");
exit();
}
ob_end_clean();
header("Content-Type: $contentType");
header('Content-Disposition: attachment; filename="' .basename($fileName) .'"');
readfile($fileName);
exit();
}

function view ($bookName, $fileName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
if (!$b->canRead()) exit403();
if (!$fileName) {
$fileName = $b->getNavFileName();
if (!$fileName) $fileName =  $b->getFirstPageFileName();
header("Location:$fileName");
exit();
}
if ($b->isExtracted() && $b->getOption('tocNeedRegen', false)) $b->updateTOC();
$item = $b->getItemByFileName($fileName);
if (!$item) exit404();
$ct = $item->mediaType;
if ($ct == 'application/xhtml+xml') {
$html = $b->getContentsByFileName($fileName);
$bv = new BookView();
$bv-> processPage($b, $item, $html);
header("Content-Type: text/html; charset=utf-8");
echo $html;
}
else {
ob_end_clean();
header("Content-Type: $ct");
$b->directEchoFile($fileName);
}
exit();
}

function onepage ($bookName, $optName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
if (!$b->canRead()) exit403();
$doc = $b->generateSinglePageDocument($optName);
header("Content-Type: text/html; charset=utf-8");
echo $doc->saveXML();
exit();
}


}
?>