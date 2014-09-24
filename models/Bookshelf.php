<?php
require_once('core/kernel.php');

class Bookshelf {

function getBookList () {
global $db;
return $db->query('select * from Books')
->fetchAll(PDO::FETCH_CLASS, 'Book');
}

function getBookById ($id) {
global $db;
$re = $db->query('select * from Books where id = %d', floor($id));
if (!$re || $re->rowCount()<1) return null;
$re->setFetchMode(PDO::FETCH_CLASS, 'Book');
return $re->fetch();
}

function deleteBook ($b) {
global $db;
if ($b && $b->delete()) {
$db->exec('delete from Books where id = %d', floor($b->id));
return true;
}
else return false;
}

function addBook ($b) {
global $db;
$db->exec('replace into Books (name, title, authors, lastUpdate) values (%s, %s, %s, UNIX_TIMESTAMP())', $b->name, $b->getTitle(), $b->getAuthors() );
return true;
}

function importBookFromFile ($file) {
global $booksdir;
$regs = array(
utf8_encode('/[àáâäãÀÁÂÄÃ]/u') => 'a',
utf8_encode('/[éèëêÉÈËÊ]/u') => 'e',
utf8_encode('/[ïîíìÏÎÍÌ]/u') => 'i',
utf8_encode('/[òóöôõÖÔÕÓÒøØ]/u') => 'o',
utf8_encode('/[ùúüûÜÛÚÙ]/u') => 'u',
utf8_encode('/[ÿýÝ]/u') => 'y',
utf8_encode('/[ñÑ]/u') => 'n',
utf8_encode('/[çÇ]/u') => 'c',
'/[][{}()<> +*%\/\\$#@&|=~,;.:]/u' => '-',
);//
$fs = new ZipFileSystem($file);
$b = new Book(array('fs'=>$fs));
$name = $b->getTitle();
$name = preg_replace(array_keys($regs), array_values($regs), $name);
$name = mb_strtolower($name);
copy($file, "$booksdir/$name.epub");
$b = new Book(array('name'=>$name));
return $this->addBook($b);
}


}
?>