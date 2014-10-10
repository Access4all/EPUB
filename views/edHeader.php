<?php
header('Content-Type: text/html; charset=utf-8');
if (!isset($pageTitle)) $pageTitle = 'No title';
$t = 'getTranslation';
$pn = (isset($p)&&is_object($p)? $p->fileName : '');
$rnd = rand(1,1000000);

echo <<<END
<!DOCTYPE HTML>
<html><head>
<title>$pageTitle</title>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="$root/editor/{$b->name}/contentCss/css.css?rnd=$rnd" />
<link rel="stylesheet" href="$root/css/editor.css" />
</head><body>
<div id="fullWrapper">
<div id="topPanel">
<ul class="menul">
<li><a class="button" role="button" href="$root/bookshelf/index">{$t('BackToBookshelf')}</a></li>
<li><a href="$root/editor/{$b->name}/{$leftView}_editor/" class="button" role="button">{$t('BookOptions')}</a></li>
<li><a href="$root/editor/{$b->name}/{$leftView}_newpage/$pn" role="button" class="button">{$t('CreateNewPage')}</a></li>
<li><a href="$root/editor/{$b->name}/{$leftView}_addfiles/$pn" role="button" class="button">{$t('AddFiles')}</a></li>
</ul>
</div>
<div id="leftPanel">
<ul class="menul">
<li><a href="$root/editor/{$b->name}/tv_{$rightView}/$pn">{$t('TocView')}</a></li>
<li><a href="$root/editor/{$b->name}/sv_{$rightView}/$pn">{$t('SpineView')}</a></li>
<li><a href="$root/editor/{$b->name}/fv_{$rightView}/$pn">{$t('FileView')}</a></li>
<li><a href="$root/editor/{$b->name}/zv_{$rightView}/$pn">{$t('TemplateEditorView')}</a></li>
</ul><!-- upper left buttons -->
END;
?>