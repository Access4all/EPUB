<?php
require_once('core/kernel.php');
checkLogged();
loadTranslation('editor');

class EditorController {

function index ($bookName, $pageName) {
$this->editorMain('tv', 'editor', $bookName, $pageName);
}

function infobox ($infoboxName, $retrn=false) {
global $lang;
if (!preg_match('/^[-a-zA-Z_0-9]+$/', $infoboxName)) exit404();
$text = @killUtf8bom(file_get_contents("lang/$lang/help-$infoboxName.txt"));
$_this = $this;
$text = preg_replace_callback('/%\{([-a-zA-Z_0-9]+)\}/', function($m) use($_this) { return $_this->infobox($m[1],true); }, $text);
if ($retrn) return $text;
else die($text);
}

public function save ($bookName, $pageName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->ensureExtracted() || !$b->exists()) exit404();
if (!$b->canWrite()) exit403();
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
if (!$b->canWrite()) exit403();
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
if (!$b->canRead()) exit403();
header('Content-type: text/css');
if ($forceDownload) header('Content-Disposition: attachment; filename="template.css"');
$b->directEchoFile('META-INF/template.css');
exit();
}

public function importTemplate ($bookName) {
global $root, $booksdir;
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->ensureExtracted() || !$b->exists()) exit404();
if (!$b->canWrite()) exit403();
$file = null;
if (isset($_FILES['upload'])) {
$f = &$_FILES['upload'];
$ext = strrchr($f['name'], '.');
$name = Misc::toValidName(basename($f['name'], $ext));
$name = $booksdir.'/uploads/'.$name .$ext;
move_uploaded_file($f['tmp_name'], $name);
$file = new UploadedFile($name);
}
if ($file && $b->importCssTemplate($file)) die('OK');
else if ($file) die('importation failed');
else die('No file received');
}

public function preview ($bookName, $pageName) {
global $root;
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
if (!$b->canRead()) exit403();
$p = $b->getItemByFileName($pageName);
if (!$p) exit404();
sleep(2); // Wait a bit so that AJAX save has time to finish
$b->updateTOC();
header("Location: $root/book/{$b->name}/view/{$p->fileName}");
exit();
}

public function onepage ($bookName, $optName) {
$bc = new BookController();
$bc->onepage($bookName, $optName);
}

public function deleteFile ($bookName) {
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
if (empty($_GET['file'])) exit404();
if (!$b->canWrite()) exit403();
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
if (!$b->canWrite()) exit403();
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
if (!$b->canWrite()) exit403();
if (empty($_GET['src']) || empty($_GET['ref'])) exit404();
$src = $_GET['src'];
$ref = $_GET['ref'];
$srcParam = null;
$refParam = null;
if (strpos($src,'#')) list($src, $srcParam) = explode('#', $src, 2);
if (strpos($ref,'#')) list($ref, $refParam) = explode('#', $ref, 2);
$moveItem = $b->getItemByFileName( $src );
$refItem = $b->getItemByFileName( $ref );
if (!$moveItem || !$refItem) exit404();
$b->$actionName($moveItem, $refItem, $srcParam, $refParam);
ie('OK');
}

public function __call ($name, $args) {
@list($left, $right) = explode('_', $name);
@list($bn, $pn) = $args;
$this->editorMain($left, $right, $bn, $pn);
}

private function editorMain ($leftViewMethod, $rightViewMethod, $bookName, $pageName) {
global $root, $booksdir;
if (!$leftViewMethod || !$rightViewMethod) exit404();
$b = Book::getWorkingBook($bookName);
if (!$b || !$bookName || !$b->exists()) exit404();
if (!$b->canRead()) exit403();
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
if (isset($_FILES['uploads'])) {
$nFiles = count($_FILES['uploads']['name']);
if ($nFiles>1) { unset($_POST['fileName']); unset($_POST['id']); }
for($uIdx=$nFiles -1; $uIdx>=0; $uIdx--) {
$f = &$_FILES['uploads'];
$ext = strrchr($f['name'][$uIdx], '.');
$name = Misc::toValidName(basename($f['name'][$uIdx], $ext));
$name = $booksdir.'/uploads/'.$name .$ext;
move_uploaded_file($f['tmp_name'][$uIdx], $name);
$file = new UploadedFile($name);
$info = array_merge($_POST, array()); // This makes a copy of the original $_POST array
$res = $b->addNewResource($info, $file, $p);
$retFn = '';
if ($res&&$p) $retFn = pathRelativize($p->fileName, $res->fileName);
else if ($res) $retFn = $res->fileName;
if (empty($_POST['noredir'])) header("Location:{$_SERVER['REQUEST_URI']}");
else echo("Uploaded: $retFn\r\n<br />");
}}
exit();
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
if (!$lf || $lf==$pageName) $lf = $b->getFirstNonTOCPageFileName();
if ($lf) header("Location: $root/editor/{$b->name}/{$leftViewMethod}_editor/{$lf}");
else header("Location: $root/editor/{$b->name}/{$leftViewMethod}_bookoptions/");
exit();
}
$view->editorMain($leftViewMethod, $rightViewMethod, $b, $p);
if ($p) $b->setLastOpenedFileName($p->fileName);
}

}
?>