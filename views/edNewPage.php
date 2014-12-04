<?php
$t = 'getTranslation';
$h = 'htmlspecialchars';
echo <<<END
<div id="rightPanel">
<h1>{$t('CreateNewPage')}</h1>
<form action="" method="post">
<h2 data-expands="partGeneral">{$t('General')}</h2>
<div id="partGeneral">
<p><label for="title">{$t('PageTitle')}: </label>
<input type="text" id="title" name="title" value="{$h(@$_POST['title'])}" required="required" aria-required="true" /></p>
</div><!--partGeneral-->
<fieldset>
<legend><h2 data-expands="partPageType">{$t('PageType')}</h2></legend>
<div id="partPageType">
END;
$first=true;
foreach (array('document', /*'freequestions',*/ 'qcm', 'truefalse', 'fillgaps', 'matching', 'ordering') as $type) {
$checked = ((!isset($_POST['type'])&&$first) || (@$_POST['type']==$type) ? 'checked="checked" ':'');
$first=false;
echo <<<END
<p><input type="radio" id="pagetype_$type" name="type" value="$type" $checked aria-labelledby="pagetypeLbl_$type" aria-describedby="pagetypeDesc_$type" />
<label for="pagetype_$type" id="pagetypeLbl_$type">{$t("PageType_$type")}</label></p>
<p id="pagetypeDesc_$type">{$t("PageTypeDesc_$type")}</p>
END;
}
echo <<<END
</div><!--partPageType-->
</fieldset>
<h2 data-expands="partAdvanced">{$t('Advanced')}</h2>
<div id="partAdvanced">
<p><label for="filename">{$t('FileName')}: </label>
<input type="text" id="filename" name="fileName" value="{$h(@$_POST['fileName'])}" /></p>
<p><label for="id">{$t('PageIdentifier')}: </label>
<input type="text" id="id" name="id" value="{$h(@$_POST['id'])}" /></p>
</div>
<p>
<input type="hidden" name="newpage" value="true" />
<button type="submit">{$t('Save')}</button>
<a class="button" role="button" href="{$_SERVER['HTTP_REFERER']}">{$t('Cancel')}</a>
</p>
</form>
END;
?>