<?php
loadTranslation('editor');
header('Content-Type: text/html; charset=utf-8');
if (!isset($pageTitle)) $pageTitle = 'No title';
$t = 'getTranslation';
$rnd = md5(time().rand(1,1000000).$_SERVER['REMOTE_ADDR']);

echo <<<END
<!DOCTYPE HTML>
<html><head>
<title>$pageTitle</title>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="$root/css/editor.css" />
</head><body>
<div id="fullWrapper">
<div id="leftPanel">
<ul class="menul">
<li><a class="button" role="button" href="$root/bookshelf/index">{$t('BackToBookshelf')}</a></li>
<li><a href="#" class="button" role="button" id="btnBookOptions">{$t('BookOptions')}</a></li>
</ul><!-- upper left buttons -->
<div>
<ol id="treeView">
<li><span>Item 1</span></li>
<li><span>Item 1,5</span></li>
<li><span>Item 2</span></li>
<li><span>Item 3</span>
<ol>
<li><span>Item 3.1</span></li>
<li><span>Item 3.2</span>
<ol>
<li><span>Item 3.2.1</span></li>
<li><span>Item 3.2.2</span></li>
<li><span>Item 3.2.3</span></li>
<li><span>Item 3.2.4</span></li>
</ol>
</li>
<li><span>Item 3.3</span></li>
<li><span>Item 3.4</span></li>
<li><span>Item 3.5</span></li>
</ol></li>
<li><span>Item 4</span></li>
<li><span>Item 5</span></li>
<li><span>Item 6</span></li>
</ol>
</div><!-- tree view -->
</div><!-- left panel -->
<div id="rightPanel">
<p id="noscript">{$t('JavaScriptNeeded')}</p>
<ul id="pageTabs" class="menul">
<li><a href="#" id="btnPageEditor">{$t('PageEditor')}</a></li>
<li><a href="#" id="btnPageOptions">{$t('PageOptions')}</a></li>
</ul>
<div id="pageEditor" class="hidden">
<div id="editor" contenteditable="true">
<p>This is the editable zone</p>
<p>Nothig is loaded at the moment, but you can edit the text.</p>
</div><!--editor-->
</div><!--page editor-->
<div id="pageOptions" class="hidden">
<h1>{$t('PageOptions')}</h1>
<p>Page options; currently empty.</p>
</div><!--page options-->
</div><!-- right panel -->
</div><!-- full wrapper -->
<script type="text/javascript" src="$root/js/global.js?rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/stringTable.php?modules=editor&amp;lang=$lang&amp;rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/editor.js?rnd=$rnd"></script>
</body></html>
END;
?>