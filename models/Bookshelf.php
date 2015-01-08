<?php
require_once('core/kernel.php');
require_once('models/Book.php');

class Bookshelf {

static function getInstance () {
return new Bookshelf();
}

function getBookList () {
global $db, $user;
if (!$db || !$user) return array();
return $db->query('select b.*, l.flags as eflags  
from '.DB_TABLE_PREFIX.'BookUsers l
join '.DB_TABLE_PREFIX.'Books b on b.id = l.book
where l.user = %d
', floor($user->id) )
->fetchAll(PDO::FETCH_CLASS, 'Book');
}

function getBookTemplateList () {
$b1 = new Book(array('name'=>'template', 'title'=>'Default'));
return array($b1);
}

function getBookById ($id) {
global $db;
$re = $db->query('select * from '.DB_TABLE_PREFIX.'Books where id = %d', floor($id));
if (!$re || $re->rowCount()<1) return null;
$re->setFetchMode(PDO::FETCH_CLASS, 'Book');
return $re->fetch();
}

function getBookByName ($bookName) {
global $db, $user;
$uid = $user? $user->id : 0;
$re = $db->query('select b.*, l.flags as eflags  
from '.DB_TABLE_PREFIX.'Books b, '.DB_TABLE_PREFIX.'BookUsers l  
where b.name=%s and b.id=l.book and (l.user=0 or l.user=%d) ', 
$bookName, floor($uid) );
if (!$re || $re->rowCount()<1) return null;
$re->setFetchMode(PDO::FETCH_CLASS, 'Book');
$b = $re->fetch();
if (@$b->eflags) return $b;
else return null;
}

function deleteBook ($b) {
global $db, $user;
if (!$db||!$user) return false;
return  $b
&& ($db->exec('delete from '.DB_TABLE_PREFIX.'BookUsers where book = %d and user = %d and flags&4', floor($b->id), floor($user->id) ))>0
&& $b->delete()
&& ($db->exec('delete from '.DB_TABLE_PREFIX.'BookUsers where book = %d', floor($b->id)))>0
&& ($db->exec('delete from '.DB_TABLE_PREFIX.'Books where id = %d', floor($b->id) ))>0;
}

function updateBook ($b) {
global $db, $user;
if (!$db||!$user) return false;
$db->exec('update '.DB_TABLE_PREFIX.'Books set title = %s, authors = %s where name = %s', $b->getTitle(), $b->getAuthors(), $b->name);
}

function addBook ($b) {
global $db, $user;
if (!$db||!$user) return false;
$b->id = $db->insert('replace into '.DB_TABLE_PREFIX.'Books (name, title, authors, lastUpdate, bflags) values (%s, %s, %s, %d, %d)', $b->name, $b->getTitle(), $b->getAuthors(), time(), floor($b->bflags)   );
$this->addBookUser($b, $user, BF_READ | BF_WRITE | BF_ADMIN);
return $b;
}

function addBookUser ($b, $u, $flags) {
if (!$flags) return $this->deleteBookUser($b,$u);
global $db;
if (is_object($b)) $b = $b->id;
if (is_object($u)) $u = $u->id;
$db->exec('replace into '.DB_TABLE_PREFIX.'BookUsers (book, user, flags) values (%d, %d, %d)', floor($b), floor($u), floor($flags) );
return true;
}

function deleteBookUser ($b, $u) {
global $db;
if (is_object($b)) $b = $b->id;
if (is_object($u)) $u = $u->id;
$db->exec('delete from '.DB_TABLE_PREFIX.'BookUsers where book=%d and user=%d', floor($b), floor($u) );
return true;
}

function getBookRightsTable ($bookId) {
global $db;
return $db->query('select l.flags, u.id, u.name, u.displayName
from '.DB_TABLE_PREFIX.'BookUsers l
join '.DB_TABLE_PREFIX.'Users u on u.id=l.user
where l.book=%d
', floor($bookId) )
->fetchAll(PDO::FETCH_OBJ);
}

function updateBookRightsTable ($bookId, &$info) {
global $db;
if (is_object($bookId)) $bookId = $bookId->id;
if (isset($info['share'])) foreach($info['share'] as $userId=>$cbt) {
$flags=0;
foreach($cbt as $f) $flags|=$f;
$this->addBookUser($bookId, $userId, $flags);
}
if (isset($info['shareNew'])) foreach($info['shareNew'] as $t) {
$names = array('read', 'write', 'admin');
$lp = LoginProvider::getInstance();
$userName = $t['user'];
$userObj = $lp->getUserByName($userName);
if (!$userObj) continue; // Entered user doesn't exist, simply skip
$userId = floor($userObj->id);
$flags=0;
for ($i=0; $i<count($names); $i++) if (isset($t[$names[$i]])) $flags|=(1<<$i);
$this->addBookUser($bookId, $userId, $flags);
}}

function createBookFromFile ($file, $info=null) {
global $booksdir;
if ($info==null) $info = array();
$bf = new BookFactory();
$b = $bf->createBookFromFile($this, $info, $file);
if (!$b || !$b->exists()) return false;
return $b;
}


}
?>