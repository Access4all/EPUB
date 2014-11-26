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
//$view->bookOptions($leftViewMethod, $rightViewMethod, $b);

private function leftView ($leftView, $rightView, $b, $p) {
global $root, $lang, $pageTitle;
$pn = (isset($p)&&is_object($p)? $p->fileName : '');
$t = 'getTranslation';
if ($rightView!='options' && $rightView!='code') $rightView = 'editor';

foreach(array('sv', 'tv', 'fv', 'zv') as $vt) {
${$vt.'Active'} = ($leftView==$vt? ' class="active"' : '');
${$vt.'Pressed'} = ($leftView==$vt? 'true' : 'false' );
${$vt.'LinkVN'} = ($leftView==$vt? '00' : $vt );
}

echo <<<END
<h2$tvActive><a role="button" aria-expanded="$tvPressed" href="$root/editor/{$b->name}/{$tvLinkVN}_{$rightView}/$pn">{$t('TocView')}</a></h2>
END;
if ($leftView=='tv') { require('edTocView.php'); }
echo <<<END
<h2$svActive><a role="button" aria-expanded="$svPressed" href="$root/editor/{$b->name}/{$svLinkVN}_{$rightView}/$pn">{$t('SpineView')}</a></h2>
END;
if ($leftView=='sv') { require('edSpineView.php'); }
echo <<<END
<h2$fvActive><a role="button" aria-expanded="$fvPressed" href="$root/editor/{$b->name}/{$fvLinkVN}_{$rightView}/$pn">{$t('FileView')}</a></h2>
END;
if ($leftView=='fv') { require('edFileView.php'); }
echo <<<END
<h2$zvActive><a role="button" aria-expanded="$zvPressed" href="$root/editor/{$b->name}/{$zvLinkVN}_{$rightView}/$pn">{$t('TemplateEditorView')}</a></h2>
END;
if ($leftView=='zv') { require('edTemplateEditor.php'); }
}

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
require("ed{$p->getEditorType()}Editor.php"); 
break;
case 'code' :
loadTranslation('editor-rtz');
require('edTextEditor.php');
break;
case 'options': 
loadTranslation('editor-pageOptions');
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
case 'bookoptions' :
require('edBookOptions.php');
break;
}
require('edFooter.php');
}


}
?>