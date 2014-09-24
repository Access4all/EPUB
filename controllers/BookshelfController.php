<?php
require_once('core/kernel.php');
loadTranslation('bookshelf');

class BookshelfController {

function index () {
$bs = new Bookshelf();
$books = $bs->getBookList();
$view = new BookshelfView();
$view->index($books);
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
$tmp = './data/upload'.time().'.tmp';
if (move_uploaded_file($_FILES['upload']['tmp_name'], $tmp)) {
if ($bs->importBookFromFile($tmp)) $failed=false;
@unlink($tmp);
}}
$_SESSION['failed'] = $failed;
$_SESSION['alertmsg'] = getTranslation($failed? 'UploadFailed' : 'UploadSuccess');
header("Location:$root/bookshelf/index#alert");
exit();
}


}
?>