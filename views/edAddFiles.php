<?php
$t = 'getTranslation';
$h = 'htmlspecialchars';
echo <<<END
<div id="rightPanel">
<h1>{$t('AddFiles')}</h1>
<p><a href="#" class="infobox" data-infobox="addfiles"><img src="$root/images/24px/attention.png" alt="{$t('btnHelp')}" /></a></p>
<form action="" method="post" enctype="multipart/form-data">
<h2 data-expands="partUpload">{$t('DirectUpload')}</h2>
<div id="partUpload">
<p><label for="uploads">{$t('DirectUpload')}: </label>
<input type="file" id="uploads" name="uploads[]" multiple="multiple" /></p>
<p>{$t('ImportFilesExpl')}</p>
</div>
<h2 data-expands="partAdvanced">{$t('Advanced')}</h2>
<div id="partAdvanced">
<p><label for="filename">{$t('FileName')}: </label>
<input type="text" id="filename" name="fileName" value="{$h(@$_POST['fileName'])}" /></p>
<p><label for="id">{$t('PageIdentifier')}: </label>
<input type="text" id="id" name="id" value="{$h(@$_POST['id'])}" /></p>
</div>
<p>
<input type="hidden" name="addfiles" value="true" />
<button type="submit">{$t('Save')}</button>
<a class="button" role="button" href="{$_SERVER['HTTP_REFERER']}">{$t('Cancel')}</a>
</p>
</form>
END;
?>