<?php
$t = 'getTranslation';
$filename = basename($p->fileName);
$h = 'htmlspecialchars';
echo <<<END
<h1>{$t('PageOptions')} {$filename}</h1>
<form action="" method="post">
<p><label for="filename">{$t('FileName')}: </label>
<input type="text" id="filename" name="filename" value="{$p->fileName}" /></p>
<p><label for="id">{$t('PageIdentifier')}: </label>
<input type="text" id="id" name="id" value="{$h($p->id)}" /></p>
END;
if ($p->mediaType=='application/xhtml+xml') {
echo <<<END
<p><label for="title">{$t('PageTitle')}: </label>
<input type="text" id=2title" name="title" value="{$h($p->getTitle())}" /></p>
END;
}
echo <<<END
<p>
<button type="submit">{$t('Save')}</button>
<button type="reset">{$t('Reset')}</button>	
</p>
</form>
END;
?>