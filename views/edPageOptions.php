<?php
$t = 'getTranslation';
$filename = basename($p->fileName);
$h = 'htmlspecialchars';
$pageTitle = "{$t('PageOptions')} {$filename}";
$edpPageOptions = true;
require('edRightHeader.php');
echo <<<END
<form action="" method="post">
<h2>{$t('General')}</h2>
END;
if ($p->mediaType=='application/xhtml+xml') {
echo <<<END
<p><label for="title">{$t('PageTitle')}: </label>
<input type="text" id="title" name="title" value="{$h($p->getTitle())}" required="required" aria-required="true" /></p>
<p><label for="language">{$t('Language')}:</label>
<input type="text" id="language" name="language" value="{$h($p->getLanguage())}" /></p>
END;
}
echo <<<END
<p><label for="filename">{$t('FileName')}: </label>
<input type="text" id="filename" name="fileName" readonly="readonly" value="{$p->fileName}" /></p>
<p><label for="id">{$t('PageIdentifier')}: </label>
<input type="text" id="id" name="id" readonly="readonly" value="{$h($p->id)}" /></p>
END;
$apo = $p->getAdditionalPageOptions();
if ($apo) { require("ed{$apo}PageOptions.php"); }
echo <<<END
<p>
<button type="submit">{$t('Save')}</button>
<button type="reset">{$t('Reset')}</button>	
</p>
</form>
END;
?>