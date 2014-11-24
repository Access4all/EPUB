<?php
require_once('core/kernel.php');
loadTranslation('editor');

class EditorController {

function index ($bookName, $pageName) {
$this->editorMain('sv', 'editor', $bookName, $pageName);
}

public function save ($bookName, $pageName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->ensureExtracted() || !$b->exists()) exit404();
$p = $b->getItemByFileName($pageName);
if (!$p) exit404();
if (empty($_POST['content'])) exit500();
$p->updateContents($_POST['content']);
$b->setOption('tocNeedRegen', true);
$b->saveBO();
die('saved');
}

public function saveTemplate ($bookName, $pageName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->ensureExtracted() || !$b->exists()) exit404();
if (empty($_POST['content'])) exit500();
$b->updateCssTemplate(array('main'=>$_POST['content']));
die('saved');
}

public function exportTemplate ($bookName) {
$this->getTemplate($bookName, null, true);
}

public function getTemplate ($bookName, $pageName=null, $forceDownload=false) {
global $root;
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
header('Content-type: text/css');
if ($forceDownload) header('Content-Disposition: attachment; filename="template.css"');
$b->directEchoFile('META-INF/template.css');
exit();
}

public function importTemplate ($bookName) {
global $root;
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->ensureExtracted() || !$b->exists()) exit404();
$file = null;
if (isset($_FILES['upload'])) {
$f = &$_FILES['upload'];
$ext = strrchr($f['name'], '.');
$name = Misc::toValidName(basename($f['name'], $ext));
$name = 'data/uploads/'.$name .$ext;
move_uploaded_file($f['tmp_name'], $name);
$file = new UploadedFile($name);
}
if ($file) die('File received: ' .$file->getFileName() .'. But importation not supported at the moment.');
else die('No file received');
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

public function deleteFile ($bookName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
if (empty($_GET['file'])) exit404();
$item = $b->getItemByFileName( $_GET['file'] );
if (!$item) exit404();
$b->deleteFile($item);
die('OK');
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

public function renameFile ($bookName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
if (empty($_GET['src']) || empty($_GET['ref'])) die('die empty params');
$moveItem = $b->getItemByFileName( $_GET['src'] );
$newName = $_GET['ref'];
if (!preg_match('@^(?:\.\./)*(?:[-a-zA-Z_0-9]+/)*[-a-z_0-9]+\.[a-zA-Z0-9]{1,5}$@', $newName)) die('error filename '.$newName);
if (!$moveItem || !$newName) die('error item not found, nn='.$newName);
if (!$b->renameFile($moveItem, $newName)) die('Rename failed');
die('OK');
}

private function moveOperation ($bookName, $actionName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->ensureExtracted() || !$b->exists()) exit404();
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
$p = $b->addNewEmptyPage($_POST, $p);
if (is_object($p)) {
header("Location: $root/editor/{$b->name}/{$leftViewMethod}_editor/{$p->fileName}");
exit();
}}
else if (isset($_POST['addfiles'], $_POST['fileName'], $_POST['id'])) {
if (!$b->ensureExtracted()) exit500();
$file = null;
if (isset($_FILES['upload'])) {
$f = &$_FILES['upload'];
$ext = strrchr($f['name'], '.');
$name = Misc::toValidName(basename($f['name'], $ext));
$name = 'data/uploads/'.$name .$ext;
move_uploaded_file($f['tmp_name'], $name);
$file = new UploadedFile($name);
}
$res = $b->addNewResource($_POST, $file, $p);
$retFn = '';
if ($res&&$p) $retFn = pathRelativize($p->fileName, $res->fileName);
else if ($res) $retFn = $res->fileName;
if (empty($_POST['noredir'])) header("Location:{$_SERVER['REQUEST_URI']}");
die("Uploaded: $retFn");
}
else if (isset($_POST['authors'], $_POST['title'])) {
if (!$b->ensureExtracted()) exit500();
$b->updateBookSettings($_POST);
if (isset($_POST['ajax'])) die('OK');
header("Location:{$_SERVER['REQUEST_URI']}");
exit();
}
else if (isset($_POST['id'], $_POST['fileName'])) {
if (!$b->ensureExtracted()) exit500();
$p->updatePageSettings($_POST);
if (isset($_POST['ajax'])) die('OK');
header("Location:{$_SERVER['REQUEST_URI']}");
}
if ($p && !preg_match('/\.(?:xhtml|htm|html|xml|opf|txt|css|js)$/i', $pageName)) {
$b->directEchoFile($p->fileName);
exit();
}
$view = new EditorView();
if (!$p && $rightViewMethod!='newpage' && $rightViewMethod!='addfiles' && $rightViewMethod!='bookoptions') {
$lf = $b->getLastOpenedFileName();
if ($lf==$pageName) $lf = $b->getFirstNonTOCPageFileName();
header("Location: $root/editor/{$b->name}/{$leftViewMethod}_editor/{$lf}");
exit();
}
$view->editorMain($leftViewMethod, $rightViewMethod, $b, $p);
if ($p) $b->setLastOpenedFileName($p->fileName);
}

}
?>