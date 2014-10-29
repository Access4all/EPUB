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

function DialogBox (title, desc, okFunc, cancelFunc, readyFunc) {
var lastFocus = document.activeElement;
var first=null, overlay = document.createElement2('div', {'class':'overlay'}), form = document.createElement2('form', {'role':'dialog', 'class':'dialogBox'});
form.appendElement('h1').appendText(title);
for (var i=0; i<desc.length; i++) {
var item = desc[i];
var p = form.appendElement('p');
p.appendElement('label', {'for':item.name, 'id':item.name+'Label'}).appendText(item.label+':');
var type = item.type || 'text';
var value = item.value || '';
var input = null;
if (type=='select') {
input = p.appendElement('select', {'id':item.name, 'name':item.name, 'aria-labelledby':item.name+'Label'});
for (val in item.values) input.appendElement('option', {'value':val}).appendText(item.values[val]);
if (item.value) input.value = item.value;
}
else {
var exargs = ['min', 'max', 'step', 'pattern', 'maxlength'];
var args = {'type':type, 'value':value, 'id':item.name, 'name':item.name, 'aria-labelledby':item.name+'Label'};
for (var j=0; j<exargs.length; j++) if (item[exargs[j]]) args[exargs[j]]=item[exargs[j]];
input = p.appendElement('input', args);
}
if (!first) first=input;
}
var p = form.appendElement('p');
var btnOk = p.appendElement('button', {'type':'submit'}).appendText(msgs.OK);
var btnCancel = p.appendElement('button', {'type':'reset'}).appendText(msgs.Cancel);
form.onsubmit = function(){ 
var re;
if (okFunc) re = okFunc.call(this);
if (re!==false) {
this.parentNode.removeChild(this); 
overlay.parentNode.removeChild(overlay);
document.getElementById('fullWrapper').removeAttribute('aria-hidden');
if (lastFocus && lastFocus.focus) lastFocus.focus();
}
return false;
};
form.onreset = function(){ 
var re;
if (cancelFunc) re = cancelFunc.call(this);
if (re!==false) {
this.parentNode.removeChild(this); 
overlay.parentNode.removeChild(overlay);
document.getElementById('fullWrapper').removeAttribute('aria-hidden');
if (lastFocus && lastFocus.focus) lastFocus.focus();
}
return false; 
};
var body = document.querySelector('body');
body.appendChild(overlay);
body.appendChild(form);
document.getElementById('fullWrapper').setAttribute('aria-hidden', true);
if (first.select) first.select();
if (first.focus) first.focus();
if (readyFunc) readyFunc.call(form);
}

function MessageBox (title, msg, btns, okFunc, readyFunc) {
var lastFocus = document.activeElement;
var first=null, overlay = document.createElement2('div', {'class':'overlay'}), form = document.createElement2('div', {'role':'alertdialog', 'class':'dialogBox'});
form.appendElement('h1').appendText(title);
var p = form.appendElement('p', {id:'MessageBoxLabel', 'aria-live':'assertive'});
p.innerHTML = msg;
p = form.appendElement('p');
var btnClick = function(btnIndex, btnLabel){ 
return function(){
var re;
if (okFunc) re = okFunc.call(this, btnIndex, btnLabel);
if (re!==false) {
this.parentNode.removeChild(this); 
overlay.parentNode.removeChild(overlay);
document.getElementById('fullWrapper').removeAttribute('aria-hidden');
if (lastFocus && lastFocus.focus) lastFocus.focus();
} }; };
for (var i=0; i<btns.length; i++) {
var btn= p.appendElement('button', {'type':'button'});
btn.innerHTML = btns[i];
btn.onclick = btnClick(i, btns[i]).bind(form);
btn.setAttribute('aria-describedby', 'MessageBoxLabel');
btn.setAttribute('aria-label', btns[i].stripHTML() );
if (!first) first=btn;
}
var body = document.querySelector('body');
body.appendChild(overlay);
body.appendChild(form);
document.getElementById('fullWrapper').setAttribute('aria-hidden', true);
if (first.select) first.select();
if (first.focus) first.focus();
if (readyFunc) readyFunc.call(form);
}

function FileTree_init (e) {
var ctxItemFunc = FileTree_linkContextMenu( window['FileTree_CtxMenuItemList_'+e.getAttribute('data-ctxtype')] ,this);
e.$('a').each(function(o){
o.oncontextmenu = ctxItemFunc;
});
e.$('ul,ol').each(function(o){
var li = o.parentNode;
var a = li.appendElement('a', {'href':'#'});
a.appendText('+');
a.onclick = FileTree_expandLinkClick;
li.insertBefore(a, li.firstChild);
o.style.display='none';
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
msgs.Rename, FileTree_RenameDialog.bind(null, link),
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

function FileTree_RenameDialog (link) {
var simpleName = link.href.substring(link.href.lastIndexOf('/')+1);
DialogBox(msgs.RenameFile, [
{label:msgs.RenameTo, name:'newName', value:simpleName}
], function(){var src  = link.href.substring(window.rootUrl.length);
var ref = this.elements.newName.value;
var url = window.rootUrl2 + 'renameFile' + '/?src=' + encodeURIComponent(src) + '&ref=' + encodeURIComponent(ref);
ajax('GET', url, null, function(re){if(re=='OK') window.location.reload(); else alert('Return! '+re);}, function(){alert('Failed!');});
});//DialogBox
}

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
if (!window.XMLHttpRequest && !window.ActiveXObject) return; // No AJAX
if (!document.querySelector || !document.querySelectorAll || !document.getElementById || !document.getElementsByTagName || !document.createElement || !document.createTextNode || !document.createDocumentFragment) return; // Missing one or more mandatory DOM functions
$('.fileTree').each(FileTree_init);
});

//alert('editor loaded');