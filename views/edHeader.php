<?php
header('Content-Type: text/html; charset=utf-8');
if (!isset($pageTitle)) $pageTitle = 'No title';
$t = 'getTranslation';
$pn = (isset($p)&&is_object($p)? $p->fileName : '');
$rnd = rand(1,1000000);

$boActive = ($rightView=='bookoptions'? ' class="active"' : '');
$npActive = ($rightView=='newpage'? ' class="active"' : '');
$afActive = ($rightView=='addfiles'? ' class="active"' : '');

foreach(array('sv', 'tv', 'fv', 'zv') as $vt) {
${$vt.'Active'} = ($leftView==$vt? ' class="active"' : '');
}

echo <<<END
<!DOCTYPE HTML>
<html><head>
<title>$pageTitle</title>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="$root/editor/{$b->name}/getTemplate/css.css?rnd=$rnd" />
<link rel="stylesheet" href="$root/css/editor.css" />
</head><body>
<div id="fullWrapper">
<div id="topPanel">
<ul class="menul">
<li><a class="button" role="button" href="$root/bookshelf/index">{$t('BackToBookshelf')}</a></li>
<li$boActive><a href="$root/editor/{$b->name}/{$leftView}_bookoptions/" class="button" role="button">{$t('BookOptions')}</a></li>
<li$npActive><a href="$root/editor/{$b->name}/{$leftView}_newpage/$pn" role="button" class="button">{$t('CreateNewPage')}</a></li>
<li$afActive><a href="$root/editor/{$b->name}/{$leftView}_addfiles/$pn" role="button" class="button">{$t('AddFiles')}</a></li>
</ul>
</div>
<div id="leftRightWrapper">
<div id="leftPanel">
END;
?>