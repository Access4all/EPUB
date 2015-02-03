<?php
loadTranslation('editor-bookOptions');
$h = 'htmlspecialchars';
$authors = htmlspecialchars( implode("\r\n", preg_split('/\s*[,;]\s*/', $b->getAuthors()) ));
$checked = function($x){ return $x? 'checked' : ''; };
$b->getOption('dummy');
$istemplate = !!($b->bflags&BF_TEMPLATE)? 'checked="checked" ':'';
echo <<<END
<div id="rightPanel">
<h1>{$t('BookOptions')}</h1>
<p class="helpicon"><a href="#" class="infobox" data-infobox="bookoptions"><img src="$root/images/24px/attention.png" alt="{$t('btnHelp')}" /></a></p>
<form action="" method="post" data-track-changes>
<h2 data-expands="partGeneral">{$t('General')}</h2>
<div id="partGeneral">
<p><label for="title">{$t('BookTitle')}: </label>
<input type="text" id="title" name="title" value="{$h($b->getTitle())}" required aria-required="true" /></p>
<p><label for="authors">{$t('Authors')}: </label>
<textarea id="authors" name="authors" rows="4" cols="60" requried aria-required="true">$authors</textarea></p>
<p><label for="identifier">{$t('BookIdentifier')}: </label>
<input type="text" id="identifier" name="identifier" value="{$h($b->identifier)}" /></p>
<p><label for="language">{$t('BookLanguage')}: </label>
<input type="text" id="language" name="bookLanguage" value="{$h($b->language)}" required aria-required="true" /></p>
</div><!--partGeneral-->
<h2 data-expands="partOrg">{$t('BookOrganisation')}</h2>
<div id="partOrg">
<fieldset><legend>{$t('DefaultDirs')}</legend>
<p>{$t('DDBTTipp')}</p>
END;
foreach(array('text', 'image', 'font', 'javascript', 'css') as $x) {
echo <<<END
<p><label for="defaultDirByType_$x">{$t("DDBT_$x")}:</label>
<input type="text" id="defaultDirByType_$x" name="defaultDirByType[$x]" value="{$h($b->getOption("defaultDirByType:$x"))}" /></p>
END;
}
echo <<<END
</fieldset>
<fieldset>
<p><label for="cssMasterFile">{$t('cssMasterFile')}:</label>
<input type="text" id="cssMasterFile" name="cssMasterFile" value="{$h($b->getOption('cssMasterFile', 'EPUB/css/epub3.css'))}" /></p>
</fieldset>
</div><!--partOrg-->
<h2 data-expands="partTOC">{$t('TOCOptions')}</h2>
<div id="partTOC">
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
</div><!--partTOC-->
END;
if (@$b->eflags&BF_ADMIN) {
echo <<<END
<h2 data-expands="partUsers">{$t('SharedEdition')}</h2>
<p class="helpicon"><a href="#" class="infobox" data-infobox="bookoptions-sharing"><img src="$root/images/24px/attention.png" alt="{$t('btnHelp')}" /></a></p>
<div id="partUsers">
<table id="rightstable">
<thead><tr>
<th id="lblUser" scope="col">{$t('User')}</th>
<th id="lblRead" scope="col">{$t('RReading')}</th>
<th id="lblWrite" scope="col">{$t('RWrite')}</th>
<th id="lblAdmin" scope="col">{$t('RAdmin')}</th>
</tr></thead><tbody>
END;
foreach($b->getRightsTable() as $entry) {
$uid = floor($entry->id);
$rsel = ($entry->flags&BF_READ? 'checked="checked"  ':'');
$wsel = ($entry->flags&BF_WRITE? 'checked="checked" ':'');
$asel = ($entry->flags&BF_ADMIN? 'checked="checked" ':'');
if ($uid==0) $entry->displayName='*';
echo <<<END
<tr>
<th scope="row"><input type="hidden" name="share[$uid][]" value="0" />
<span id="lblUsr$uid">{$entry->displayName}</span></th>
<td><input type="checkbox" name="share[$uid][]" value="1" aria-labelledby="lblUsr$uid lblRead" title="{$entry->displayName} {$t('RReading')}" $rsel/></td>
<td><input type="checkbox" name="share[$uid][]" value="2" aria-labelledby="lblUsr$uid lblWrite" title="{$entry->displayName} {$t('RWrite')}" $wsel/></td>
<td><input type="checkbox" name="share[$uid][]" value="4" aria-labelledby="lblUsr$uid lblAdmin" title="{$entry->displayName} {$t('RAdmin')}" $asel/></td>
</tr>
END;
}
echo <<<END
<tr>
<th scope="row"><input type="text" name="shareNew[0][user]" aria-labelledby="lblUser" title="{$t('User')}" /></th>
<td><input type="checkbox" name="shareNew[0][read]" value="1" aria-labelledby="lblRead" /></td>
<td><input type="checkbox" name="shareNew[0][write]" value="2" aria-labelledby="lblWrite" /></td>
<td><input type="checkbox" name="shareNew[0][admin]" value="4" aria-labelledby="lblAdmin" /></td>
</tr>
</tbody></table>
<script type="text/javascript" src="$root/js/editor-bookoptions-rightstable.js"></script>
</div><!--partUsers-->
END;
}
echo <<<END
<h2 data-expands="partAdvanced">{$t('Advanced')}</h2>
<div id="partAdvanced">
<p><input type="checkbox" id="template" name="template" value="1" $istemplate/>
<label for="template">{$t('DefineAsTemplate')}</label>
<a href="#" class="infobox" data-infobox="bookoptions-define-as-template"><img src="$root/images/24px/attention.png" alt="{$t('btnHelp')}" /></a>
</p>
</div><!--partAdvanced-->
<p>
<button type="submit">{$t('Save')}</button>
<button type="reset">{$t('Reset')}</button>	
</p>
</form>
END;
?>