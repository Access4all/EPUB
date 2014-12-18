<?php
loadTranslation('editor-fillgaps');
$t = 'getTranslation';
$h = 'htmlspecialchars';
$d = $p->getDataDoc() ->documentElement;
$ftgType = $d->getAttribute('type');
require_once('edGeneralActivityPageOptions.php');
echo <<<END
<h2 data-expands="partFTG">{$t('FillTheGaps')}</h2>
<div id="partFTG">
<fieldset>
<legend>{$t('FTGType')}</legend>
<dl>
END;
foreach(array('simple', 'indicative', 'strict') as $qt) {
$checked = ($qt==$ftgType? ' checked="checked"' : '');
echo <<<END
<dt><input type="radio" name="ftgType" id="ftgType_$qt" value="$qt"$checked aria-labelledby="ftgTypeLbl_$qt" aria-describedby="FTGTypeDesc_$qt" />
<label for="ftgType_$qt" id="ftgTypeLbl_$qt">{$t("FTGType_$qt")}</label></dt>
<dd id="ftgTypeDesc_$qt">{$t("FTGTypeDesc_$qt")}</dd>
END;
}
echo <<<END
</dl>
</fieldset>
</div><!--partFTG-->
END;
?>