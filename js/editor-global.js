function Menu_show (items, originator, x, y) {
originator = originator || document.activeElement;
var ul = document.createElement2('ul', {'class':'contextmenu', role:'menu'});
var firstA = null;
for (var i=0; i<items.length; i+=2) {
var label = items[i], action = items[i+1];
if (typeof(label)=='string') label = {text:label, type:'menuitem'};
var a = ul.appendElement('li').appendElement('a', {role:label.type}).appendText(label.text);
a.href = (typeof(action)=='string'? action : '#');
a.onkeydown = Menu_keydown.bind(a, ul, originator);
if (typeof(action)=='function') a.onclick = Menu_click(action, originator, ul);
else if (!action) a.onclick = Menu_close.bind(ul, originator);
if (label.checked || label.type=='menuitemcheckbox' || label.type=='menuitemradio') {
a.setAttribute('aria-checked', !!label.checked);
if (label.checked) a.parentNode.insertElementBefore('span', a, {'class':'checkmark', 'aria-hidden':true}, '\u2714');
}
if (!firstA) firstA=a;
}
var body = document.querySelector('body');
body.onclick = function(e){ body.onclick=null; Menu_close.call(ul,originator); return true; }; // When the user clicks outside of the context menu, it should be closed
body.appendChild(ul);
if (x&&y) {
ul.style.position='absolute';
ul.style.left=x+'px';
ul.style.top=y+'px';
}
firstA.focus();
document.getElementById('fullWrapper').setAttribute('aria-hidden', true);
}

function Menu_click (action, originator, ul) {
return function(e){ 
Menu_close.call(ul,originator); 
action.call(this,e); 
return false;
}}

function Menu_close (originator) {
this.parentNode.removeChild(this);
document.getElementById('fullWrapper').removeAttribute('aria-hidden');
if (originator) originator.focus();
return false;
}

function Menu_keydown (ul, originator, e) {
e = e || window.event;
var k = e.keyCode || e.which;
if (e.ctrlKey) k |= vk.ctrl;
if (e.shiftKey) k|=vk.shift;
if (e.altKey) k|=vk.alt;
switch(k){
case vk.up: {
var prevLi = this.parentNode.previousElementSibling;
if (!prevLi) prevLi = this.parentNode.parentNode.lastElementChild;
var prevItem = prevLi && prevLi.querySelector('a');
if (prevItem) prevItem.focus();
}break;
case vk.down: {
var nextLi = this.parentNode.nextElementSibling;
if (!nextLi) nextLi = this.parentNode.parentNode.firstElementChild;
var nextItem = nextLi && nextLi.querySelector('a');
if (nextItem) nextItem.focus();
}break;
case vk.escape:
Menu_close.call(ul, originator);
break;
default: return true;
}
if (e.preventDefault()) e.preventDefault();
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
var exargs = ['min', 'max', 'step', 'pattern', 'maxlength', 'multiple', 'checked', 'disabled', 'readonly'];
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
try {
if (okFunc) re = okFunc.call(this);
} catch(e){ debug(e.message); }
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
overlay.onclick = DialogBox_overlayClick; // When a dialog box is active, nothing should happen if the user clicks outside of the dialog box
var body = document.querySelector('body');
body.appendChild(overlay);
body.appendChild(form);
if (first.focus) first.focus();
if (first.select) first.select();
document.getElementById('fullWrapper').setAttribute('aria-hidden', true);
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
overlay.onclick = DialogBox_overlayClick; // When a dialog box is active, nothing should happen if the user clicks outside of the dialog box
body.appendChild(overlay);
body.appendChild(form);
document.getElementById('fullWrapper').setAttribute('aria-hidden', true);
if (first.select) first.select();
if (first.focus) first.focus();
if (readyFunc) readyFunc.call(form);
}

function DialogBox_overlayClick (e) {
e = e || window.event;
if (e.preventDefault) e.preventDefault();
if (e.stopPropagation) e.stopPropagation();
if (e.stopImmediatePropagation) e.stopImmediatePropagation();
e.returnValue = e.ReturnValue = false;
return false;
}

function FileTree_init (e) {
var ctxType = e.getAttribute('data-ctxtype');
var ctxItemFunc = FileTree_linkContextMenu( window['FileTree_CtxMenuItemList_'+ctxType] ,this);
e.$('a').each(function(o){
o.oncontextmenu = ctxItemFunc;
o.ondragstart = FileTree_link_dragStart;
o.ondragover = FileTree_link_dragOver;
o.ondrop = FileTree_link_drop(ctxType);
o.setAttribute('draggable', true);
o.draggable=true;
});
e.$('ul,ol').each(function(o){
var li = o.parentNode;
var a = document.createElement2('a', {'href':'#'}, '+');
a.onclick = FileTree_expandLinkClick;
a.ondragenter = FileTree_folderLink_dragEnter;
a.ondragleave = FileTree_folderLink_dragLeave;
li.insertBefore(a, li.firstChild);
});
e.$('.directory').each(function(o){
o.ondragenter = FileTree_folderLink_dragEnter;
o.ondragleave = FileTree_folderLink_dragLeave;
});
//suite
}

