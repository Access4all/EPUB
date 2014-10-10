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
if ($rightView!='options') $rightView = 'editor';
switch($leftView) {
case 'tv':  require('edTocView.php'); break;
case 'fv':  require('edFileView.php'); break;
case 'sv':  require('edSpineView.php'); break;
case 'zv':  require('edTemplateEditor.php'); break;
}}

function editorMain ($leftView, $rightView, $b, $p) {
global $root, $lang, $pageTitle;
if ($rightView=='newpage') $pageTitle = $b->getTitle() .' &mdash; '. getTranslation('CreateNewPage');
else if ($rightView=='addfiles') $pageTitle = $b->getTitle() .' &mdash; '. getTranslation('AddFiles');
else if (!$p) $pageTitle = $b->getTitle() .' &mdash; '. $b->getAuthors();
else $pageTitle = $b->getTitle() .' &mdash; '. basename($p->fileName);
$t = 'getTranslation';
require('edHeader.php');
$this->leftView($leftView, $rightView, $b, $p);
require('edLeftFooter.php');
switch($rightView){
case 'editor': 
loadTranslation('editor-rtz');
require('edRightHeader.php');
if ($p->mediaType=='application/xhtml+xml') require('edHTMLEditor.php'); 
else require('edTextEditor.php');
break;
case 'options': 
loadTranslation('editor-pageOptions');
require('edRightHeader.php');
require('edPageOptions.php'); 
break;
case 'newpage' :
loadTranslation('editor-pageOptions');
require('edNewPage.php');
break;
case 'addfiles' :
loadTranslation('editor-pageOptions');
require('edAddFiles.php');
break;
}
require('edFooter.php');
}


}
?>