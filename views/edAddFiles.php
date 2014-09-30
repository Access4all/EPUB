<?php
$t = 'getTranslation';
$h = 'htmlspecialchars';
echo <<<END
<div id="rightPanel">
<h1>{$t('AddFiles')}</h1>
<form action="" method="post" enctype="multipart/form-data">
<h2>{$t('General')}</h2>
<p><label for="filename">{$t('FileName')}: </label>
<input type="text" id="filename" name="fileName" value="{$h(@$_POST['fileName'])}" /></p>
<p><label for="id">{$t('PageIdentifier')}: </label>
<input type="text" id="id" name="id" value="{$h(@$_POST['id'])}" /></p>
<h2>{$t('DirectUpload')}</h2>
<p><label for="upload">{$t('DirectUpload')}: </label>
<input type="file" id="upload" name="upload" /></p>
<p>
<input type="hidden" name="addfiles" value="true" />
<button type="submit">{$t('Save')}</button>
<a class="button" role="button" href="{$_SERVER['HTTP_REFERER']}">{$t('Cancel')}</a>
</p>
</form>
END;
?>