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
this.insertIcon = RTZ_insertIcon;
this.insertIllustration = RTZ_insertIllustration;
this.insertIconDialog = RTZ_insertIconDialog;
this.insertIllustrationDialog = RTZ_insertIllustrationDialog;
this.enterKey = RTZ_enterKey;
this.enterKeyOnEmptyParagraph = RTZ_enterKeyOnEmptyParagraph;
this.enterKeyOnNonEmptyParagraph = RTZ_enterKeyOnNonEmptyParagraph;
this.init = RTZ_init;
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
link: vk.ctrl+vk.k,
bold: vk.ctrl+vk.b,
italic: vk.ctrl+vk.i,
strikeout: vk.ctrl+vk.shift+vk.k,
regular: vk.ctrl+vk.n0,
h1: vk.ctrl+vk.n1,
h2: vk.ctrl+vk.n2,
h3: vk.ctrl+vk.n3,
h4: vk.ctrl+vk.n4,
h5: vk.ctrl+vk.n5,
h6: vk.ctrl+vk.n6,
orderedList: vk.ctrl+vk.l,
unorderedList: vk.ctrl+vk.u,
definitionList: vk.ctrl+vk.shift+vk.d,
blockquote: vk.ctrl+vk.q,
insertIcon: vk.ctrl+vk.shift+vk.i,
insertIllustration: vk.ctrl+vk.shift+vk.g,
};
}

function RTZ_init () {
var _this = this;
this.zone.onkeydown = RTZ_keyDown.bind(this);
this.zone.onpaste = RTZ_paste.bind(this);
this.toolbar.$('button').each(function(){ this.onclick = RTZ_toolbarButtonClick.bind(_this, this.getAttribute('data-action')); });
this.toolbar.$('select').each(function(){ this.onchange = RTZ_toolbarStyleSelect.bind(_this, this); });
if (this.debug){
var div = document.createElement('div');
div.appendElement('h1').appendText('Debug HTML code');
this.htmlCodePreview = div.appendElement('pre');
this.zone.onkeyup = RTZ_debug_updateHTMLPreview.bind(this);
this.zone.parentNode.insertBefore(div, this.zone.nextSibling);
this.zone.onkeyup(null);
}
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
if (k>=20) {
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

function RTZ_implKeyDown (k, simulated) {
switch(k){
case vk.enter:
this.enterKey();
break;
case keys.regular:
this.simpleBlockFormat('p');
break;
case keys.h1:
case keys.h2:
case keys.h3:
case keys.h4:
case keys.h5:
case keys.h6: {
var n = (k - vk.ctrl -vk.n0);
this.simpleBlockFormat('h'+n, {'role':'heading', 'aria-level':n});
}break;
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
this.inlineFormat('del', false);
break;
case keys.unorderedList:
this.formatAsList('ul', 'li', 'li');
break;
case keys.orderedList:
this.formatAsList('ol', 'li', 'li');
break;
case keys.definitionList:
this.formatAsList('dl', 'dt', 'dd');
break;
case keys.blockquote:
this.superBlockFormat('blockquote');
break;
case keys.insertIcon:
this.insertIconDialog();
break;
case keys.insertIllustration :
this.insertIllustrationDialog();
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
Editor_save();
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
var sel = this.getSelection();
var textNode = null;
var el = sel.commonAncestorContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'li', 'dt', 'dd', 'th', 'td', 'figcaption']);
if (!sel.collapsed) {
sel.deleteContents();
sel.collapse(false);
}
if (sel.commonAncestorContainer.nodeType==3 && sel.endOffset<sel.endContainer.length) {
var node = sel.endContainer;
textNode = node.splitText(sel.endOffset);
node.parentNode.removeChild(textNode);
if (node.length<=0) node.parentNode.removeChild(node);
}
if (!el.hasChildNodes()) this.enterKeyOnEmptyParagraph(sel, el, textNode);
else this.enterKeyOnNonEmptyParagraph(sel, el, textNode);
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

function RTZ_enterKeyOnNonEmptyParagraph (sel, el, textNode) {
var match, ok, tgn=el.tagName.toLowerCase();
ok = tgn!='figcaption' && tgn!='caption' && tgn!='td' && tgn!='th';
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
if (textNode) li.appendChild(textNode);
if (el.nodeName.toLowerCase()=='li') el.appendChild(ol);
else {
el.parentNode.insertBefore(ol, el);
el.parentNode.removeChild(el);
}
sel.selectNodeContents(li);
sel.collapse(false);
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
if (textNode) li.appendChild(textNode);
if (el.nodeName.toLowerCase()=='li') el.appendChild(ul);
else {
el.parentNode.insertBefore(ul, el);
el.parentNode.removeChild(el);
}
sel.selectNodeContents(li);
sel.collapse(false);
this.select(sel);
return;
}
if (tgn=='figcaption') tgn='p';
else if (tgn=='dd') tgn='dt';
else if (tgn=='dt') tgn='dd';
var newEl = document.createElement(tgn);
el.parentNode.insertBefore(newEl, el.nextSibling);
if (textNode) newEl.appendChild(textNode);
sel.selectNodeContents(newEl);
sel.collapse(true);
this.select(sel);
}

function RTZ_enterKeyOnEmptyParagraph (sel, el, textNode) {
var parent = el.parentNode;
var tgn = 'p';
if (parent.parentNode && parent.parentNode.tagName.toLowerCase()=='li') tgn='li';
else if (parent.parentNode && parent.parentNode.tagName.toLowerCase()=='dd') tgn='dt';
var newEl = document.createElement(tgn);
if (textNode) newEl.appendChild(textNode);
if (parent==this.zone) parent.replaceChild(newEl, el);
else {
parent.removeChild(el);
var parentTgn = (parent.parentNode? parent.parentNode.tagName.toLowerCase() : '#none');
if (parentTgn=='li' || parentTgn=='dd') parent = parent.parentNode;
parent.parentNode.insertBefore(newEl, parent.nextSibling);
}
sel.selectNodeContents(newEl);
sel.collapse(true);
this.select(sel);
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
if (sel.collapsed && sel.commonAncestorContainer.nodeType==3 && sel.endOffset==sel.commonAncestorContainer.nodeValue.length) { // Are we exactly at the end of the element ? In this case we would perhaps like to continue writing without the attribute, not remove it
sel.setEndAfter(same);
sel.collapse(false);
this.select(sel);
} else {
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
} catch(e) { alert('failed'); return; }
if (!allowNest) node.$(tagName).each(function(){ sel.selectNodeContents(this);  var ex = sel.extractContents();  this.parentNode.replaceChild(ex, this);  }); // If needed, let's clean duplicate tags, i.e. <b><b></b></b>, before forming the final new selection
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
return false;
}, null); //DialogBox
}

