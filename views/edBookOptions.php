<?php
$h = 'htmlspecialchars';
$authors = htmlspecialchars( implode("\r\n", preg_split('/\s*[,;]\s*/', $b->getAuthors()) ));
echo <<<END
<div id="rightPanel">
<h1>{$t('BookOptions')}</h1>
<form action="" method="post">
<h2>{$t('General')}</h2>
<p><label for="title">{$t('BookTitle')}: </label>
<input type="text" id="title" name="title" value="{$h($b->getTitle())}" /></p>
<p><label for="authors">{$t('Authors')}: </label>
<textarea id="authors" name="authors" rows="4" cols="60">$authors</textarea></p>
<p>
<button type="submit">{$t('Save')}</button>
<button type="reset">{$t('Reset')}</button>	
</p>
</form>
END;
?>