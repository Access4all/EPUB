<?php
loadTranslation('editor-fillgaps');
$rnd = substr(md5(time()), 0, 12);
$simpleFileName = basename($p->fileName);
$pageLang = $p->getLanguage();
$doc = $p->getDataDoc();
$ftg = $doc->documentElement;
$contents = $doc->getFirstElementByTagName('intro')->saveInnerHTML();
$gaptext = $doc->getFirstElementByTagName('gaptext')->saveInnerHTML();
$gapType = $ftg->getAttribute('type');
$pageTitle = $simpleFileName;
require('edRightHeader.php');
require('edToolbar.php');
global $otherStringTable;
$otherStringTable = ',editor-fillgaps';
echo <<<END
<div class="edWrapper">
<div id="intro" class="editor" lang="$pageLang" contenteditable="true" data-toolbar="toolbar" DATA-AUTOFOCUS="TRUE" aria-label="{$t('IntroText')}">
$contents
</div></div><!--editor-->
END;

if ($gapType=='indicative' || $gapType=='strict') {
$gaplist = $ftg->getFirstElementByTagName('gaplist');
if (!$gaplist) $gaplist = '';
else $gaplist = implode("\r\n", DOM::nodeListToArray( $gaplist->getElementsByTagName('li') ));
echo <<<END
<p><label for="gaplist">{$t('GapList')}:</label>
<textarea id="gaplist" lang="$pageLang" rows="10" cols="50">
$gaplist
</textarea>
</p><p>{$t('GapListTipp')}</p>
END;
}

$theToolbarId = 'toolbar2';
$theToolbarAdditionalItems = <<<END
<span class="buttonGroup">
<button type="button" data-action="mark">{$t('MarkGap')}</button>
</span>
END;
require('edToolbar.php');
echo <<<END
<div class="edWrapper">
<div id="gaptext" class="editor" lang="$pageLang" contenteditable="true" data-toolbar="toolbar2" aria-label="{$t('GapText')}">
$gaptext
</div></div><!--editor-->
<script type="text/javascript" src="$root/js/editor-fillgaps.js?rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/editor-rtz.js?rnd=$rnd"></script>
END;
?>