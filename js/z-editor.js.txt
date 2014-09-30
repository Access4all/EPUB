function MessageBox (title, text) {
var div = $c('div', {'role':'alertdialog', 'class':'dialogbox', 'text':[
$c('h1', {'text':title}),
$c('div', {'text':text})
]});
$('body').appendChild(div);
}

function Tabs_init (ul, initial, lst) {
var lis = ul.querySelectorAll('li');
for (var i=0; i<lis.length; i++) {
var li = lis[i];
var item = li.firstChild;
//item.setAttribute('role', 'tab');
item.setAttribute('aria-controls', lst[i].id);
item.onclick = Tabs_itemClick.bind(item, i, lst, ul, lis);
}
if (initial>=0) Tabs_itemClick.call(lis[initial].firstChild, initial, lst, ul, lis);
}

function Tabs_itemClick (idx, lst, ul, lis) {
for (var i=0; i<lst.length; i++) {
var tab = lst[i];
var item = lis[i].firstChild;
if (idx==i) domShow(tab);
else domHide(tab);
}
if (ul.curitem) ul.curitem.removeAttribute('aria-selected');
this.setAttribute('aria-selected', true);
return false;
}

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
if (!window.XMLHttpRequest && !window.ActiveXObject) return; // No AJAX
if (!document.querySelector || !document.querySelectorAll || !document.getElementById || !document.getElementsByTagName || !document.createElement || !document.createTextNode || !document.createDocumentFragment) return; // Missing one or more mandatory DOM functions
domRemove($('#noscript'));
Tabs_init($('#pageTabs'), 0, [$('#pageEditor'), $('#pageOptions')]);
//MessageBox('Test', '<p>It works !</p>');
});

alert('editor loaded');