Node.prototype.getFirstTextNode =  function () {
if (this.nodeType==3) return this;
var re = null;
for (var i=0; re==null && i<this.childNodes.length; i++) re = this.childNodes[i].getFirstTextNode();
return re;
}

Node.prototype.getLastTextNode =  function () {
if (this.nodeType==3) return this;
var re = null;
for (var i=this.childNodes.length -1; re==null && i>=0; i--) re = this.childNodes[i].getLastTextNode();
return re;
}

Node.prototype.eachTextNode = function(f){
if (this.nodeType==3) f(this);
if (this.childNodes) for (var i=this.childNodes.length -1; i>=0; i--) this.childNodes[i].eachTextNode(f);
}

function RTZ (zone, toolbar) {
this.zone = zone;
this.toolbar = toolbar;
this.select = RTZ_select;
this.getSelection = RTZ_getSelection;
this.simpleBlockFormat = RTZ_simpleBlockFormat;
this.inlineFormat = RTZ_inlineFormat;
this.superBlockFormat = RTZ_superBlockFormat;
this.formatAsList = RTZ_formatAsList;
this.formatAsLink = RTZ_formatAsLink;
this.formatAsCodeListing = RTZ_formatAsCodeListing;
this.removeFormatting = RTZ_removeFormatting;
this.insertElement = RTZ_insertElement;
this.insertIcon = RTZ_insertIcon;
this.insertIllustration = RTZ_insertIllustration;
this.insertTable = RTZ_insertTable;
this.insertBox = RTZ_insertBox;
this.insertIconDialog = RTZ_insertIconDialog;
this.insertIllustrationDialog = RTZ_insertIllustrationDialog;
this.insertTableDialog = RTZ_insertTableDialog;
this.insertAbbrDialog = RTZ_insertAbbrDialog;
this.insertBoxDialog = RTZ_insertBoxDialog;
this.quickUploadDialog = RTZ_quickUploadDialog;
this.openPreview = RTZ_openPreview;
this.enterKey = RTZ_enterKey;
this.enterKeyOnEmptyParagraph = RTZ_enterKeyOnEmptyParagraph;
this.enterKeyOnNonEmptyParagraph = RTZ_enterKeyOnNonEmptyParagraph;
this.enterKeyAtBeginningOfParagraph = RTZ_enterKeyAtBeginningOfParagraph;
this.tabKey = RTZ_tabKey;
this.shiftTabKey = RTZ_shiftTabKey;
this.cleanHTML = RTZ_cleanHTML;
this.cleanHTMLElement = RTZ_cleanHTMLElement;
this.init = RTZ_init;
this.onselchanged = RTZ_onSelChanged;
this.loadStyles = RTZ_loadStyles;
this.toString = RTZ_toString;
this.implKeyDown = RTZ_implKeyDown;
keys = {
goToHome: vk.ctrl+vk.home,
goToEnd: vk.ctrl+vk.end,
selectToHome: vk.ctrl+vk.shift+vk.home,
selectToEnd: vk.ctrl+vk.shift+vk.end,
copy: vk.ctrl+vk.c,
paste: vk.ctrl+vk.v,
cut: vk.ctrl+vk.x,
save: vk.ctrl+vk.s,
preview: vk.impossible +1,
link: vk.ctrl+vk.k,
bold: vk.ctrl+vk.b,
italic: vk.ctrl+vk.i,
strikeout: vk.ctrl+vk.shift+vk.k,
abbreviation: vk.ctrl+vk.shift+vk.b,
regular: vk.ctrl+vk.n0,
h1: vk.ctrl+vk.n1,
h2: vk.ctrl+vk.n2,
h3: vk.ctrl+vk.n3,
h4: vk.ctrl+vk.n4,
h5: vk.ctrl+vk.n5,
h6: vk.ctrl+vk.n6,
h1alt: vk.alt+vk.n1,
h2alt: vk.alt+vk.n2,
h3alt: vk.alt+vk.n3,
h4alt: vk.alt+vk.n4,
h5alt: vk.alt+vk.n5,
h6alt: vk.alt+vk.n6,
orderedList: vk.ctrl+vk.l,
unorderedList: vk.ctrl+vk.u,
definitionList: vk.ctrl+vk.shift+vk.d,
codeListing: vk.ctrl+vk.shift+vk.p,
blockquote: vk.ctrl+vk.q,
insertBox: vk.ctrl+vk.shift+vk.a,
insertIcon: vk.ctrl+vk.shift+vk.i,
insertIllustration: vk.ctrl+vk.shift+vk.g,
insertTable: vk.ctrl+vk.shift+vk.t,
quickUpload: vk.ctrl+vk.shift+vk.u,
cleanHTML: vk.f9,
};
}

function RTZ_init () {
var _this = this;
this.inlineOnly = ['div', 'section', 'aside'].indexOf(this.zone.tagName.toLowerCase())<0;
this.zone.onkeydown = RTZ_keyDown.bind(this);
this.zone.onpaste = RTZ_paste.bind(this);
if (this.toolbar) {
setInterval(RTZ_selectionTimer.bind(this), 100);
this.toolbar.$('button').each(function(o){ 
var action = o.getAttribute('data-action');
o.onclick = RTZ_toolbarButtonClick.bind(_this, action); 
var img = o.querySelector('img'), alt = img.getAttribute('alt');
if (keys[action] && keys[action]<vk.impossible) alt += '\t(' + RTZ_keyCodeToString(keys[action]) + ')';
o.setAttribute('title', alt);
o.setAttribute('aria-label', alt);
img.setAttribute('title', alt);
img.setAttribute('alt', alt);
});
this.toolbar.$('select').each(function(o){ 
o.onchange = RTZ_toolbarStyleSelect.bind(_this, o); 
o.$('option').each(function (opt){
var action = opt.getAttribute('value');
var text = opt.firstChild;
if (keys[action] && keys[action]<vk.impossible) text.appendData('\t(' + RTZ_keyCodeToString(keys[action]) + ')');
});
});
}
this.loadStyles();
if (this.debug){
var div = document.createElement('div');
var div2 = document.getElementById('debugdiv2');
if (!div2) {
div2 = document.createElement2('div', {id:'debugdiv2'});
div2.appendElement('h1').appendText('Debug messages');
//document.body.appendChild(div2);
}
div.appendElement('h1').appendText('Debug HTML code');
this.htmlCodePreview = div.appendElement('pre');
this.zone.onkeyup = RTZ_debug_updateHTMLPreview.bind(this);
this.zone.parentNode.insertBefore(div, this.zone.nextSibling);
this.zone.onkeyup(null);
this.debug = function(str){ div2.insertAdjacentHTML('beforeEnd', str+'<br />'); };
}
else this.debug = function(str) {};
this.cleanHTML();
if (window.onRTZCreate) window.onRTZCreate(this);
}

