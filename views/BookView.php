<?php
class BookView {

public function processPage ($b, $item, &$html) {
global $root;
loadTranslation('book');
$spine = $b->getSpine();
$index = array_search($item->id, $spine, true);
$navItem = $b->getNavItem();
$prevItem = ($index<=0 || $index>count($spine) -1? null: $b->getItemById($spine[$index -1]) );
$nextItem = ($index<0 || $index>=count($spine) -1? null: $b->getItemById($spine[$index +1] ));
$isnav = $navItem === $item;
$navLabel = getTranslation('btnNav');
$prevLabel = getTranslation('btnPrev');
$nextLabel = getTranslation('btnNext');
$printLabel = getTranslation('btnPrint');
$backLabel = getTranslation('btnBs');
$baseUrl = "$root/book/{$b->name}/view/";
$navUrl = (!$navItem? '#': $baseUrl .$navItem->fileName );
$prevUrl = (!$prevItem? '#': $baseUrl .$prevItem->fileName );
$nextUrl = (!$nextItem?  '#': $baseUrl .$nextItem->fileName );
$printUrl = "$baseUrl../onepage/";
$backUrl = "$root/";
$isnav = ($navUrl=='#'? ' aria-disabled="true"': ($isnav? ' aria-pressed="true"':''));
$isnext = ($nextUrl=='#'? ' aria-disabled="true"':'');
$isprev = ($prevUrl=='#'? ' aria-disabled="true"':'');
$navbar = <<<END
<div id="booknavbar" role="toolbar">
<a href="$backUrl" role="button">&nwarr; $backLabel</a>
<a href="$prevUrl" role="button"$isprev>&larr; $prevLabel</a>
<a href="$navUrl" role="button"$isnav>&uarr; $navLabel</a>
<a href="$nextUrl" role="button"$isnext>$nextLabel &rarr;</a>
<a href="$printUrl" role="button">$printLabel</a>
</div>
END;
$html = Strings::insertBefore($html, '<link rel="stylesheet" href="' .$root .'/css/bookview.css" />', '</head>');
$html = Strings::insertAfter($html, $navbar.'<main role="main">', '<body>');
$html = Strings::insertBefore($html, '</main>'.$navbar, '</body>');
}

}
?>