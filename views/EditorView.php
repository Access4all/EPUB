<?php
loadTranslation('editor');

class EditorView {

function bookOptions ($leftView, $rightView, $b) {
global $root, $lang, $pageTitle;
$pageTitle = $b->getTitle() .' &mdash; '. $b->getAuthors();
$t = 'getTranslation';
require('edHeader.php');
$this->leftView($leftView, $rightView, $b, null);
require('edLeftFooter.php');
require('edBookOptions.php');
require('edFooter.php');
}

private function leftView ($leftView, $rightView, $b, $p) {
global $root, $lang, $pageTitle;
$t = 'getTranslation';
switch($leftView) {
case 'tv':  require('edTocView.php'); break;
case 'fv':  require('edFileView.php'); break;
}}

function editorMain ($leftView, $rightView, $b, $p) {
global $root, $lang, $pageTitle;
$pageTitle = $b->getTitle() .' &mdash; '. basename($p->fileName);
$t = 'getTranslation';
require('edHeader.php');
$this->leftView($leftView, $rightView, $b, $p);
require('edLeftFooter.php');
switch($rightView){
case 'editor': 
require('edRightHeader.php');
if ($p->mediaType=='application/xhtml+xml') require('edHTMLEditor.php'); 
else require('edTextEditor.php');
break;
case 'options': 
require('edRightHeader.php');
require('edPageOptions.php'); 
break;
}
require('edFooter.php');
}


}
?>