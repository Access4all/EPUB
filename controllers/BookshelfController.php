<?php
require_once('core/kernel.php');
loadTranslation('bookshelf');

class BookshelfController {

function index () {
$bs = new Bookshelf();
$books = $bs->getBookList();
$bv = new BookshelfView();
$bv->index($books);
}

function changeLanguage () {
global $lang, $langs;
$newLang = @$_POST['lang'];
if (array_key_exists($newLang, $langs)) {
$lang = $newLang;
$_SESSION['language'] = $lang;
}
header("Location:{$_SERVER['HTTP_REFERER']}");
exit();
}

function delete ($id) {
global $root;
$failed = true;
$bs = new Bookshelf();
$b = Bookshelf::getBookById($id);
if ($b && $bs->deleteBook($b)) $failed=false;
$_SESSION['failed'] = $failed;
$_SESSION['alertmsg'] = getTranslation($failed? 'DeleteFailed' : 'DeleteSuccess');
header("Location:$root/bookshelf/index#alert");
exit();
}

function upload () {
global $root;
$failed = true;
$bs = new Bookshelf();
if (isset($_FILES['upload'])) {
$tmp = './data/uploads/'.basename($_FILES['upload']['name']);
if (move_uploaded_file($_FILES['upload']['tmp_name'], $tmp)) {
$book = $bs->createBookFromFile(new UploadedFile($tmp));
if ($book) {
$failed=false;
$bs->addBook($book);
}
@unlink($tmp);
}}
$_SESSION['failed'] = $failed;
$_SESSION['alertmsg'] = getTranslation($failed? 'UploadFailed' : 'UploadSuccess');
header("Location:$root/bookshelf/index#alert");
exit();
}


function newBook () {
global $root;
if (empty($_POST['title'])) exit404();
$title = trim($_POST['title']);
$failed = true;
$bs = new Bookshelf();
$tplFile = './data/template.epub';
$book = $bs->createBookFromFile(new LocalFile($tplFile), array('title'=>$title));
if ($book) {
$bs->addBook($book);
$book->extract();
$book->updateBookSettings(array('title'=>$title));
$failed=false;
}
if ($failed) {
$_SESSION['failed'] = $failed;
$_SESSION['alertmsg'] = getTranslation($failed? 'CreateNewFailed' : 'CreateNewSuccess');
header("Location:$root/bookshelf/index#alert");
}
else header("Location: $root/editor/{$book->name}/index/");
exit();
}

}
?>