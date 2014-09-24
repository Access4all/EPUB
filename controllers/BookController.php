<?php
require_once('core/kernel.php');
loadTranslation('bookshelf');

class BookController {

function index ($name) {
$this->view($name);
}

function view ($bookName, $fileName) {
//if (isset($_SESSION['curBookName'], $_SESSION['curBook']) && $_SESSION['curBookName']==$bookName) $b = $_SESSION['curBook'];
//else 
$b = new Book(array('name'=>$bookName));
$_SESSION['curBook'] = $b;
$_SESSION['curBookName'] = $bookName;
if (!$b || !$bookName || !$b->exists()) exit404();
if (!$fileName) {
$fileName = $b->getNavFileName();
if (!$fileName) $fileName =  $b->getFirstPageFileName();
header("Location:$fileName");
exit();
}
$item = $b->getItemByFileName($fileName);
if (!$item) exit404();
$ct = $item->getAttribute('media-type');
if ($ct == 'application/xhtml+xml') {
$html = $b->getContentsByFileName($fileName);
(new BookView()) -> processPage($b, $item, $html);
header("Content-Type: text/html; charset=utf-8");
echo $html;
}
else {
@ob_end_clean();
header("Content-Type: $ct");
$stream = $b->getStreamByFileName($fileName);
while(!feof($stream)) echo fread($stream, 4096);
fclose($stream);
}
exit();
}

}
?>