<?php
loadTranslation('editor-mcq');
$t = 'getTranslation';
$h = 'htmlspecialchars';
$d = $p->getDataDoc() ->documentElement;
$quizType = $d->getAttribute('type');
echo <<<END
<h2>{$t('MCQLong')}</h2>
<fieldset>
<legend>{$t('QuizType')}</legend>
END;
foreach(array('simple', 'complex') as $qt) {
$checked = ($qt==$quizType? ' checked="checked"' : '');
echo <<<END
<p><input type="radio" name="quizType" id="quizType_$qt" value="$qt"$checked aria-labelledby="quizTypeLbl_$qt" aria-describedby="quizTypeDesc_$qt" />
<label for="quizType_$qt" id="quizTypeLbl_$qt">{$t("QuizType_$qt")}</label></p>
<p id="quizTypeDesc_$qt">{$t("QuizTypeDesc_$qt")}</p>
END;
}
echo <<<END
</fieldset>
END;
?>