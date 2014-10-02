function Menu_show (originator, items) {
var ul = document.createElement('ul');
var firstA = null;
for (var i=0; i<items.length; i+=2) {
var label = items[i], action = items[i+1];
var a = ul.appendElement('li').appendElement('a').appendText(label);
a.href = (typeof(action)=='string'? action : '#');
if (typeof(action)=='function') a.onclick = Menu_click(action, originator, ul);
else if (!action) a.onclick = Menu_close.bind(ul, originator);
if (!firstA) firstA=a;
}
originator.parentNode.insertBefore(ul, originator.nextSibling);
firstA.focus();
}

function Menu_click (action, originator, ul) {
return function(e){ 
action.call(this,e); 
Menu_close.call(ul,originator); 
return false;
}}

function Menu_close (originator) {
this.parentNode.removeChild(this);
if (originator) originator.focus();
return false;
}

function InputBox (title, prompt, text, okFunc, cancelFunc) {
var form = document.createElement('form', {'role':'alertdialog'});
form.appendElement('h1').appendText(title);
var p = form.appendElement('p');
p.appendElement('label', {'for':'InputBoxInput', 'id':'InputBoxLabel'}).appendText(prompt);
var input = p.appendElement('input', {'type':'text', 'value':text, 'id':'InputBoxInput', 'aria-labelledby':'InputBoxLabel'});
p = form.appendElement('p');
var btnOk = p.appendElement('button', {'type':'submit'}).appendText(msgs.OK);
var btnCancel = p.appendElement('button', {'type':'reset'}).appendText(msgs.Cancel);
form.onsubmit = function(){ if (okFunc) okFunc(input.value); form.parentNode.removeChild(form); return false; };
form.onreset = function(){ if (cancelFunc) cancelFunc(); form.parentNode.removeChild(form); return false; };
document.querySelector('body').appendChild(form);
input.select();
input.focus();
}

function FileTree_init () {
var ctxItemFunc = FileTree_linkContextMenu( window['FileTree_CtxMenuItemList_'+this.getAttribute('data-ctxtype')] ,this);
this.$('a').each(function(){
this.oncontextmenu = ctxItemFunc;
});
this.$('ul,ol').each(function(){
var li = this.parentNode;
var a = li.appendElement('a', {'href':'#'});
a.appendText('+');
a.onclick = FileTree_expandLinkClick;
li.insertBefore(a, li.firstChild);
this.style.display='none';
});
//suite
}

function FileTree_MoveInitiate (link) {
window.tmpMoveName = link.innerHTML;
window.tmpMoveHref = link.href;
}

function FileTree_SpineMove (link, actionName) {
var src = window.tmpMoveHref;
var ref = link.href;
src = src.substring(window.rootUrl.length);
ref = ref.substring(window.rootUrl.length);
window.tmpMoveName = window.tmpMoveHref = null;
var url = window.rootUrl2 + actionName + '/?src=' + encodeURIComponent(src) + '&ref=' + encodeURIComponent(ref);
ajax('GET', url, null, function(re){if(re=='OK') window.location.reload(); else alert('Return! '+re);}, function(){alert('Failed!');});
return false;
}

function FileTree_expandLinkClick () {
var ul = this.parentNode.querySelector('ul,ol');
ul.style.display = (ul.style.display=='block'? 'none' : 'block');
this.firstChild.nodeValue = (ul.style.display=='block'? '-' : '+');
}

function FileTree_CtxMenuItemList_file (items, link) {
items.merge([
msgs.Rename, null,
msgs.Delete, null,
]);//
if (window.tmpMoveName) items.merge([
msgs.MoveHere.replace('%1', window.tmpMoveName), FileTree_SpineMove.bind(null, link, 'moveFile'),
]);//
else items.merge([
msgs.Move, FileTree_MoveInitiate.bind(null,link),
]);//
}

function FileTree_CtxMenuItemList_toc (items, link) {
if (window.tmpMoveName) items.merge([
msgs.MoveBefore.replace('%1', window.tmpMoveName).replace('%2', link.innerHTML), FileTree_SpineMove.bind(null, link, 'moveTocBefore'),
msgs.MoveAfter.replace('%1', window.tmpMoveName).replace('%2', link.innerHTML), FileTree_SpineMove.bind(null, link, 'moveTocAfter'),
msgs.MoveUnder.replace('%1', window.tmpMoveName).replace('%2', link.innerHTML), FileTree_SpineMove.bind(null, link, 'moveTocUnder'),
]);//
else items.merge([
msgs.Move, FileTree_MoveInitiate.bind(null,link),
]);//
//suite
}

function FileTree_CtxMenuItemList_spine (items, link) {
if (window.tmpMoveName) items.merge([
msgs.MoveBefore.replace('%1', window.tmpMoveName).replace('%2', link.innerHTML), FileTree_SpineMove.bind(null, link, 'moveSpineBefore'),
msgs.MoveAfter.replace('%1', window.tmpMoveName).replace('%2', link.innerHTML), FileTree_SpineMove.bind(null, link, 'moveSpineAfter'),
]);//
else items.merge([
msgs.Move, FileTree_MoveInitiate.bind(null,link),
]);//
//suite
}

function FileTree_linkContextMenu (itemListFunc, ul) {
return function(){
var items = [
msgs.Edit, this.href,
msgs.PageOptions, this.href.replace('_editor', '_options'),
];//
itemListFunc(items, this, ul);
items.merge([
msgs.CreateNewPage, this.href.replace('_editor', '_newpage'),
msgs.AddFiles, this.href.replace('_editor', '_addfiles'),
msgs.Cancel, null
]);
Menu_show(this, items);
return false;
}}

function Editor_save () {
var editor = document.querySelector('#editor');
if (!editor) return;
var url = window.actionUrl.replace('@@', 'save');
ajax('POST', url, 'content='+encodeURIComponent(editor.innerHTML), function(e){alert('success: '+e);}, function(){alert('failed');});
};

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
if (!window.XMLHttpRequest && !window.ActiveXObject) return; // No AJAX
if (!document.querySelector || !document.querySelectorAll || !document.getElementById || !document.getElementsByTagName || !document.createElement || !document.createTextNode || !document.createDocumentFragment) return; // Missing one or more mandatory DOM functions
$('.fileTree').each(FileTree_init);
});

//alert('editor loaded');