function RTZ_loadStyles () {
if (this.inlineOnly) return;
this.styles = {};
this.positionalStyles = {};
this.boxTypes = {};
if (document.styleSheets) for (var j=0; j<document.styleSheets.length; j++) {
var stylesheet = document.styleSheets[j];
var rules = null;
try { rules = stylesheet.cssRules || stylesheet.rules; } catch(e){} // Against firefox security error
if (!rules) continue;
for (var i=0; i<rules.length; i++) {
var rule = rules[i];
if (!/^\.editor /.test(rule.selectorText)) continue;
var m, selector = rule.selectorText.substring(8).trim();
if (/^\.\w+$/.test(selector)) {
var x = selector.substring(1).trim();
this.positionalStyles[x]=x;
}
else if (m=selector.match(/^(\w+)\.(\w+)$/)) {
var ar = this.styles[m[1]];
if (!ar) { ar = []; this.styles[m[1]]=ar; }
ar.push(m[2]);
}}}
var tags = ['aside', 'section'];
for (var j=0; j<tags.length; j++) {
if (this.styles[tags[j]]) for (var i=0; i<this.styles[tags[j]].length; i++) {
var name = tags[j]+'.'+this.styles[tags[j]][i];
var label = this.styles[tags[j]][i];
this.boxTypes[name]=label;
}}
}

function RTZ_toolbarStyleSelect (select) {
RTZ_toolbarButtonClick.call(this, select.value);
select.selectedIndex=0;
}

function RTZ_toolbarButtonClick (action) {
this.zone.focus();
this.implKeyDown(keys[action], true);
}

function RTZ_keyDown (e) {
e = e || window.event;
var k = e.keyCode || e.which;
if (k>=20 || k==vk.tab) {
if (e.ctrlKey) k |= vk.ctrl;
if (e.shiftKey) k|=vk.shift;
if (e.altKey) k|=vk.alt;
}
var re = this.implKeyDown(k, false);
if (!re){
if (e.preventDefault) e.preventDefault();
if (e.stopPropagation) e.stopPropagation();
}
return re;
}

function RTZ_onSelChanged (sel) {
var structCb = document.getElementById('structureDropDown');
var listCb = document.getElementById('listsDropDown');
var el = sel.commonAncestorContainer;
if (!el || !listCb || !structCb) return;
if (el.nodeType==3) el = el.parentNode;
var structuralAncestor = el.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre']);
var listAncestor = el.findAncestor(['ul', 'ol', 'dl']);
if (structuralAncestor) {
var tgn = structuralAncestor.tagName.toLowerCase();
switch(tgn) {
case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
structCb.value = tgn;
break;
case 'pre': structCb.value = 'codeListing'; break;
default: structCb.value = 'regular'; break;
}}
else structCb.value = 'regular';
if (listAncestor) {
var tgn = listAncestor.tagName.toLowerCase();
if (tgn=='ul') listCb.value = 'unorderedList';
else if (tgn=='dl') listCb.value = 'definitionList';
else if (tgn=='ol') listCb.value = 'orderedList';
else listCb.value = 'regular';
var div = document.getElementById('debug3');
if (!div) { div=document.querySelector('body').appendElement('div', {id:'debug3'}); }
}
else listCb.value = 'regular';
}

function RTZ_implKeyDown (k, simulated) {
if (this.onkeydown) {
var result = this.onkeydown(k,simulated);
if (result===true || result===false) return result;
}
switch(k){
case vk.enter:
//try {
this.enterKey();
//} catch(e){ alert(e.message); }
break;
case vk.tab :
if (this.tabKey()) return true;
else break;
case vk.shift+vk.tab:
if (this.shiftTabKey()) return true;
else break;
case keys.regular:
this.removeFormatting();
break;
case keys.h1alt:
case keys.h2alt:
case keys.h3alt:
case keys.h4alt:
case keys.h5alt:
case keys.h6alt:
k = k + keys.h1 - keys.h1alt;
case keys.h1:
case keys.h2:
case keys.h3:
case keys.h4:
case keys.h5:
case keys.h6: 
k += 1 - keys.h1;
this.simpleBlockFormat('h'+k, {'role':'heading', 'aria-level':k});
break;
case keys.link:
this.formatAsLink();
break;
case keys.bold:
this.inlineFormat('strong', false);
break;
case keys.italic:
this.inlineFormat('em', false);
break;
case keys.strikeout:
this.inlineFormat('s', false);
break;
case keys.abbreviation:
this.insertAbbrDialog();
break;
case keys.unorderedList:
this.formatAsList('ul', 'li', 'li');
break;
case keys.orderedList:
this.formatAsList('ol', 'li', 'li');
break;
case keys.definitionList:
this.formatAsList('dl', 'dd', 'dt');
break;
case keys.codeListing:
this.formatAsCodeListing();
break;
case keys.blockquote:
this.superBlockFormat('blockquote');
break;
case keys.insertBox:
this.insertBoxDialog();
break;
case keys.insertIcon:
this.insertIconDialog();
break;
case keys.insertIllustration :
this.insertIllustrationDialog();
break;
case keys.insertTable :
this.insertTableDialog();
break;
case keys.copy:
if (!simulated) return true;
document.execCommand('copy', false, null);
break;
case keys.cut:
if (!simulated) return true;
document.execCommand('cut', false, null);
break;
case keys.paste: 
if (!simulated) return true;
document.execCommand('paste', false, null);
break;
case keys.save :
this.cleanHTML();
if (this.onsave) this.onsave();
break;
case keys.preview:
this.openPreview();
break;
case keys.quickUpload:
this.quickUploadDialog();
break;
case keys.cleanHTML:
this.cleanHTML();
break;
case keys.goToHome: { // Some browsers don't support Ctrl+Home to go to the beginning of the document
var sel = this.getSelection();
sel.selectNode( this.zone.getFirstTextNode() );
sel.collapse(true);
this.select(sel);
}break;
case keys.goToEnd: { // Some browsers don't support Ctrl+End to go to the end of the document
var sel = this.getSelection();
sel.selectNode( this.zone.getLastTextNode() );
sel.collapse(false);
this.select(sel);
}break;
case keys.selectToHome: { // Similarly, some browsers don't support the select to home command, Ctrl+Shift+Home
var sel = this.getSelection();
sel.collapse(true);
sel.setStartBefore(this.zone.firstChild);
this.select(sel);
}break;
case keys.selectToEnd: { // Similarly, some browsers don't support the select to end command, Ctrl+Shift+End
var sel = this.getSelection();
sel.collapse(true);
sel.setEndAfter(this.zone.lastChild);
this.select(sel);
}break;
default: return true;
}
return false;
}

