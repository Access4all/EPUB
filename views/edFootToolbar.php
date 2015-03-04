<?php
$spine = $b->getSpine();
$index = array_search($p->id, $spine, true);
$prevItem = ($index<=0 || $index>count($spine) -1? null: $b->getItemById($spine[$index -1]) );
$nextItem = ($index<0 || $index>=count($spine) -1? null: $b->getItemById($spine[$index +1] ));
$baseUrl = "$root/editor/{$b->name}/{$leftView}_editor/";
$prevUrl = (!$prevItem? '#': $baseUrl .$prevItem->fileName );
$nextUrl = (!$nextItem?  '#': $baseUrl .$nextItem->fileName );
$isnext = ($nextUrl=='#'? ' aria-disabled="true"':'');
$isprev = ($prevUrl=='#'? ' aria-disabled="true"':'');
echo <<<END
<div id="footToolbar" class="footToolbar" role="toolbar">
<p>
<span class="buttonGroup">
<button class="saveBtn" type="button" data-action="save">{$t('Save')}</button>
<button type="button" data-action="preview" data-href="$root/editor/{$b->name}/preview/$pn">{$t('Preview')}</button>
</span>
<span class="buttonGroup">
<a role="button" href="$prevUrl"$isprev>&larr; {$t('btnPrev')}</a>
<a role="button" href="$nextUrl"$isnext>{$t('btnNext')} &rarr;</a>
</span>
</p>
</div><!--footToolbar-->
END;
?>