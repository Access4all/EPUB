<?php
if ($b->getOption('tocNeedRegen', false)) $b->updateTOC();

$t = 'getTranslation';
$toc = null;
$doc = DOM::loadXMLString( $b->getContentsByFileName( $b->getNavFileName() ));
if ($doc && $doc->documentElement) $toc = $doc->documentElement->getFirstElement(function($e){  return $e->getAttribute('epub:type')=='toc'; });
if (!$toc) {
$b->updateTOC();
$doc = @DOM::loadHTMLString( $b->getContentsByFileName( $b->getNavFileName() ));
if ($doc&&$doc->documentElement) $toc = $doc->documentElement->getFirstElement(function($e){  return $e->getAttribute('epub:type')=='toc'; });
}
if ($toc) {
$toc = $toc->getFirstElementByTagName('ol');
if ($toc) {
$toc->setAttribute('class', 'fileTree');
$toc->setAttribute('data-ctxtype', 'toc');
foreach($toc->getElements(function($e){ return $e->hasAttribute('href'); }) as $a) {
$href = $a->getAttribute('href');
$href = $b->getNavRelativeFileName($href);
$href = "$root/editor/{$b->name}/{$leftView}_{$rightView}/$href";
$a->setAttribute('href', $href);
}
}}//if toc

//echo '<h2>', getTranslation('TocView'), '</h2>';
echo <<<END
<div class="leftPanelTab">
<p><a role="button" class="button" href="$root/editor/{$b->name}/refreshTOC">{$t('Refresh')}</a></p>
END;
if ($toc && $toc->hasChildNodes()) echo $toc->saveHTML();
else if ($toc) {
echo <<<END
<p><strong>{$t('ErrTocEmpty')}</strong></p>
<p class="helpicon"><a href="#" class="infobox" data-infobox="newpage"><img src="$root/images/24px/attention.png" alt="{$t('btnHelp')}" /></a></p>
END;
}
else echo '<p><strong>', getTranslation('ErrTocNotAvail'), '</strong></p>';
echo '</div><!--leftPanelTab-->';
?>