<?php
loadTranslation('editor-truefalse');
$rnd = substr(md5(time()), 0, 12);
$simpleFileName = basename($p->fileName);
$doc = $p->getDataDoc();
$quiz = $doc->documentElement;
$contents = $doc->getFirstElementByTagName('intro')->saveInnerHTML();
$simple = $quiz->getAttribute('type')=='simple';
$choices = DOM::nodeListToArray($quiz->getFirstElementByTagName('choices')->getElementsByTagName('c'));
echo <<<END
<h1>$simpleFileName</h1>
END;
require('edToolbar.php');
echo <<<END
<div class="edWrapper">
<div id="intro" class="editor" contenteditable="true" data-toolbar="toolbar" aria-label="{$t('IntroText')}">
$contents
</div></div><!--editor-->
<form id="quiz">
<div id="toolbar3"><p>
<button type="button" id="addColBtn">{$t('AddColumn')}</button>
<button type="button" id="remColBtn">{$t('RemoveColumn')}</button>
</p></div>
<table><thead><tr>
<th scope="col">{$t('Question')}</th>
END;
for ($i=0; $i<count($choices); $i++) {
$ii=$i+1;
$choice = $choices[$i];
$choice = $choice->saveInnerHTML();
$choices[$i] = $choice;
echo <<<END
<th scope="col"><span id="choice$i" class="qchoice" contenteditable="true" aria-label="{$t('Answer')} $ii">$choice</span></th>
END;
}
echo <<<END
</tr></thead>
<tbody>
END;
{ $i= -1; 
foreach($quiz->getElementsByTagName('question') as $q) {
$count = ++$i+1;
$text = $q->getFirstElementByTagName('q')->saveInnerHTML();
echo <<<END
<tr class="qrow">
<th scope="row"><span id="qlbl$i" class="qlabel"><span class="questionNumber">$count</span>. </span>
<span class="questionText" contenteditable="true" aria-labelledby="qlbl$i">$text</span></th>
END;
$answers = array_map(function($x){ return intval(strval($x)); }, DOM::nodeListToArray($q->getElementsByTagName('an')));
for ($j=0; $j<count($choices); $j++) {
$choice = $choices[$j];
$choice = htmlspecialchars(strip_tags($choice));
$name = $simple? "q[{$i}]" : "q[{$i}][]";
$id = "q{$i}_{$j}";
$itype = $simple? 'radio' : 'checkbox';
$checked = in_array($j, $answers)? ' checked="checked"' : '';
echo <<<END
<td><input type="$itype" name="$name" class="qanswerbox" id="$id" value="$j"$checked aria-labelledby="choice$j" /></td>
END;
}
echo '</tr>';
}}
echo <<<END
</tbody></table>
</form>
<script type="text/javascript" src="$root/js/editor-truefalse.js?rnd=$rnd"></script>
<script type="text/javascript" src="$root/js/editor-rtz.js?rnd=$rnd"></script>
END;
?>