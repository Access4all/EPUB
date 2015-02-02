function Menu_show (items, originator, x, y) {
originator = originator || document.activeElement;
var ul = document.createElement2('ul', {'class':'contextmenu', role:'menu'});
var firstA = null;
for (var i=0; i<items.length; i+=2) {
var label = items[i], action = items[i+1];
if (typeof(label)!='object') label = {text:label, type:'menuitem'};
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
var a = document.createElement2('a', {'href':'#'}, o.hasClass('collapsed')?'+':'-');
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
if (!f.changed) {
f.$('button[type=submit], input[type=submit], button[type=reset], input[type=reset]').each(function(b){ 
b.disabled=true;
});//each command button
f.$('input, textarea, select').each(function(input){
input.onchange = FormTrackChanges_changed;
input.onkeydown = FormTrackChanges_keydown;
});//each input
}
f.virtualSubmit = FormTrackChanges_virtualSubmit;
$('#topPanel a[href], #leftPanel a[href], #pageTabs a[href]').each(function(a){
if (a.textContent.trim().length<=1) return;
var oldonclick  = a.onclick;
if (a.hasAttribute('data-ajax')) a.onclick = function(e){
if (oldonclick) oldonclick.call(a,e);
return LeftPanelAJAXLoad(this.href);
};
else  a.onclick = function(e){
if (!f.changed) return true;
MessageBox(msgs.Save, msgs.SaveChangesDlg, [msgs.Yes, msgs.No], function(btnIdx){ 
if (btnIdx==0) f.virtualSubmit();
if (oldonclick) oldonclick.call(a,e);
window.location.href = a.href;
});//MessageBox
return false;
};//onclick
});//each link
}

function FormTrackChanges_virtualSubmit () {
if (window.readOnly) return;
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
if (window.readOnly) return;
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

function LeftPanelAJAXLoad (url) {
window.onloads ={push:function(f){ try { f(); } catch(e){alert(e.message);} }}; // Catch functions that normally have to be called when the page loads (window.onload) and call them immediately
ajax('POST', url, '', function(html){
// Take only the part we are interested in, the left panel
var begin = '<div id="leftPanel">', end = '</div><!--leftPanel-->';
begin = html.indexOf(begin) + begin.length;
end = html.indexOf(end);
var html2 = html.substring(begin,end);
var lp = document.getElementById('leftPanel');
lp.innerHTML = html2;
// IE: scripts inclueded within the HTML code don't seem to be properly loaded, so we (re)load them explicitely
{
window.onloads = [];
var count = 0, lh = function(url){ if(--count<0) window.onload(null); debug('loaded '+url); };
html.replace(/<scrip.*src="(.*?)".*script>/g, function(_,src){ count+= include(src, lh)?1:0; debug('Reloading '+src); });
lh();
if (window.history&&history.pushState) history.pushState(url, url, url);
}
debug('AJAX done!'+new Date());
}, function(e){ alert('Failed!10'); });//ajax
return false;
}

window.onpopstate = function (e) {
if (e&&e.state) LeftPanelAJAXLoad(e.state);
}

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
$('.infobox').each(HelpBox_BtnInit);
$('.fileTree').each(FileTree_init);
$('form[data-track-changes]').each(FormTrackChanges_init);
$('*[data-expands]').each(Accordion_init);
});

//alert('editor loaded');