function FileTree_link_dragStart (e) {
e = e || window.event;
var dt = e.dataTransfer || window.clipboardData;
var relativeUrl = this.getAttribute('data-relative-url');
if (!dt || !relativeUrl) return;
try { dt.setData('Text', "\u007F" + relativeUrl + "\u007F\u007F" + this.href.substring(window.rootUrl.length) ); } catch(ex){}
}

function FileTree_link_dragOver (e) {
if (e&&e.preventDefault) e.preventDefault();
}

function FileTree_link_drop (ctxType) {
var actionName = 'undefinedAction';
if (ctxType=='spine') actionName = 'moveSpineAfter';
else if (ctxType=='file') actionName = 'moveFile';
else if (ctxType=='toc') actionName = 'moveTocAfter';
return function(e){
e = e || window.event;
if (!e||!e.dataTransfer) return;
if (e.preventDefault) e.preventDefault();
if (e.dataTransfer.files && window.RTZ_uploadFiles) RTZ_uploadFiles(e.dataTransfer.files, function(_){ window.location.reload(); }, true);
var data = null;
try { data = e.dataTransfer.getData('Text'); }catch(ex){}
if (data && data.startsWith("\u007F")) {
var src = data.substring(data.indexOf("\u007F\u007F")+2).trim();
var ref = this.href.substring(window.rootUrl.length);
var url = window.rootUrl2 + actionName + '/?src=' + encodeURIComponent(src) + '&ref=' + encodeURIComponent(ref);
debug(url);
ajax('GET', url, null, function(re){if(re=='OK') window.location.reload(); else alert('Return! '+re);}, function(){alert('Failed!2');});
}
//other types of transfers
};}

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
ajax('GET', url, null, function(re){
if(re=='OK') window.location.reload(); 
else debug('Return!'+re.length+'!'+re+'!');
}, function(){alert('Failed!5');});
return false;
}

function FileTree_deleteDialog  (link) {
var src = link.href.substring(window.rootUrl.length);
var url = window.rootUrl2 + 'deleteFile' + '/?file=' + encodeURIComponent(src);
MessageBox(msgs.MBDeleteSpineItemT, msgs.MBDeleteSpineItem.replace('%1', src), [msgs.Yes, msgs.No], function(btnIndex){
if (btnIndex==0) ajax('GET', url, null, function(re){if(re=='OK') window.location.reload(); else alert('Return! '+re);}, function(){alert('Failed!3');});
});//MessageBox
}

function FileTree_folderLink_dragEnter () {
window.folderLinkTimer = setTimeout(FileTree_expandLinkClick.bind(this), 1000);
}

function FileTree_folderLink_dragLeave () {
if (window.folderLinkTimer) clearTimeout(window.folderLinkTimer);
window.folderLinkTimer=null;
}

function FileTree_expandLinkClick () {
var ul = this.parentNode.querySelector('ul,ol');
var collapsed = ul.toggleClass('collapsed');
this.firstChild.nodeValue = (!collapsed? '-' : '+');
}

