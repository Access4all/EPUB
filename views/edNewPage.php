<?php
$t = 'getTranslation';
$h = 'htmlspecialchars';
echo <<<END
<div id="rightPanel">
<h1>{$t('CreateNewPage')}</h1>
<form action="" method="post">
<h2>{$t('General')}</h2>
<p><label for="title">{$t('PageTitle')}: </label>
<input type="text" id=2title" name="title" value="{$h(@$_POST['title'])}" /></p>
<p><label for="filename">{$t('FileName')}: </label>
<input type="text" id="filename" name="fileName" value="{$h(@$_POST['fileName'])}" /></p>
<p><label for="id">{$t('PageIdentifier')}: </label>
<input type="text" id="id" name="id" value="{$h(@$_POST['id'])}" /></p>
<fieldset>
<legend><h2>{$t('PageType')}</h2></legend>
END;
$first=true;
foreach (array('document') as $type) {
$checked = ((!isset($_POST['type'])&&$first) || (@$_POST['type']==$type) ? 'checked="checked" ':'');
$first=false;
echo <<<END
<p><input type="radio" id="pagetype_$type" name="type" value="$type" $checked/>
<label for="pagetype_$type">{$t("PageType_$type")}</label></p>
END;
}
echo <<<END
</fieldset>
<p>
<input type="hidden" name="newpage" value="true" />
<button type="submit">{$t('Save')}</button>
<a class="button" role="button" href="{$_SERVER['HTTP_REFERER']}">{$t('Cancel')}</a>
</p>
</form>
END;
?>