<?php
require_once('core/kernel.php');
loadTranslation('editor');

class EditorController {

function index ($bookName, $pageName) {
$this->editorMain('fv', 'editor', $bookName, $pageName);
}

public function __call ($name, $args) {
@list($left, $right) = explode('_', $name);
@list($bn, $pn) = $args;
$this->editorMain($left, $right, $bn, $pn);
}

private function editorMain ($leftViewMethod, $rightViewMethod, $bookName, $pageName) {
if (!$leftViewMethod || !$rightViewMethod) exit404();
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
$p = $b->getItemByFileName($pageName);
if (isset($_POST['authors'], $_POST['title'])) {
$b->updateBookMetadata($_POST);
header("Location:{$_SERVER['REQEUST_URI']}");
exit();
}
$view = new EditorView();
if (!$p) $view->bookOptions($leftViewMethod, $rightViewMethod, $b);
else $view->editorMain($leftViewMethod, $rightViewMethod, $b, $p);
}

}
?>