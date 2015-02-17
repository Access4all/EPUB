<?php
require_once('core/kernel.php');
checkLogged();
loadTranslation('bookshelf');

class BookshelfController {

function index ($showTemplates=false) {
$bs = new Bookshelf();
$books = $bs->getBookList($showTemplates);
$templateList = $bs->getBookTemplateList();
$bv = new BookshelfView();
$bv->index($books, $templateList, $showTemplates);
}

function templates () {
$this->index(true);
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
global $root, $booksdir;
$failed = true;
$bs = new Bookshelf();
if (isset($_FILES['upload'])) {
$tmp = $booksdir.'/uploads/'.basename($_FILES['upload']['name']);
if (move_uploaded_file($_FILES['upload']['tmp_name'], $tmp)) {
$book = $bs->createBookFromFile(new UploadedFile($tmp));
if ($book) {
$failed=false;
$bs->addBook($book);
}
@unlink($tmp);
}
else $_SESSION['alertmsg'] = getTranslation('uploadFailed3');
}
else $_SESSION['alertmsg'] = getTranslation('uploadFailed3');
$_SESSION['failed'] = $failed;
if (!isset($_SESSION['alertmsg'])) $_SESSION['alertmsg'] = getTranslation($failed? 'UploadFailed' : 'UploadSuccess');
header("Location:$root/bookshelf/index#alert");
exit();
}


function newBook () {
global $root;
if (empty($_POST['title']) || empty($_POST['template'])) exit404();
$title = trim($_POST['title']);
$templateName = trim($_POST['template']);
$failed = true;
$bs = new Bookshelf();
$tplFile = "./data/$templateName.epub";
if (file_exists($tplFile)) {
$book = $bs->createBookFromFile(new LocalFile($tplFile), array('title'=>$title));
if ($book &&is_object($book)) {
$bs->addBook($book);
$book->extract();
$book->updateBookSettings(array('title'=>$title));
$failed=false;
}
}
else $_SESSION['alertmsg'] = getTranslation('createNewFailed2');
if ($failed) {
$_SESSION['failed'] = $failed;
if (!isset($_SESSION['alertmsg'])) $_SESSION['alertmsg'] = getTranslation($failed? 'CreateNewFailed' : 'CreateNewSuccess');
header("Location:$root/bookshelf/index#alert");
}
else header("Location: $root/editor/{$book->name}/index/");
exit();
}

function duplicate ($bookName, $bookTitle) {
$_POST['template'] = $bookName;
$_POST['title'] = $bookTitle . ' (' .getTranslation('Duplicated') .')';
$this->newBook();
}

}
?>