function FileTree_CtxMenuItemList_file (items, link) {
items.merge([
msgs.Rename, FileTree_RenameDialog.bind(null, link),
msgs.Delete, FileTree_deleteDialog.bind(null,link),
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
items.merge([
msgs.Delete, FileTree_deleteDialog.bind(null,link), 
]);//
//suite
}

function FileTree_linkContextMenu (itemListFunc, ul) {
return function(e){
e = e || window.event;
var x = e.pageX || e.clientX;
var y = e.pageY || e.clientY;
var items = [];
if (/\.(?:xhtml|xml|txt|js|css)$/i .test(this.href)) items.merge([msgs.Edit, this.href]);
if (/\.xhtml$/i .test(this.href)) items.merge([msgs.PageOptions, this.href.replace('_editor', '_options')]);
if (this.hasAttribute('data-relative-url')) {
var rel = this.getAttribute('data-relative-url');
if (rel && rel!='') items.merge([msgs.CopyRelUrl, FileTree_linkCopyRelUrl.bind(this)]);
}
itemListFunc(items, this, ul);
items.merge([
msgs.CreateNewPage, this.href.replace('_editor', '_newpage'),
msgs.AddFiles, this.href.replace('_editor', '_addfiles'),
msgs.Cancel, null
]);
Menu_show(items, this, x+7, y+7);
return false;
}}

function FileTree_RenameDialog (link) {
var simpleName = link.href.substring(link.href.lastIndexOf('/')+1);
DialogBox(msgs.RenameFile, [
{label:msgs.RenameTo, name:'newName', value:simpleName}
], function(){var src  = link.href.substring(window.rootUrl.length);
var ref = this.elements.newName.value;
var url = window.rootUrl2 + 'renameFile' + '/?src=' + encodeURIComponent(src) + '&ref=' + encodeURIComponent(ref);
ajax('GET', url, null, function(re){if(re=='OK') window.location.reload(); else alert('Return! '+re);}, function(st,tx){alert('Failed!4!'+st+tx+tx.responseText);});
});//DialogBox
}

function FileTree_linkCopyRelUrl () {
var rel = this.getAttribute('data-relative-url');
var data = "\u007F" + rel + "\u007F\u007F" + this.href.substring(window.rootUrl.length);
if (window.clipboardData) {
try {
if (!window.clipboardData.setData('Text', data)) throw new Error('failed');
return;
} catch(ex) {}
}
if (window.prompt) window.prompt(msgs.CopyRelUrl, data);
else alert('No copy method');
}

function FormTrackChanges_init (f) {
f.changed = false;
f.$('button[type=submit], input[type=submit], button[type=reset], input[type=reset]').each(function(b){ 
b.disabled=true;
});//each command button
f.$('input, textarea, select').each(function(input){
input.onchange = FormTrackChanges_changed;
input.onkeydown = FormTrackChanges_keydown;
});//each input
f.virtualSubmit = FormTrackChanges_virtualSubmit;
if (!window.formTrackChange1st) {
window.formTrackChange1st=true;
if (window.location.host!='localhost') 
$('a[href]').each(function(a){
var oldonclick  = a.onclick;
a.onclick = function(e){
if (!f.changed) return true;
MessageBox(msgs.Save, msgs.SaveChangesDlg, [msgs.Yes, msgs.No], function(btnIdx){ 
if (btnIdx==0) f.virtualSubmit();
if (oldonclick) oldonclick.call(a,e);
window.location.href = a.href;
});//MessageBox
return false;
};//onclick
});//each link
}//if formTrackChange1st
}

function FormTrackChanges_virtualSubmit () {
if (this.onsubmit && !this.onsubmit()) return;
var params = ['ajax=1'];
this.$('input, textarea, select').each(function(input){
params.push(input.name + '=' + encodeURIComponent(input.value) );
});//each input
params = params.join('&');
var url = this.actionn || window.location.href;
if (this.method.toUpperCase()=='GET') { url += '?' + params; params=null; }
var form = this;
ajax(this.method, url, params, function(re){if(re=='OK') FormTrackChanges_init(form); else alert('Return! '+re);}, function(){alert('Failed!6');});
}

function FormTrackChanges_changed () {
this.onchange=null;
this.form.changed = true;
this.form.$('button[type=submit], input[type=submit], button[type=reset], input[type=reset]').each(function(b){ 
b.disabled=false;
});//each command button
}

function FormTrackChanges_keydown (e) {
e = e || window.event;
var k = e.keyCode || e.which;
if (k==vk.backspace || k==vk.enter || (k>=vk.n0&&k<=vk.n9) || k>=vk.a) { if (this.onchange) this.onchange(); }
if (e.ctrlKey && k==vk.s) {
this.form.virtualSubmit();
if (e.preventDefault) e.preventDefault();
return false;
}
return true;
}

function Accordion_init (toggleContainer) {
var sel = document.createRange();
var regionId = toggleContainer.getAttribute('data-expands');
var region = document.getElementById(regionId);
var expanded = !region||!region.hasClass('collapsed');
sel.selectNodeContents(toggleContainer);
var toggle = document.createElement2('a', {href:'#', 'aria-expanded':expanded, role:'button'});
sel.surroundContents(toggle);
toggle.onclick = Accordion_click.bind(toggle, regionId);
}

function Accordion_click (regionId) {
var region = document.getElementById(regionId);
if (!region) return false;
var collapsed = region.toggleClass('collapsed');
this.setAttribute('aria-expanded', !collapsed);
return false;
}

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
$('.fileTree').each(FileTree_init);
$('form[data-track-changes]').each(FormTrackChanges_init);
$('*[data-expands]').each(Accordion_init);
});

//alert('editor loaded');