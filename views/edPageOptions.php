<?php
$t = 'getTranslation';
$filename = basename($p->fileName);
$h = 'htmlspecialchars';
$pageTitle = "{$t('PageOptions')} {$filename}";
$edpPageOptions = true;
$cbIsLinear = (@$p->linear===false? ' checked="checked"' : '');
require('edRightHeader.php');
echo <<<END
<form action="" method="post" data-track-changes>
END;
if ($p->mediaType=='application/xhtml+xml') {
echo <<<END
<h2 data-expands="partGeneral">{$t('General')}</h2>
<div id="partGeneral">
<p><label for="title">{$t('PageTitle')}: </label>
<input type="text" id="title" name="title" value="{$h($p->getTitle())}" required aria-required="true" /></p>
<p><label for="language">{$t('Language')}:</label>
<input type="text" id="language" name="bookLanguage" value="{$h($p->getLanguage())}" required aria-required="true" /></p>
</div><!--partGeneral-->
END;
}
$apo = $p->getAdditionalPageOptions();
if ($apo) { require("ed{$apo}PageOptions.php"); }
echo <<<END
<h2 data-expands="partAdvanced">{$t('Advanced')}</h2>
<div id="partAdvanced">
<p><input type="checkbox" id="linear" name="linear"$cbIsLinear />
<label for="linear">{$t('CbLinear')}</label>
<a href="#" class="infobox" data-infobox="pageoptions-nospinecb"><img src="$root/images/24px/attention.png" alt="{$t('btnHelp')}" /></a>
</p>
<p><label for="filename">{$t('FileName')}: </label>
<input type="text" id="filename" name="fileName" readonly value="{$p->fileName}" />
<a href="#" class="infobox" data-infobox="filenamefield"><img src="$root/images/24px/attention.png" alt="{$t('btnHelp')}" /></a>
</p>
<p><label for="id">{$t('PageIdentifier')}: </label>
<input type="text" id="id" name="id" readonly value="{$h($p->id)}" />
<a href="#" class="infobox" data-infobox="pageidfield"><img src="$root/images/24px/attention.png" alt="{$t('btnHelp')}" /></a>
</p>
</div><!--partAdvanced-->
<p>
<button type="submit">{$t('Save')}</button>
<button type="reset">{$t('Reset')}</button>	
</p>
</form>
END;
?>