function RTZ_enterKey () {
if (this.onenter) {
var re = this.onenter();
if (re===true || re===false) return re;
}
if (this.inlineOnly) return false;
var followFrag=null, sel = this.getSelection();
var el = sel.commonAncestorContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'li', 'dt', 'dd', 'th', 'td', 'caption']);
if (!el) {
el = document.createElement('p');
sel.selectNodeContents(sel.commonAncestorContainer);
sel.surroundContents(el);
sel.selectNodeContents(el);
sel.collapse(false);
}
else {
var tagName = el.tagName.toLowerCase();
if (['td', 'th'] .indexOf(tagName) >=0) { this.tabKey(); return false; }
else if (tagName=='caption') return false;
else if (tagName=='pre') return this.insertElement('br');
}
if (!sel.collapsed) {
sel.deleteContents();
sel.collapse(false);
}
if (el.lastChild) {
sel.setStart(sel.startContainer, sel.startOffset);
sel.setEndAfter(el.lastChild);
followFrag = sel.extractContents();
}
el.normalize();
el.normalize2();
if (!el.hasChildNodes()) {
if (followFrag) this.cleanHTMLElement(sel, followFrag, true); // Required for firefox and chrome, which add useless <br> and/or empty text nodes all the time
if (followFrag&&followFrag.childNodes.length>0) this.enterKeyAtBeginningOfParagraph(sel, el, followFrag);
else this.enterKeyOnEmptyParagraph(sel, el, followFrag);
}
else this.enterKeyOnNonEmptyParagraph(sel, el, followFrag);
return false;
}

function RTZ_listDetectType (s) {
if (/^\d+$/.test(s)) return '1';
else if (s=='i' || s=='I' || s.length>1) return s.charAt(0)>'Z'?'i':'I';
else return s.charAt(0)>'Z'?'a':'A';
}

function RTZ_listDetectStart (s, type) {
if (type=='1') return s;
else if (type=='A' || type=='a') return 1 + s.charCodeAt(0) - type.charCodeAt(0);
var romanNumbers = [null, 'i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii', 'xiii', 'xiv', 'xv', 'xvi', 'xvii', 'xviii', 'xix', 'xx'];
return romanNumbers.indexOf(s.toLowerCase());
}

function RTZ_enterKeyOnNonEmptyParagraph (sel, el, followFrag) {
var match, ok, firstTextNode, tgn=el.tagName.toLowerCase();
ok = tgn!='caption' && tgn!='td' && tgn!='th';
firstTextNode = (followFrag&&followFrag.firstChild? followFrag.getFirstTextNode() : null);
if (ok && (match = el.textContent.match(/^(\d+|[a-zA-Z]{1,4})[.)]\s/))) {
sel.selectNodeContents(el);
var text = sel.extractContents();
text.firstChild.nodeValue = text.firstChild.nodeValue.toString().substring(match[0].length);
var ol = document.createElement('ol');
var li = ol.appendElement('li');
li.appendChild(text);
var type = RTZ_listDetectType(match[1]);
var start = RTZ_listDetectStart(match[1], type);
ol.setAttribute('start', start);
ol.setAttribute('type', type);
li = ol.appendElement('li');
if (followFrag) li.appendChild(followFrag);
if (el.nodeName.toLowerCase()=='li') el.appendChild(ol);
else {
el.parentNode.insertBefore(ol, el);
el.parentNode.removeChild(el);
}
sel.selectNodeContents(li);
sel.collapse(true);
this.select(sel);
return;
}
else if (ok && (match = /^[-*+]\s/ .test(el.textContent))) {
sel.selectNodeContents(el);
var text = sel.extractContents();
text.firstChild.nodeValue = text.firstChild.nodeValue.toString().substring(2);
var ul = document.createElement('ul');
var li = ul.appendElement('li');
li.appendChild(text);
li = ul.appendElement('li');
if (followFrag) li.appendChild(followFrag);
if (el.nodeName.toLowerCase()=='li') el.appendChild(ul);
else {
el.parentNode.insertBefore(ul, el);
el.parentNode.removeChild(el);
}
sel.selectNodeContents(li);
sel.collapse(true);
this.select(sel);
return;
}
if (tgn=='dd') tgn='dt';
else if (tgn=='dt') tgn='dd';
else if (/^h[1-6]$/ .test(tgn)) tgn='p';
var newEl = document.createElement(tgn);
el.parentNode.insertBefore(newEl, el.nextElementSibling);
if (followFrag) newEl.appendChild(followFrag);
if (firstTextNode&&firstTextNode.length==0) firstTextNode.appendData('\u00A0'); // Chrome: we need to avoid 0-length text nodes, otherwise the text node can no longer be reached with the cursor, and it is incorrectly positionned on the next paragraph
sel.selectNodeContents(firstTextNode? firstTextNode : newEl);
sel.collapse(true);
this.select(sel);
return false;
}

function RTZ_enterKeyOnEmptyParagraph (sel, el, followFrag) {
var parent = el.parentNode;
var tgn = 'p';
var firstTextNode = (followFrag&&followFrag.firstChild? followFrag.getFirstTextNode() : null);
if (parent.parentNode && parent.parentNode.tagName.toLowerCase()=='li') tgn='li';
else if (parent.parentNode && parent.parentNode.tagName.toLowerCase()=='dd') tgn='dt';
var newEl = document.createElement(tgn);
if (followFrag) newEl.appendChild(followFrag);
newEl.appendText('\u00A0'); // Chrome: IF we don't add this unbreakable space, the cursor is always incorrectly positionned at beginning of next paragraph
if (parent==this.zone) parent.replaceChild(newEl, el);
else {
parent.removeChild(el);
var parentTgn = (parent.parentNode? parent.parentNode.tagName.toLowerCase() : '#none');
if (parentTgn=='li' || parentTgn=='dd') parent = parent.parentNode;
parent.parentNode.insertBefore(newEl, parent.nextSibling);
}
sel.selectNodeContents(firstTextNode? firstTextNode : newEl);
sel.collapse(true);
this.select(sel);
return false;
}

