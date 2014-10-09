<?php
loadTranslation('editor-bookOptions');
$h = 'htmlspecialchars';
$authors = htmlspecialchars( implode("\r\n", preg_split('/\s*[,;]\s*/', $b->getAuthors()) ));
$checked = function($x){ return $x? 'checked' : ''; };
$b->getOption('dummy');
echo <<<END
<div id="rightPanel">
<h1>{$t('BookOptions')}</h1>
<form action="" method="post">
<h2>{$t('General')}</h2>
<p><label for="title">{$t('BookTitle')}: </label>
<input type="text" id="title" name="title" value="{$h($b->getTitle())}" /></p>
<p><label for="authors">{$t('Authors')}: </label>
<textarea id="authors" name="authors" rows="4" cols="60">$authors</textarea></p>
<p><label for="identifier">{$t('BookIdentifier')}: </label>
<input type="text" id="identifier" name="identifier" value="{$h($b->identifier)}" /></p>
<p><label for="language">{$t('BookLanguage')}: </label>
<input type="text" id="language" name="language" value="{$h($b->language)}" /></p>
<h2>{$t('TOCOptions')}</h2>
<p>
<input type="checkbox" id="tocNoGen" name="tocNoGen" {$checked($b->getOption('tocNoGen',false))} />
<label for="tocNoGen">{$t('TOCNoGen')}</label></p>
<p><label for="tocHeadingText">{$t('TOCHeadingText')}: </label>
<input type="text" id="tocHeadingText" name="tocHeadingText" value="{$h($b->getOption('tocHeadingText', getTranslation('TableOfContents')))}" /></p>
<p><label for="tocMaxDepth">{$t('TOCMaxDepth')}: </label>
<select id="tocMaxDepth" name="tocMaxDepth">
END;
for ($i=2; $i<=6; $i++) {
$j=$i-1;
$sel = ($b->getOption('tocMaxDepth', 4)==$i? ' selected':'');
echo <<<END
<option value="$i"$sel>$j {$t('levels')}</option>
END;
}
echo <<<END
</select>
</p>
<p>
<button type="submit">{$t('Save')}</button>
<button type="reset">{$t('Reset')}</button>	
</p>
</form>
END;
?>