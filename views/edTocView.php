<?php
if ($b->getOption('tocNeedRegen', false)) $b->updateTOC();

$doc = @DOM::loadHTMLString( $b->getContentsByFileName( $b->getNavFileName() ));
$toc = $doc->getFirstElement(function($e){  return $e->getAttribute('epub:type')=='toc'; });
if (!$toc) {
$b->updateTOC();
$doc = @DOM::loadHTMLString( $b->getContentsByFileName( $b->getNavFileName() ));
$toc = $doc->getFirstElement(function($e){  return $e->getAttribute('epub:type')=='toc'; });
}
if ($toc) {
$toc = $toc->getFirstElementByTagName('ol');
if (!$toc) $toc=null;
else {
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
echo '<div class="leftPanelTab">';
if ($toc) echo $toc->saveHTML();
else echo '<p><strong>', getTranslation('ErrTocNotAvail'), '</strong></p>';
echo '</div><!--leftPanelTab-->';
?>