function RTZ_enterKeyAtBeginningOfParagraph (sel, el, followFrag) {
var newEl = document.createElement(el.tagName);
newEl.appendText('\u00A0'); // firefox: IF we don't add this unbreakable space, the paragraph is unreachable with the cursor
var firstTextNode = followFrag&&followFrag.firstChild? followFrag.getFirstTextNode() : null;
el.parentNode.insertBefore(newEl, el);
if (followFrag) el.appendChild(followFrag);
sel.selectNodeContents(firstTextNode? firstTextNode : el);
sel.collapse(true);
this.select(sel);
return false;
}

function RTZ_tabKey () {
if (this.ontab) {
var result = this.ontab();
if (result===false || result===true) return result;
}
var sel = this.getSelection();
var cell = sel.commonAncestorContainer.findAncestor(['th', 'td']);
if (!cell || !cell.isInside(this.zone)) return true;
var nextCell = cell.nextElementSibling;
if (!nextCell) {
var tr = cell.parentNode;
var nextTr = tr.nextElementSibling;
if (!nextTr || !nextTr.tagName || nextTr.tagName.toLowerCase()!='tr') nextTr=null;
if (nextTr) nextCell = nextTr.firstElementChild;
else {
nextTr = tr.cloneNode(true);
nextTr.$('td,th').each(function(o){ o.innerHTML='\u00A0'; }); // Chrome: if the celle is left completely empty, the cursor isn't positionned into it
tr.parentNode.insertBefore(nextTr, tr.nextSibling);
nextCell = nextTr.firstElementChild;
}}
sel.selectNodeContents(nextCell);
sel.collapse(true);
this.select(sel);
return false;
}

function RTZ_shiftTabKey () {
var sel = this.getSelection();
var cell = sel.commonAncestorContainer.findAncestor(['th', 'td']);
if (!cell || !cell.isInside(this.zone)) return true;
var prevCell = cell.previousElementSibling;
if (!prevCell) {
var tr = cell.parentNode;
var prevTr = tr.previousElementSibling;
if (prevTr) prevCell = prevTr.lastElementChild;
}
if (!prevCell) return true;
sel.selectNodeContents(prevCell);
sel.collapse(true);
this.select(sel);
return false;
}

function RTZ_inlineFormat (tagName, allowNest, attrs, justCheck) {
var sel = this.getSelection();
var node = sel.commonAncestorContainer;
var same = null;
if (!allowNest) { // Nesting not allowed; let's look if we are already within a tag of the same type
while(node!=null && node!=this) {
if (node.nodeName.toUpperCase() == tagName.toUpperCase() ) { same=node; break; }
node = node.parentNode;
}}

if (same) { // We are already within a tag of the same type
if (justCheck) return false;
if (sel.collapsed && sel.commonAncestorContainer.nodeType==3 && sel.endOffset==sel.commonAncestorContainer.nodeValue.length) { // Are we exactly at the end of the element ? In this case we would very probably like to continue writing without the bold/italic/etc attribute, not remove it
sel.setEndAfter(same);
sel.collapse(false);
this.select(sel);
} else { // In other case, assume that we want to remove the bold/italic/etc attribute completely
sel.selectNodeContents(same);
var extracted = sel.extractContents	();
same.parentNode.replaceChild(extracted, same);
}
return;
}

// Common case: surround the selection if possible
if (justCheck) return true;
node = document.createElement(tagName);
if (attrs) for (var i in attrs) node.setAttribute(i, attrs[i]);
try {
sel.surroundContents(node);
} catch(e) { alert('Inline formatting failed'); return; }
if (!allowNest) node.$(tagName).each(function(o){ sel.selectNodeContents(o);  var ex = sel.extractContents();  o.parentNode.replaceChild(ex, o);  }); // If needed, let's clean duplicate tags, i.e. <b><b></b></b>, before forming the final new selection; this can happen for example when requesting <b> for a selection like a[b<b>c</b>d<b>e</b>f]g
if (!node.hasChildNodes()) node.appendText('\u00A0'); // Chrome: if the node is empty, the cursor is incorrectly placed after the node instead of inside it.
sel.selectNodeContents(node);
this.select(sel);
}

function RTZ_formatAsLink () {
if (!this.inlineFormat('a', false, null, true)) {
this.inlineFormat('a', false);
return;
}
var sel = this.getSelection();
var _this = this;
DialogBox(msgs.MakeALink, [{label:msgs.LinkURL, name:'url'}], function(){
var text = this.elements.url.value;
_this.select(sel);
_this.inlineFormat('a', false, {'href':text});
_this.zone.focus();
}, null); //DialogBox
}

function RTZ_formatAsCodeListing () {
if (this.inlineOnly) return false;
var sel = this.getSelection();
var wasCollapsed = sel.collapsed;
var startNode = sel.startContainer.findAncestor(['p']);
var endNode = sel.endContainer.findAncestor(['p']);
if (!startNode || !endNode) return;
sel.setStartBefore(startNode);
sel.setEndAfter(endNode);
if (startNode.tagName!=endNode.tagName) return;
var extracted = sel.extractContents();
var pre = document.createElement2('pre');
var code = pre.appendElement('code');
var sel2 = document.createRange();
for (var i=0; i<extracted.childNodes.length; i++) {
if (i>0) code.appendElement('br');
var p = extracted.childNodes[i];
sel2.selectNodeContents(p);
p = sel2.extractContents();
code.appendChild(p);
}
sel.insertNode(pre);
}