function RTZ_simpleBlockFormat (tagName, attrs) {
var sel = this.getSelection();
var collapsed = sel.collapsed;
var node = sel.commonAncestorContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
if (!node) return;
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
var sel = this.getSelection();
var wasCollapsed = sel.collapsed;
var startNode = sel.startContainer.findAncestor(['p', 'li']);
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
} catch(e) { alert('Failed!'); return; }
if (!wasCollapsed) sel.selectNodeContents(node);
else {
sel.setStart(realStartNode, realStartOffset);
sel.setEnd(realEndNode, realEndOffset);
sel.collapse(false);
}
this.select(sel);
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

function RTZ_insertIllustration (url, alt) {
var figure = document.createElement('figure');
var img = figure.appendElement('img', {'alt':'', 'src':url, 'width':'96%'});
var capt = figure.appendElement('figcaption').appendText(alt);
var sel = this.getSelection();
var ancestor = sel.commonAncestorContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'dl', 'pre']);
ancestor.parentNode.insertBefore(figure, ancestor.nextSibling);
sel.setStartAfter(capt);
sel.setEndAfter(capt);
sel.collapse(false);
this.select(sel);
}

function RTZ_insertIconDialog () {
var sel = this.getSelection();
var _this = this;
DialogBox(msgs.InsertIcon, [
{label:msgs.IconURL, name:'url'},
{label:msgs.IconAlt, name:'alt'},
], function(){ 
_this.select(sel);
_this.insertIcon(this.elements.url.value, this.elements.alt.value);
_this.zone.focus();
}); //DialogBox
}

function RTZ_insertIllustrationDialog () {
var sel = this.getSelection();
var _this = this;
DialogBox(msgs.InsertIllu, [
{label:msgs.IlluURL, name:'url'},
{label:msgs.IlluAlt, name:'alt'},
], function(){ 
_this.select(sel);
_this.insertIllustration(this.elements.url.value, this.elements.alt.value);
_this.zone.focus();
}); //DialogBox
}


function RTZ_paste (e) {
var sel = this.getSelection();
var div = document.createElement('div');
this.zone.appendChild(div);
sel.selectNodeContents(div);
this.select(sel);
alert(1);
return true;

}

function RTZ_debug_updateHTMLPreview () {
var code = this.zone.innerHTML;
code = code.replace(/>\s*</g, '>\r\n<');
code = code.split('&').join('&amp;').split('<').join('&lt;').split('>').join('&gt;').split('\r\n').join('<br />');
code = code.replace(/^\s+/m, '').replace(/\s+$/m, '');
this.htmlCodePreview.innerHTML = code;
}

function RTZ_getSelection () {
var selectionObject = window.getSelection();
if (selectionObject.getRangeAt)
		return selectionObject.getRangeAt(0);
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
var rtz = new RTZ( $('#editor')[0], $('#toolbar')[0]);
rtz.debug = true;
rtz.init();
});

alert('RTZ loaded');