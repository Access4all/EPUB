<?php
loadTranslation('editor-fillgaps');
$rnd = substr(md5(time()), 0, 12);
$simpleFileName = basename($p->fileName);
$doc = $p->getDataDoc();
$ftg = $doc->documentElement;
$contents = $doc->getFirstElementByTagName('intro')->saveInnerHTML();
$gaptext = $doc->getFirstElementByTagName('gaptext')->saveInnerHTML();

$gaptext = preg_replace('#<gap>(.*?)</gap>#ms', '<strong unselectable="ON" contenteditable="false">$1</strong>', $gaptext);

echo <<<END
<h1>$simpleFileName</h1>
END;
require('edToolbar.php');
echo <<<END
<div class="edWrapper">
<div id="intro" class="editor" contenteditable="true" data-toolbar="toolbar" aria-label="{$t('IntroText')}">
$contents
</div></div><!--editor-->
END;
$theToolbarId = 'toolbar2';
require('edToolbar.php');
echo <<<END
<div class="edWrapper">
<div id="gaptext" class="editor" contenteditable="true" data-toolbar="toolbar2" aria-label="{$t('GapText')}">
$gaptext
</div></div><!--editor-->
<script type="text/javascript" src="$root/js/editor-fillgaps.js?rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/editor-rtz.js?rnd=$rnd"></script>
END;
?>