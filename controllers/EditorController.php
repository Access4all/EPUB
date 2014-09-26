<?php
require_once('core/kernel.php');
loadTranslation('editor');

class EditorController {

function index ($bookName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
(new EditorView()) ->index($b);
}


}
?>