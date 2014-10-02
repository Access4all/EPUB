function RTZ (zone) {
this.zone = zone;
this.select = RTZ_select;
this.getSelection = RTZ_getSelection;
this.simpleBlockFormat = RTZ_simpleBlockFormat;
this.inlineFormat = RTZ_inlineFormat;
this.superBlockFormat = RTZ_superBlockFormat;
this.formatAsList = RTZ_formatAsList;
this.init = RTZ_init;
this.implKeyDown = RTZ_implKeyDown;
}

function RTZ_init () {
this.zone.onkeydown = RTZ_keyDown.bind(this);
if (this.debug){
var div = document.createElement('div');
div.appendElement('h1').appendText('Debug HTML code');
this.htmlCodePreview = div.appendElement('pre');
this.zone.onkeyup = RTZ_debug_updateHTMLPreview.bind(this);
this.zone.parentNode.insertBefore(div, this.zone.nextSibling);
this.zone.onkeyup(null);
}
}

function RTZ_keyDown (e) {
e = e || window.event;
var k = e.keyCode || e.which;
if (k>=20) {
if (e.ctrlKey) k |= vk.ctrl;
if (e.shiftKey) k|=vk.shift;
if (e.altKey) k|=vk.alt;
}
var re = this.implKeyDown(k);
if (!re){
if (e.preventDefault) e.preventDefault();
if (e.stopPropagation) e.stopPropagation();
}
return re;
}

function RTZ_implKeyDown (k) {
switch(k){
case vk.ctrl+vk.n0:
this.simpleBlockFormat('p');
break;
case vk.ctrl+vk.n1 :
case vk.ctrl+vk.n2 :
case vk.ctrl+vk.n3 :
case vk.ctrl+vk.n4 :
case vk.ctrl+vk.n5 :
case vk.ctrl+vk.n6 : {
var n = (k - vk.ctrl -vk.n0);
this.simpleBlockFormat('h'+n, {'role':'heading', 'aria-level':n});
}break;
case vk.ctrl+vk.b:
case vk.ctrl+vk.g:
this.inlineFormat('strong', false);
break;
case vk.ctrl+vk.i:
this.inlineFormat('em', false);
break;
case vk.ctrl+vk.shift+vk.k:
this.inlineFormat('del', false);
break;
case vk.ctrl+vk.u :
this.formatAsList('ul', 'li', 'li');
break;
case vk.ctrl+vk.l :
this.formatAsList('ol', 'li', 'li');
break;
case vk.ctrl+vk.shift+vk.d :
this.formatAsList('dl', 'dt', 'dd');
break;
case vk.ctrl+vk.q :
this.superBlockFormat('blockquote');
break;
default: return true;
}
return false;
}

function RTZ_inlineFormat (tagName, allowNest, attrs) {
var sel = this.getSelection();
var node = sel.commonAncestorContainer;
var same = null;
if (!allowNest) { // Nesting not allowed; let's look if we are already within a tag of the same type
while(node!=null && node!=this) {
if (node.nodeName.toUpperCase() == tagName.toUpperCase() ) { same=node; break; }
node = node.parentNode;
}}

if (same) { // We are already within a tag of the same type
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
node = document.createElement(tagName);
if (attrs) for (var i in attrs) node.setAttribute(i, attrs[i]);
try {
sel.surroundContents(node);
} catch(e) { alert('failed'); return; }
if (!allowNest) node.$(tagName).each(function(){ sel.selectNodeContents(this);  var ex = sel.extractContents();  this.parentNode.replaceChild(ex, this);  }); // If needed, let's clean duplicate tags, i.e. <b><b></b></b>, before forming the final new selection
sel.selectNodeContents(node);
this.select(sel);
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
sel.selectNodeContents(node);
this.select(sel);
return;
}
else if (startNode.tagName.toLowerCase()=='p') {
var newNodes = [];
var first=true;
var cur, count=0, next = startNode;
while(cur!=endNode || first){
cur = next;
next = cur.nextElementSibling;
sel.selectNodeContents(cur);
var extracted = sel.extractContents();
var li = document.createElement(++count%2? evenItemType : oddItemType);
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
sel.selectNode(node);
this.select(sel);
}}

function RTZ_superBlockFormat (tagName, attrs) {
var sel = this.getSelection();
var startNode = sel.startContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'ul', 'ol', 'dl',]);
var endNode = sel.endContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'ul', 'ol', 'dl']);
sel.setStartBefore(startNode);
sel.setEndAfter(endNode);
var node = document.createElement(tagName);
if (attrs) for (var i in attrs) node.setAttribute(i, attrs[i]);
try {
sel.surroundContents(node);
} catch(e) { alert('Failed!'); return; }
sel.selectNodeContents(node);
this.select(sel);
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
var rtz = new RTZ( $('#editor')[0] );
rtz.debug = true;
rtz.init();
});

alert('RTZ loaded');