function RTZ_simpleBlockFormat (tagName, attrs) {
if (this.inlineOnly) return false;
var sel = this.getSelection();
var collapsed = sel.collapsed;
var node = sel.commonAncestorContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
if (!node && sel.commonAncestorContainer && sel.commonAncestorContainer.parentNode==this.zone) {
node = document.createElement('p');
sel.selectNodeContents(sel.commonAncestorContainer);
sel.surroundContents(node);
sel.selectNodeContents(node);
}
if (!node) return;
if (!node.isInside(this.zone)) return;
sel.selectNodeContents(node);
var extracted = sel.extractContents();
var newNode = document.createElement(tagName);
if (attrs) for (var i in attrs) newNode.setAttribute(i, attrs[i]);
newNode.appendChild(extracted);
node.parentNode.replaceChild(newNode, node);
sel.selectNodeContents(newNode);
if (collapsed) sel.collapse(false);
this.select(sel);
}

function RTZ_formatAsList (listType, oddItemType, evenItemType) {
if (this.inlineOnly) return false;
var sel = this.getSelection();
var wasCollapsed = sel.collapsed;
var startNode = sel.startContainer.findAncestor(['p', 'li']);
if (!startNode) return false;
var endNode = sel.endContainer.findAncestor(['p', 'li']);
sel.setStartBefore(startNode);
sel.setEndAfter(endNode);
if (startNode.tagName!=endNode.tagName) return;
if (startNode.tagName.toLowerCase()=='li') {
var node = document.createElement(listType);
var li = document.createElement(evenItemType);
sel.surroundContents(node);
sel.selectNode(node);
sel.surroundContents(li); 
if (!wasCollapsed) sel.selectNodeContents(node);
else {
var lis = node.$('li');
var last = lis[lis.length -1];
sel.selectNodeContents(last);
sel.collapse(false);
}
this.select(sel);
return;
}
else if (startNode.tagName.toLowerCase()=='p') {
var newNodes = [];
var first=true;
var li, cur, count=0, next = startNode;
while(cur!=endNode || first){
cur = next;
next = cur.nextElementSibling;
sel.selectNodeContents(cur);
var extracted = sel.extractContents();
li = document.createElement(++count%2? evenItemType : oddItemType);
li.appendChild(extracted);
cur.parentNode.replaceChild(li, cur);
newNodes.push(li);
first=false;
}
var node = document.createElement(listType);
startNode = newNodes[0];
endNode = newNodes[newNodes.length -1];
sel.setStartBefore(startNode);
sel.setEndAfter(endNode);
sel.surroundContents(node);
if (wasCollapsed) { sel.selectNodeContents(li); sel.collapse(false); }
else sel.selectNode(node);
this.select(sel);
}}

function RTZ_superBlockFormat (tagName, attrs) {
if (this.inlineOnly) return false;
var sel = this.getSelection();
var wasCollapsed = sel.collapsed;
var realStartNode = sel.startContainer, realEndNode = sel.endContainer, realStartOffset = sel.startOffset, realEndOffset = sel.endOffset;
var startNode = sel.startContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'ul', 'ol', 'dl',]);
var endNode = sel.endContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'ul', 'ol', 'dl']);
sel.setStartBefore(startNode);
sel.setEndAfter(endNode);
var node = document.createElement(tagName);
if (attrs) for (var i in attrs) node.setAttribute(i, attrs[i]);
try {
sel.surroundContents(node);
} catch(e) { alert('Superblock formatting failed'); return; }
if (!wasCollapsed) sel.selectNodeContents(node);
else {
sel.setStart(realStartNode, realStartOffset);
sel.setEnd(realEndNode, realEndOffset);
sel.collapse(false);
}
this.select(sel);
}

