<?php
require_once('core/kernel.php');
loadTranslation('editor');

class EditorController {

function index ($bookName, $pageName) {
$this->editorMain('sv', 'editor', $bookName, $pageName);
}

public function __call ($name, $args) {
@list($left, $right) = explode('_', $name);
@list($bn, $pn) = $args;
$this->editorMain($left, $right, $bn, $pn);
}

private function editorMain ($leftViewMethod, $rightViewMethod, $bookName, $pageName) {
global $root;
if (!$leftViewMethod || !$rightViewMethod) exit404();
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
$p = $b->getItemByFileName($pageName);
if (isset($_POST['newpage'], $_POST['fileName'], $_POST['id'], $_POST['title'], $_POST['type'])) {
$p = $b->addNewPage($_POST, $p);
if (is_object($p)) {
header("Location: $root/editor/{$b->name}/{$leftViewMethod}_editor/{$p->fileName}");
exit();
}}
else if (isset($_POST['addfiles'], $_POST['fileName'], $_POST['id'])) {
$file = 'data/uploads/dummy.xhtml';
$b->addNewResource($_POST, $file, $p);
header("Location:{$_SERVER['REQUEST_URI']}");
exit();
}
else if (isset($_POST['authors'], $_POST['title'])) {
$b->updateBookSettings($_POST);
header("Location:{$_SERVER['REQUEST_URI']}");
exit();
}
else if (isset($_POST['id'], $_POST['fileName'])) {
$p->updatePageSettings($_POST);
header("Location:{$_SERVER['REQUEST_URI']}");
}
$view = new EditorView();
if (!$p && $rightViewMethod!='newpage' && $rightViewMethod!='addfiles') $view->bookOptions($leftViewMethod, $rightViewMethod, $b);
else $view->editorMain($leftViewMethod, $rightViewMethod, $b, $p);
}

}
?>