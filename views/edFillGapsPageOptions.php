<?php
loadTranslation('editor-fillgaps');
$t = 'getTranslation';
$h = 'htmlspecialchars';
$d = $p->getDataDoc() ->documentElement;
$ftgType = $d->getAttribute('type');
require_once('edGeneralActivityPageOptions.php');
echo <<<END
<h2>{$t('FillTheGaps')}</h2>
<fieldset>
<legend>{$t('FTGType')}</legend>
END;
foreach(array('simple', 'indicative', 'strict') as $qt) {
$checked = ($qt==$ftgType? ' checked="checked"' : '');
echo <<<END
<p><input type="radio" name="ftgType" id="ftgType_$qt" value="$qt"$checked aria-labelledby="ftgTypeLbl_$qt" aria-describedby="FTGTypeDesc_$qt" />
<label for="ftgType_$qt" id="ftgTypeLbl_$qt">{$t("FTGType_$qt")}</label></p>
<p id="ftgTypeDesc_$qt">{$t("FTGTypeDesc_$qt")}</p>
END;
}
echo <<<END
</fieldset>
END;
?>