function RTZ_removeFormatting () {
if (this.inlineOnly) return false;
var sel = this.getSelection();
var startNode = sel.startContainer.findAncestor(['p', 'li', 'dd', 'dt', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre']);
var endNode = sel.endContainer.findAncestor(['p', 'li', 'dd', 'dt', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre']);
var startTag = startNode.tagName.toLowerCase(), endTag = endNode.tagName.toLowerCase();
if ((startTag=='dd' || startTag=='dt') && endTag!='dd' && endTag!='dt') return false;
else if (startTag!='dd' && startTag!='dt' && startTag!=endTag) return false;
if (startNode==endNode && ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'].indexOf(startTag)>=0) return this.simpleBlockFormat('p'); // An heading is selected: simply reset it to <p>
sel.setStartBefore(startNode);
sel.setEndAfter(endNode);
if (startNode.parentNode.firstElementChild==startNode && ['li', 'dd', 'dt'].indexOf(startTag)>=0) { // The first few items of a list are selected, put them out of the list
var parent = startNode.parentNode, grandParent = parent.parentNode, grandParentTag = grandParent.tagName.toLowerCase();
var frag = sel.extractContents();
if (grandParentTag!='li') frag.eachChild(function(li){
if (!li.tagName || ['li', 'dd', 'dt'].indexOf(li.tagName.toLowerCase())<0) return;
sel.selectNodeContents(li);
var frag2 = sel.extractContents();
var p = document.createElement('p');
p.appendChild(frag2);
li.parentNode.insertBefore(p,li);
li.parentNode.removeChild(li);
});//each fragment child
else { parent=grandParent; grandParent = grandParent.parentNode; }
var fs = frag.firstChild, ls = frag.lastChild;
grandParent.insertBefore(frag, parent);
this.cleanHTMLElement(sel,parent);
try {
sel.setStartBefore(fs);
sel.setEndAfter(ls);
this.select(sel);
} catch(e){}//Just in case the elements aren't any longer in the document
}
else if (endNode.parentNode.lastElementChild==endNode && ['li', 'dd', 'dt'].indexOf(startTag)>=0) { // The last few items of a list are selected: put them out of the list
var parent = startNode.parentNode, grandParent = parent.parentNode, grandParentTag = grandParent.tagName.toLowerCase();
var frag = sel.extractContents();
if (grandParentTag!='li') frag.eachChild(function(li){
if (!li.tagName || ['li', 'dd', 'dt'].indexOf(li.tagName.toLowerCase())<0) return;
sel.selectNodeContents(li);
var frag2 = sel.extractContents();
var p = document.createElement('p');
p.appendChild(frag2);
li.parentNode.insertBefore(p,li);
li.parentNode.removeChild(li);
});//each fragment child
else { parent=grandParent; grandParent = grandParent.parentNode; }
var fs = frag.firstChild, ls = frag.lastChild;
grandParent.insertBefore(frag, parent.nextElementSibling);
this.cleanHTMLElement(sel,parent);
try {
sel.setStartBefore(fs);
sel.setEndAfter(ls);
this.select(sel);
} catch(e){}//Just in case the elements aren't any longer in the document
}
else if (['li', 'dd', 'dt'].indexOf(startTag)>=0) { // A few items in the middle of a list are selected: let's assume that we want to remove the whole list
var lst = startNode.parentNode;
sel.setStartBefore(lst.firstChild.firstChild);
sel.setEndAfter(lst.lastChild.lastChild);
this.select(sel);
this.removeFormatting(); // This will fall in one of the two above cases
}
else if (startTag=='pre') { // Code block: make it simple, assume we want to remove the entire block
var node = startNode;
if (startNode.firstElementChild && startNode.firstElementChild.tagName.toLowerCase()=='code') node = startNode.firstElementChild;
var frag = document.createDocumentFragment(), p = frag.appendElement('p');
node.eachChild(true, function(el){
if (el.tagName && el.tagName.toLowerCase()=='br') p = frag.insertElementBefore('p', frag.firstChild); 
else p.insertBefore(el, p.firstChild);
});//each pre/code child
var fs = frag.firstChild, ls = frag.lastChild;
startNode.parentNode.insertBefore(frag, startNode);
startNode.parentNode.removeChild(startNode);
sel.setStartBefore(fs);
sel.setEndAfter(ls);
this.select(sel);
}
// other cases
}

function RTZ_insertElement (tagName, attrs) {
var sel = this.getSelection();
var node = document.createElement2(tagName, attrs);
sel.insertNode(node);
sel.setStartAfter(node);
sel.setEndAfter(node);
this.select(sel);
return false;
}

function RTZ_insertIcon (url, alt) {
var img = document.createElement2('img', {'alt':alt, 'src':url});
var sel = this.getSelection();
sel.insertNode(img);
sel.setStartAfter(img);
sel.setEndAfter(img);
sel.collapse(false);
this.select(sel);
}

function RTZ_insertIllustration (url, alt, style) {
if (this.inlineOnly) return false;
var figure = document.createElement2('figure', {'class':style});
var img = figure.appendElement('img', {'alt':'', 'src':url, 'width':'99%'});
var capt = figure.appendElement('figcaption');
var captP = capt.appendElement('p').appendText(alt);
var sel = this.getSelection();
var ancestor = sel.commonAncestorContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'dl', 'pre']);
ancestor.parentNode.insertBefore(figure, ancestor.nextSibling);
sel.selectNodeContents(captP);
sel.collapse(false);
this.select(sel);
}

function RTZ_insertBox (type, position) {
if (this.inlineOnly) return false;
var t = type.split('.');
var tagName = t[0];
var classNames = t[1] + ' ' + position;
this.superBlockFormat(tagName, {'class':classNames});
}

function RTZ_insertTable (nRows, nCols, captionText, thScheme, style) {
if (this.inlineOnly) return false;
var table = document.createElement2('table', {'class':style, 'data-th':thScheme});
var firstCell = null;
for (var i=0; i<nRows; i++) {
var tr = table.appendElement('tr');
for (var j=0; j<nCols; j++) {
var type = 'td';
if (i==0 && (thScheme=='top' || thScheme=='both')) type='th';
if (j==0 && (thScheme=='left' || thScheme=='both')) type = 'th';
var cell = tr.appendElement(type);
cell.appendText('\u00A0'); // Chrome: if the celle is empty, it's no longer possible to reach it with the cursor
if (!firstCell) firstCell=cell;
}}
table.appendElement('caption').appendText(captionText);
var sel = this.getSelection();
var ancestor = sel.commonAncestorContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'dl', 'pre']);
ancestor.parentNode.insertBefore(table, ancestor.nextSibling);
sel.selectNodeContents(firstCell);
sel.collapse(true);
this.select(sel);
}

function RTZ_insertAbbrDialog () {
var sel = this.getSelection();
var _this = this;
DialogBox(msgs.InsertAbbr, [
{label:msgs.AbbrTitle, name:'abbrtitle'},
], function(){ 
_this.select(sel);
_this.inlineFormat('abbr', false, {title:this.elements.abbrtitle.value});
_this.zone.focus();
});//DialogBox
}

function RTZ_insertIconDialog (imgUrl) {
imgUrl = imgUrl || '';
var sel = this.getSelection();
var _this = this;
DialogBox(msgs.InsertIcon, [
{label:msgs.IconURL, name:'url', value:imgUrl},
{label:msgs.IconAlt, name:'alt'},
], function(){ 
_this.select(sel);
_this.insertIcon(this.elements.url.value, this.elements.alt.value);
_this.zone.focus();
});//DialogBox
}

function RTZ_insertIllustrationDialog (imgUrl) {
if (this.inlineOnly) return false;
imgUrl = imgUrl || '';
var sel = this.getSelection();
var _this = this;
DialogBox(msgs.InsertIllu, [
{label:msgs.IlluURL, name:'url', value:imgUrl},
{label:msgs.IlluAlt, name:'alt'},
{label:msgs.IlluStyle, name:'istyle', type:'select', values:this.positionalStyles},
], function(){ 
_this.select(sel);
_this.insertIllustration(this.elements.url.value, this.elements.alt.value, this.elements.istyle.value);
_this.zone.focus();
});//DialogBox
}

function RTZ_insertTableDialog () {
if (this.inlineOnly) return false;
var sel = this.getSelection();
var _this = this;
DialogBox(msgs.InsertTable, [
{type:'number', name:'nRows', label:msgs.TableNbRows, min:2, max:100, step:1, value:4},
{type:'number', name:'nCols', label:msgs.TableNbCols, min:2, max:100, step:1, value:3},
{name:'captionText', label:msgs.TableCaption},
{type:'select', name:'thScheme', label:msgs.TableTHScheme, value:'top', values:{top:msgs.OnTop, left:msgs.OnLeft, both:msgs.BothTopLeft} },
{type:'select', name:'tstyle', label:msgs.TableStyle, values:this.positionalStyles},
], function(){
var nRows = parseInt(this.elements.nRows.value), nCols = parseInt(this.elements.nCols.value);
if (nRows<2 || nCols<2) return false;
_this.select(sel);
_this.insertTable(nRows, nCols, this.elements.captionText.value, this.elements.thScheme.value, this.elements.tstyle.value);
_this.zone.focus();
});//DialogBox
}

function RTZ_insertBoxDialog () {
if (this.inlineOnly) return false;
var _this = this;
var sel = this.getSelection();
DialogBox(msgs.InsertBox, [
{label:msgs.IBoxType, name:'type', type:'select', values:this.boxTypes},
{label:msgs.IBoxPosition, name:'position', type:'select', values:this.positionalStyles},
], function(){
_this.select(sel);
_this.insertBox(this.elements.type.value, this.elements.position.value);
_this.zone.focus();
});//Dialog box
}

function RTZ_quickUploadDialog () {
DialogBox(msgs.AddFiles, [
{name:'upload', label:msgs.Upload, type:'file'}
], function(){
RTZ_uploadFiles(this.elements.upload.files);
});//DialogBox
}

function RTZ_openPreview () {
var previewBtn = document.getElementById('previewBtn');
if (!previewBtn || !previewBtn.hasAttribute('data-href')) return;
var href = previewBtn.getAttribute('data-href');
window.open(href);
if (this.onsave) this.onsave();
}

function RTZ_cleanHTML () {
if (this.inlineOnly) return;
var cursel = this.getSelection() || document.createRange();
var startNode = cursel.startContainer, endNode = cursel.endContainer, startOf = cursel.startOffset, endOf = cursel.endOffset;
var sel = document.createRange();
sel.selectNodeContents(this.zone);
var frag = sel.extractContents();
this.cleanHTMLElement(sel, frag);
cleanHTML2(sel, frag);
frag.querySelectorAll('div, aside, section, figure').each(cleanHTML2.bind(this, sel));
this.zone.appendChild(frag);
try {
cursel.setStart(startNode, startOf);
cursel.setEnd(endNode, endOf);
this.select(cursel);
} catch(e) {} // Just in case the previous selection is no longer in the document
}

function RTZ_cleanHTMLElement (sel, o, inlineContext) {
var allowedElements = 'p h1 h2 h3 h4 h5 h6 ul ol li dl dt dd table tbody thead tfoot tr th td caption br a b i q s strong em abbr sup sub ins del code pre hr img audio video source track object param section aside header footer figure figcaption mark var samp kbd span div'.split(' ');
var trimableElements = 'p h1 h2 h3 h4 h5 h6 li dt dd th td caption pre div'.split(' ');
var ignoreElements = ['math', 'script'];
var allowedEmptyElements = ['br', 'img', 'hr', 'mark'];
var allowedAttrs = {
'#':[ 'id', 'class', 'role', 'aria-label', 'aria-level', 'aria-describedby' ],
a:['href', 'rel', 'rev', 'type', 'hreflang', 'title'],
abbr:['title'],
img:['src', 'width', 'height', 'alt'],
ol:['type', 'start'],
};
var remove = false, rename=null, surround=null;
if (o.nodeType==1 && ignoreElements.indexOf(o.nodeName.toLowerCase())>=0) return; // Ignored type of element: don't go further
if (o.nodeType==11 && !inlineContext) { // document fragment
// Look for blocks incorrectly present within a big <p>; this can happen when pasting 
var blocks = o.querySelectorAll('ul, ol, dl, div, pre, p, h1, h2, h3, h4, h5, h6, aside, section, figure');
for (var i=0; i<blocks.length; i++) {
var block = blocks[i];
var p = block.findAncestor(['p']);
if (!p || p==block) continue;
var sel3 = document.createRange();
sel3.selectNodeContents(p);
var extracted = sel3.extractContents();
p.parentNode.insertBefore(extracted, p);
p.parentNode.removeChild(p);
}}
// Normalize and clean children
if (o.normalize) o.normalize();
if (o.childNodes) for (var i=o.childNodes.length -1; i>=0; i--) this.cleanHTMLElement(sel, o.childNodes[i], inlineContext);
// Clean attributes
if (o.attributes) for (var i=o.attributes.length -1; i>=0; i--) {
var removeA=true, attr = o.attributes[i], nodeName = o.nodeName.toLowerCase();
if (attr.name.startsWith('data-')) continue;
if (allowedAttrs['#'].indexOf(attr.name)>=0) removeA=false;
if (allowedAttrs[nodeName] && allowedAttrs[nodeName].indexOf(attr.name)>=0) removeA=false;
if (attr.name=='class') {
var classNames = attr.value.split(' ');
var newClassNames = [];
for (var j=0; j<classNames.length; j++) {
var add=false, cn = classNames[j];
if (this.styles[nodeName] && this.styles[nodeName].indexOf(cn)>=0) add=true;
else if (this.positionalStyles[cn]) add=true;
if (add) newClassNames.push(cn);
}
attr.value = newClassNames.join(' ');
if (!attr.value || attr.value.length<=0) removeA=true;
}
if (removeA) o.removeAttributeNode(attr);
}
switch(o.nodeType) {
case 3: // textnode
if (o.nodeValue.trim().length==0) remove=6; // empty or entirely composed of spaces: useless, so remove
if (o.parentNode.nodeType==11 && !inlineContext) surround='p'; // Text node directly within the fragment or a node which should normally not contain direct text children; probably not correct, should be surrounded by <p> if we are in block context
if (o.parentNode.nodeType==1 && !inlineContext && ['div', 'aside', 'section', 'figure', 'figcaption'].indexOf(o.parentNode.nodeName.toLowerCase())>=0) surround='p'; // idem
if (o.parentNode.nodeType==1 && trimableElements.indexOf(o.parentNode.tagName.toLowerCase())>=0 && !o.previousSibling) o.data = o.data.ltrim();
if (o.parentNode.nodeType==1 && trimableElements.indexOf(o.parentNode.tagName.toLowerCase())>=0 && !o.nextSibling) o.data = o.data.rtrim();
break;
case 1: { // Normal element
var nodeName = o.nodeName.toLowerCase();
if (allowedElements.indexOf(nodeName)<0) remove=5; // element not explicitely allowed: remove but keep children
if (!o.hasChildNodes() && allowedEmptyElements.indexOf(nodeName)<0) remove=4; // Empty element which isn't permitted to be: remove
if ((!o.attributes || o.attributes.length<=0) && (nodeName=='div' || nodeName=='span')) remove=3; // a div or span without any attribute is meaningless: remove
if (nodeName=='p' && ['p', 'td', 'th', 'li', 'dt', 'dd', 'caption'].indexOf(o.parentNode.nodeName.toLowerCase())>=0) remove=2; // p isn't strictly disallowed within elements listed, but it isn't good semantic: remove but keep children
if (nodeName=='br' && o.parentNode.firstElementChild==o) remove = 7; // <br> as a first child is useless: remove
if (nodeName=='br' && o.parentNode.lastElementChild==o) remove = 8; // <br> as a last child is useless: remove
if (nodeName=='i') rename='em'; 
else if (nodeName=='b') rename='strong';
}break;
case 11: break;
default: remove=1; break; // Any other bizarre XML element
}
if (remove && o.hasChildNodes()) {
sel.selectNodeContents(o);
var extracted = sel.extractContents();
o.parentNode.insertBefore(extracted, o);
o.parentNode.removeChild(o);
}
else if (rename) {
sel.selectNodeContents(o);
var extracted = sel.extractContents();
var newEl = o.ownerDocument.createElement(rename);
newEl.appendChild(extracted);
o.parentNode.insertBefore(newEl, o);
o.parentNode.removeChild(o);
}
else if (remove && !o.hasChildNodes()) o.parentNode.removeChild(o);
else if (surround) {
sel.selectNode(o);
var newEl = o.ownerDocument.createElement(surround);
sel.surroundContents(newEl);
}
}

function cleanHTML2 (sel, frag) {
var firstNode = null, lastNode=null;
for (var i=0; i<frag.childNodes.length; i++) {
var o = frag.childNodes[i];
if (o.nodeType==3 || ['a', 'strong', 'em', 'abbr', 's', 'span', 'kbd', 'var', 'samp'].indexOf(o.nodeName.toLowerCase())>=0) { if (firstNode==null) firstNode=o; lastNode=o; }
else if (firstNode!=null) { cleanHTML2Surround(sel, firstNode, lastNode); firstNode=null; i=0; }
}
if (firstNode!=null) cleanHTML2Surround(sel, firstNode, lastNode);
}

function cleanHTML2Surround (sel, startNode, endNode) {
sel.setStartBefore(startNode);
sel.setEndAfter(endNode);
sel.surroundContents(document.createElement('p'));
}

function RTZ_paste () {
var count=0, lengthBefore = this.zone.innerHTML.length;
var f = (function(){
if (this.zone.innerHTML.length==lengthBefore) {
if (++count<2000) setTimeout(f,1);
else alert('Paste failed');
}
this.cleanHTML();
}) .bind(this);
setTimeout(f,1);
return true;
}

function RTZ_defaultSave (code) {
var data = code || this.zone.innerHTML;
var url = window.actionUrl.replace('@@', 'save');
ajax('POST', url, 'content='+encodeURIComponent(data), function(e){
var div = document.getElementById('debug3');
if (!div) { div=document.querySelector('body').appendElement('div', {id:'debug3'}); }
div.innerHTML = e;
}, function(){alert('Save failed');});
};

function RTZ_uploadFiles (files) {
if (!files || files.length<=0) return; // empty or upload not supported
var url = window.location.href;
var data = new FormData();
data.append('addfiles', '1');
data.append('noredir', '1');
data.append('fileName', '');
data.append('id', '');
for (var i=0; i<files.length; i++) data.append('upload', files[i], files[i].name);
ajax('POST', url, data, function(e){
var div = document.getElementById('debug3');
if (!div) { div=document.querySelector('body').appendElement('div', {id:'debug3'}); }
div.innerHTML = e;
}, function(){alert('Upload failed');});
}

function RTZ_toString () {
return "RTZ"+JSON.stringify(this);
}

function RTZ_debug_updateHTMLPreview () {
var code = this.zone.innerHTML;
code = code.replace(/>[ \r\n\t]*</g, '>\r\n<');
code = code.split('&').join('&amp;').split('<').join('&lt;').split('>').join('&gt;').split('\r\n|\n|\r').join('<br />');
code = code.replace(/^\s+/m, '').replace(/\s+$/m, '');
this.htmlCodePreview.innerHTML = code;
}

function RTZ_keyCodeToString (k) {
var mods = [];
if (k&vk.ctrl) mods.push(msgs.Ctrl);
if (k&vk.shift) mods.push(msgs.Shift);
if (k&vk.alt) mods.push(msgs.Alt);
k&=0x7F;
if (k>=65 && k<=90) k = String.fromCharCode(k);
else if (k>=48 && k<=57) k -= 48;
mods.push(k);
return mods.join('+');
}

function RTZ_selectionTimer () {
var sel = this.getSelection();
if (!this.lastSelection || this.lastSelection.so!=sel.startOffset || this.lastSelection.eo!=sel.endOffset || this.lastSelection.sc!=sel.startContainer || this.lastSelection.ec!=sel.endContainer) {
this.lastSelection = {sc:sel.startContainer, so:sel.startOffset, ec:sel.endContainer, eo:sel.endOffset};
this.lastSelectionIdentifier = this.lastSelectionIdentifier? this.lastSelectionIdentifier+1 : 1;
if (sel.commonAncestorContainer.isInside(this.zone) && this.onselchanged) this.onselchanged(sel, this.lastSelectionIdentifier);
}
}

function RTZ_getSelection () {
var selectionObject = window.getSelection();
if (selectionObject.getRangeAt) try {
return selectionObject.getRangeAt(0);
} catch(exc){ return null; }
	else { // Safari!
		var range = document.createRange();
		range.setStart(selectionObject.anchorNode,selectionObject.anchorOffset);
		range.setEnd(selectionObject.focusNode,selectionObject.focusOffset);
		return range;
	}}

function RTZ_select (sel) {
var w = window.getSelection();
w.removeAllRanges();
w.addRange(sel);
}

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
$('.editor, *[contenteditable=true]').each(function(e){
var toolbarId = e.getAttribute('data-toolbar');
var toolbar = toolbarId? document.getElementById(toolbarId) : null;
e.setAttribute('tabindex',0);
var rtz = new RTZ( e, toolbar);
rtz.debug = e.tagName.toLowerCase()=='div';
rtz.init();
if (!rtz.onsave) rtz.onsave = RTZ_defaultSave;
});//each .editor/contenteditable
});

//alert('RTZ13 loaded');