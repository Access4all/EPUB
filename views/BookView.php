<?php
class BookView {

public function processPage ($b, $item, &$html) {
global $root;
loadTranslation('book');
$spine = $b->getSpine();
$index = array_search($item, $spine, true);
$navItem = $b->getNavItem();
$isnav = $navItem === $item;
$navLabel = getTranslation('btnNav');
$prevLabel = getTranslation('btnPrev');
$nextLabel = getTranslation('btnNext');
$baseUrl = "$root/book/{$b->name}/view/";
$navUrl = (!$navItem? '#': $baseUrl .$b->getFileNameFromItem($navItem) );
$prevUrl = ($index<=0 || $index>count($spine) -1? '#': $baseUrl .$b->getFileNameFromItem($spine[$index -1]) );
$nextUrl = ($index<0 || $index>=count($spine) -1? '#': $baseUrl .$b->getFileNameFromItem($spine[$index+1]) );
$isnav = ($navUrl=='#'? ' aria-disabled="true"': ($isnav? ' aria-pressed="true"':''));
$isnext = ($nextUrl=='#'? ' aria-disabled="true"':'');
$isprev = ($prevUrl=='#'? ' aria-disabled="true"':'');
$navbar = <<<END
<div id="booknavbar" role="toolbar">
<a href="$prevUrl" role="button"$isprev>&larr; $prevLabel</a>
<a href="$navUrl" role="button"$isnav>&uarr; $navLabel</a>
<a href="$nextUrl" role="button"$isnext>$nextLabel &rarr;</a>
</div>
END;
$html = Strings::insertBefore($html, '<link rel="stylesheet" href="' .$root .'/css/bookview.css" />', '</head>');
$html = Strings::insertAfter($html, $navbar.'<main role="main">', '<body>');
$html = Strings::insertBefore($html, '</main>'.$navbar, '</body>');
}

}
?>