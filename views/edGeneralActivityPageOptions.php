<?php
$t = 'getTranslation';
$h = 'htmlspecialchars';
$d = $p->getDataDoc() ->documentElement;
$submitMode = $d->getAttribute('submission');
$submitURL = '';
if (substr($submitMode,0,4)=='http') {
$submitURL = $submitMode;
$submitMode = 'url';
}
echo <<<END
<h2 data-expands="partGA">{$t('GeneralActivity')}</h2>
<div id="partGA">
<fieldset>
<legend>{$t('SubmitMode')}</legend>
END;
foreach(array('local', 'file', 'url') as $st) {
$checked = ($st==$submitMode? ' checked="checked"' : '');
echo <<<END
<p><input type="radio" name="submitMode" id="submitMode_$st" value="$st"$checked aria-labelledby="submitModeLbl_$st" aria-describedby="submitModeDesc_$st" />
<label for="submitMode_$st" id="submitModeLbl_$st">{$t("SubmitMode_$st")}</label></p>
<p id="submitModeDesc_$st">{$t("SubmitModeDesc_$st")}</p>
END;
}
echo <<<END
</fieldset>
<p><label for="submitURL">{$t('SubmitURL')}:</label>
<input type="text" id="submitURL" name="submitURL" value="{$h($submitURL)}" /></p>
</div><!--partGA-->
END;
?>