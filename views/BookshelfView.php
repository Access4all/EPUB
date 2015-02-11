<?php
require_once('core/kernel.php');
loadTranslation('bookshelf');

class BookshelfView {

function index ($bookList, $templateList, $templates) {
global $root, $lang, $langs, $user;
$templates = $templates?'index':'templates';
$t = 'getTranslation';
$pageTitle = getTranslation('Bookshelf');
require('bsHeader.php');
if (isset($_SESSION['failed'], $_SESSION['alertmsg'])) {
$msg = $_SESSION['alertmsg'];
$type = $_SESSION['failed']? 'error' : 'success';
unset($_SESSION['failed']);
unset($_SESSION['alertmsg']);
echo <<<END
<div role="alert" id="alert" class="$type">
<p><strong>$msg</strong></p>
</div>
END;
}
echo <<<END
<ul id="usernav">
<li><a href="$root/user">{$t('GoToUserProfile')}</a></li>
END;
if ($user&&$user->isAdmin()) echo "<li><a href=\"$root/admin\">{$t('GoToAdminPanel')}</a></li>";
echo <<<END
<li><a href="$root/user/signoff">{$t('Signoff')}</a></li>
</ul>
<h2>{$t("bsh2_$templates")}</h2>
<table>
<thead><tr>
<th scope="col">{$t('BookTitle')}</th>
<th scope="col">{$t('BookAuthors')}</th>
<th scope="col">{$t('ParticipatingUsers')}</th>
<th scope="col">{$t('Actions')}</th>
</tr></thead><tbody>
END;
foreach($bookList as $b) {
$viewUrl = "$root/book/{$b->name}/view/";
$exportUrl = "$root/book/{$b->name}/export/epub3";
$deleteUrl = "$root/bookshelf/{$b->id}/delete";
$editUrl = "$root/editor/{$b->name}/index/";
echo <<<END
<tr>
<th scope="row"><a href="$viewUrl">{$b->title}</a></th>
<td>{$b->authors}</td>
<td>
END;
{
$a = array();
$rightsnames = array('nothing', 'reading', 'writing', 'writing', 'administrate', 'administrate', 'administrate', 'administrate');
foreach($b->getParticipatingUsers() as $u) {
$rights = getTranslation($rightsnames[($u->eflags)]);
$a[] = "{$u->displayName} ({$rights})";
}
echo implode(',<br />', $a);
}
echo <<<END
</td><td>
<a href="$viewUrl" role="button">{$t('btnView')}</a>
<a href="$editUrl" role="button">{$t('btnEdit')}</a>
<a href="$exportUrl" role="button">{$t('btnExport')}</a>
<a href="$deleteUrl" role="button">{$t('btnDelete')}</a>
</td>
</tr>
END;
}
echo <<<END
</tbody></table>
<p>
<a href="$root/bookshelf/$templates">{$t("showTemplates_$templates")}</a>
<a href="#" class="infobox" data-infobox="template"><img src="$root/images/24px/attention.png" alt="{$t('btnHelp')}" /></a>
</p>
<h2>{$t('AddToBookshelf')}</h2>
<form action="$root/bookshelf/upload" method="post" enctype="multipart/form-data">
<p><label for="upload">{$t('UploadFile')} : </label>
<input type="file" id="upload" name="upload" />
<button type="submit">{$t('BtnAdd')}</button>
<a href="#" class="infobox" data-infobox="addbook"><img src="$root/images/24px/attention.png" alt="{$t('btnHelp')}" /></a>
</p></form>
<h2>{$t('CreateNewBook')}</h2>
<form action="$root/bookshelf/newBook" method="post">
<p><label for="title">{$t('BookTitle')}: </label>
<input type="text" name="title" id="title" />
<label for="template">{$t('Template')}:</label>
<select id="template" name="template">
END;
foreach($templateList as $tpl) {
$name = $tpl->name;
$label = $tpl->getTitle();
echo "<option value=\"$name\">$label</option>";
}
echo <<<END
</select>
<button type="submit">{$t('BtnCreate')}</button>
<a href="#" class="infobox" data-infobox="createbook"><img src="$root/images/24px/attention.png" alt="{$t('btnHelp')}" /></a>
</p></form>
END;
require('bsFooter.php');
}

}
?>