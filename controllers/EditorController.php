<?php
require_once('core/kernel.php');
loadTranslation('editor');

class EditorController {

function index ($bookName, $pageName) {
$this->editorMain('sv', 'editor', $bookName, $pageName);
}

public function save ($bookName, $pageName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
$p = $b->getItemByFileName($pageName);
if (!$p) exit404();
if (empty($_POST['content'])) exit500();
$p->updateContents($_POST['content']);
die('saved');
}

public function saveTemplate ($bookName, $pageName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
if (empty($_POST['content'])) exit500();
$b->updateCssTemplate($_POST['content']);
die('saved');
}

public function contentCss ($bookName) {
global $root;
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
header('Content-type: text/css');
$b->directEchoFile('META-INF/template.css');
exit();
}

public function preview ($bookName, $pageName) {
global $root;
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
$p = $b->getItemByFileName($pageName);
if (!$p) exit404();
sleep(2); // Wait a bit so that AJAX save has time to finish
$b->updateTOC();
header("Location: $root/book/{$b->name}/view/{$p->fileName}");
exit();
}

public function moveSpineAfter ($bookName) {
$this->moveOperation($bookName, __FUNCTION__);
}

public function moveSpineBefore ($bookName) {
$this->moveOperation($bookName, __FUNCTION__);
}

public function moveTocAfter ($bookName) {
$this->moveOperation($bookName, __FUNCTION__);
}

public function moveTocBefore ($bookName) {
$this->moveOperation($bookName, __FUNCTION__);
}

public function moveTocUnder ($bookName) {
$this->moveOperation($bookName, __FUNCTION__);
}

public function moveFile ($bookName) {
$this->moveOperation($bookName, __FUNCTION__);
}

private function moveOperation ($bookName, $actionName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
if (empty($_GET['src']) || empty($_GET['ref'])) exit404();
$moveItem = $b->getItemByFileName( $_GET['src'] );
$refItem = $b->getItemByFileName( $_GET['ref'] );
if (!$moveItem || !$refItem) exit404();
$b->$actionName($moveItem, $refItem);
die('OK');
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
$file = null;
if (isset($_FILES['upload'])) {
$f = &$_FILES['upload'];
$ext = strrchr($f['name'], '.');
$name = Misc::toValidName(basename($f['name'], $ext));
$name = 'data/uploads/'.$name .$ext;
@move_uploaded_file($f['tmp_name'], $name);
$file = new UploadedFile($name);
}
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
if ($p && !preg_match('/\.(?:xhtml|htm|html|xml|opf|txt|css|js)$/i', $pageName)) {
$b->directEchoFile($p->fileName);
exit();
}
$view = new EditorView();
if (!$p && $rightViewMethod!='newpage' && $rightViewMethod!='addfiles') $view->bookOptions($leftViewMethod, $rightViewMethod, $b);
else $view->editorMain($leftViewMethod, $rightViewMethod, $b, $p);
}

}
?>