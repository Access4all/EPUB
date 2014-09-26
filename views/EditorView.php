<?php
class EditorView {

function index ($b) {
global $root, $lang, $pageTitle;
$pageTitle = $b->getTitle() .' &mdash; '. $b->getAuthors();
require('edIndex.php');
}


}
?>