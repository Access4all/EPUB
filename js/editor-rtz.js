var undoStack = [], undoPos = 0;


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
if (!window.rtzs) window.rtzs = [];
window.rtzs.push(this);
this.zone = zone;
this.toolbar = toolbar;
this.select = RTZ_select;
this.getSelection = RTZ_getSelection;
this.moveCaretToPoint = RTZ_moveCaretToPoint;
this.simpleBlockFormat = RTZ_simpleBlockFormat;
this.inlineFormat = RTZ_inlineFormat;
this.superBlockFormat = RTZ_superBlockFormat;
this.formatAsList = RTZ_formatAsList;
this.formatAsCodeListing = RTZ_formatAsCodeListing;
this.removeFormatting = RTZ_removeFormatting;
this.insertElement = RTZ_insertElement;
this.insertIcon = RTZ_insertIcon;
this.insertIllustration = RTZ_insertIllustration;
this.insertMultimediaClip = RTZ_insertMultimediaClip;
this.insertTable = RTZ_insertTable;
this.insertBox = RTZ_insertBox;
this.insertFootnote = RTZ_insertFootnote;
this.insertLinkDialog = RTZ_insertLinkDialog;
this.insertIconDialog = RTZ_insertIconDialog;
this.insertIllustrationDialog = RTZ_insertIllustrationDialog;
this.insertMultimediaClipDialog = RTZ_insertMultimediaClipDialog;
this.insertTableDialog = RTZ_insertTableDialog;
this.insertAbbrDialog = RTZ_insertAbbrDialog;
this.insertBoxDialog = RTZ_insertBoxDialog;
this.quickUploadDialog = RTZ_quickUploadDialog;
this.openPreview = RTZ_openPreview;
this.save = RTZ_save;
this.undo = RTZ_undo;
this.redo = RTZ_redo;
this.pushUndoState = RTZ_undo_pushState;
this.pushUndoState2 = RTZ_undo_pushMutationUndoState;
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
this.startRecordDOMChanges = RTZ_startRecordDOMChanges;
this.stopRecordDOMChanges = RTZ_stopRecordDOMChanges;
this.createObservedElement = RTZ_createObservedElement;
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
undo: vk.ctrl+vk.z,
redo: vk.ctrl+vk.shift+vk.z,
preview: vk.impossible +1,
link: vk.ctrl+vk.k,
bold: vk.ctrl+vk.b,
italic: vk.ctrl+vk.i,
strikethrough: vk.ctrl+vk.shift+vk.k,
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
definitionList: vk.ctrl+vk.d,
codeListing: vk.ctrl+vk.shift+vk.l,
blockquote: vk.ctrl+vk.q,
box: vk.ctrl+vk.shift+vk.a,
icon: vk.ctrl+vk.shift+vk.i,
illustration: vk.ctrl+vk.shift+vk.g,
multimediaClip: vk.ctrl+vk.shift+vk.m,
table: vk.ctrl+vk.shift+vk.t,
footnote: vk.ctrl+vk.shift+vk.f,
superscript: vk.ctrl+vk.shift+vk.y,
subscript: vk.ctrl+vk.y,
brTag: vk.shift+vk.enter,
quickUpload: vk.ctrl+vk.shift+vk.u,
cleanHTML: vk.f9,
insTag: vk.ctrl+vk.shift+vk.e,
delTag: vk.ctrl+vk.shift+vk.d,
qTag: vk.ctrl+vk.shift+vk.q,
dfnTag: vk.ctrl+vk.n8,
smallPrint: vk.ctrl+vk.n9,
codeTag: vk.ctrl+vk.t,
varTag: vk.ctrl+vk.shift+vk.n7,
sampTag: vk.ctrl+vk.shift+vk.n8,
kbdTag: vk.ctrl+vk.shift+vk.n9,
/*Note: shortcuts to avoid definitely
Ctrl+P = Print
Ctrl+O = Open
Ctrl+W = Close tab
Ctrl+J = Download manager
*/
};
}

function RTZ_init () {
this.inlineOnly = ['div', 'section', 'aside', 'header', 'footer'].indexOf(this.zone.tagName.toLowerCase())<0;
this.zone.onkeydown = RTZ_keyDown.bind(this);
this.zone.onkeypress = RTZ_keyPress.bind(this);
this.zone.onpaste = RTZ_paste.bind(this);
this.zone.oncut = RTZ_cut.bind(this);
this.zone.ondragover = RTZ_onDragOver;
this.zone.ondrop = RTZ_onDrop.bind(this);
this.zone.onfocus = RTZ_onfocus.bind(this);
this.zone.onblur = RTZ_onblur.bind(this);
this.zone.oncontextmenu = RTZ_contextmenu.bind(this);
this.isAtNonEditablePoint = RTZ_isAtNonEditablePoint;
if (window.MutationObserver || window.WebKitMutationObserver) {
if (!window.MutationObserver) window.MutationObserver = window.WebKitMutationObserver;
this.mutationList = [];
this.mutationObserver = new MutationObserver(RTZ_domchanged.bind(this));
}
if (this.toolbar) {
this.zone.onmousedown = function(){ setInterval(RTZ_selectionTimer.bind(this), 100); this.zone.onmousedown=null; } .bind(this); // we start the automatic style list updater, but only if we ahve a mouse user. It can cause focus troubles for a keyboard-only or screen reader user.
this.toolbar.$('button').each(function(o){ 
var action = o.getAttribute('data-action'), text = o.textContent;
o.onclick = RTZ_toolbarButtonClick.bind(this, action); 
if (keys[action] && keys[action]<vk.impossible && text.indexOf('\t')<0) text += '\t(' + RTZ_keyCodeToString(keys[action]) + ')';
o.textContent = text;
o.setAttribute('title', text);
}.bind(this));
this.toolbar.$('select').each(function(o){ 
o.onchange = RTZ_toolbarStyleSelect.bind(this, o); 
o.$('option').each(function (opt){
var action = opt.getAttribute('value');
var text = opt.firstChild;
if (keys[action] && keys[action]<vk.impossible && text.textContent.indexOf('\t')<0) text.appendData('\t(' + RTZ_keyCodeToString(keys[action]) + ')');
});
}.bind(this));
if (this.mutationObserver) this.toolbar.$('*[data-action=undo], *[data-action=redo]').each(function(o){
o.removeAttribute('disabled');
}.bind(this));
//additional toolbar actions
}
this.loadStyles();
if (this.debug){
if (!this.htmlCodePreview && !this.zone.hasAttribute('data-debughtmlcode')) {
var div = document.createElement('div');
div.appendElement('h1').appendText('Debug HTML code');
this.htmlCodePreview = div.appendElement('pre');
this.zone.parentNode.insertBefore(div, this.zone.nextSibling);
this.zone.setAttribute('data-debughtmlcode', true);
}
this.zone.onkeyup = RTZ_debug_updateHTMLPreview.bind(this);
this.zone.onkeyup(null);
}
this.cleanHTML();
this.startRecordDOMChanges();
if (window.onRTZCreate) window.onRTZCreate(this);
if (this.zone.hasAttribute('data-autofocus')) this.zone.focus();
}

function RTZ_loadStyles () {
if (this.inlineOnly) return;
this.styles = {};
this.positionalStyles = {};
this.boxTypes = {};
this.styleData = {};
if (document.styleSheets) for (var j=0; j<document.styleSheets.length; j++) {
var stylesheet = document.styleSheets[j];
var rules = null;
try { rules = stylesheet.cssRules || stylesheet.rules; } catch(e){} // Against firefox security error
if (!rules) continue;
for (var i=0; i<rules.length; i++) {
var rule = rules[i];
if (rule.selectorText=='#StyleData' && rule.style.content) {
var str = rule.style.content;
str = str.trim().substring(1, str.length -1)
.replace(/\\(['"])/g, '$1').trim();
this.styleData = JSON.parse(str);
continue;
}
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
var tags = ['aside', 'section', 'footer', 'header'];
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
if (k>=20 || k==vk.tab || k==vk.enter) {
if (e.ctrlKey) k |= vk.ctrl;
if (e.shiftKey) k|=vk.shift;
if (e.altKey) k|=vk.alt;
}
var re = this.implKeyDown(k, false);
if (!re){
if (e.preventDefault) e.preventDefault();
if (e.stopPropagation) e.stopPropagation();
}
if (!window.changed && (k==13 || (k>=48&&k<=57) || (k>=65&&k<=90) || k==32) ) window.changed=true;
return re;
}

function RTZ_keyPress (e) {
e = e || window.event;
if (this.isAtNonEditablePoint()) {
if (e.preventDefault) e.preventDefault();
if (e.stopPropagation) e.stopPropagation();
return false;
}
return true;
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
}
else listCb.value = 'regular';
if (window.STE_selectionChangedInRTZ) window.STE_selectionChangedInRTZ(el);
}

function RTZ_domchanged (records) {
//if (this.ignoreMutations) return;
for (var i=0; i<records.length; i++) {
this.mutationList.push(records[i]);
}
}

function RTZ_undo_pushState (fUndo, fRedo) {
var newState = {undo:fUndo, redo:fRedo};
if (undoPos<undoStack.length) undoStack.splice(undoPos, undoStack.length -undoPos, newState);
else undoStack.push(newState);
if (undoStack.length>10) undoStack.shift();
undoPos = undoStack.length;
}

function RTZ_undo_pushMutationUndoState () {
if (!this.mutationList || this.mutationList.length<=0) return; // Don't add empty undo states
var fUndo = RTZ_undoMutationList.bind(this, this.mutationList);
var fRedo = RTZ_redoMutationList.bind(this, this.mutationList);
this.pushUndoState(fUndo, fRedo);
this.mutationList = [];
window.changed=true;
}

function RTZ_undo () {
if (!this.mutationObserver) { MessageBox(msgs.FeatureNotAvailT, msgs.FeatureNotAvail,[msgs.OK]); return; }
if (this.mutationList && this.mutationList.length>0) this.pushUndoState2();
if (undoPos<=0) alert('No more undo');
if (undoPos<=0) return; // no more undo states
var state = undoStack[--undoPos];
state.undo(state);
}

function RTZ_redo () {
if (!this.mutationObserver) { MessageBox(msgs.FeatureNotAvailT, msgs.FeatureNotAvail,[msgs.OK]); return; }
if (this.mutationList && this.mutationList.length>0) { alert('No longer allowed to redo'); return; }
if (undoPos>=undoStack.length) alert('No more redo');
if (undoPos>=undoStack.length) return; // no more states to redo
var state = undoStack[undoPos++];
state.redo(state);
}

function RTZ_isAtNonEditablePoint () {
return false;
}

function RTZ_implKeyDown (k, simulated) {
var editablePoint = this.isAtNonEditablePoint(), kk=k&0xFF;
if (editablePoint && (
kk==10 || kk==13 || kk==32 || (kk>=65 && kk<=90) || (kk>=48 && kk<=57) || kk==vk.backspace
)) return false;
if (this.onkeydown) {
var result = this.onkeydown(k,simulated);
if (result===true || result===false) return result;
}
if (this.saveBtn && ((k>=vk.n0&&k<=vk.n9)||k>=vk.a) ) {
if (!window.readOnly) this.saveBtn.removeClass('disabled');
if (!window.readOnly) this.saveBtn.removeAttribute('aria-disabled');
this.saveBtn=null;
}
switch(k){
case vk.enter:
this.enterKey();
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
this.insertLinkDialog();
break;
case keys.bold:
this.inlineFormat('strong', false);
break;
case keys.italic:
this.inlineFormat('em', false);
break;
case keys.strikethrough:
this.inlineFormat('s', false);
break;
case keys.subscript:
this.inlineFormat('sub', false);
break;
case keys.superscript:
this.inlineFormat('sup', false);
break;
case keys.abbreviation:
this.insertAbbrDialog();
break;
case keys.qTag:
this.inlineFormat('q', false);
break;
case keys.varTag:
this.inlineFormat('var', false);
break;
case keys.dfnTag:
this.inlineFormat('dfn', false);
break;
case keys.kbdTag:
this.inlineFormat('kbd', false);
break;
case keys.sampTag:
this.inlineFormat('samp', false);
break;
case keys.codeTag:
this.inlineFormat('code', false);
break;
case keys.insTag:
this.inlineFormat('ins', false);
break;
case keys.delTag:
this.inlineFormat('del', false);
break;
case keys.smallPrint:
this.inlineFormat('small', false);
break;
case keys.brTag:
this.insertElement('br');
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
case keys.box:
this.insertBoxDialog();
break;
case keys.icon:
this.insertIconDialog();
break;
case keys.illustration :
this.insertIllustrationDialog();
break;
case keys.table :
this.insertTableDialog();
break;
case keys.multimediaClip:
this.insertMultimediaClipDialog();
break;
case keys.copy:
if (!simulated) return true;
try {
if (!document.execCommand('copy', false, null)) throw new Error('failed');
}catch(ex){
MessageBox(msgs.FeatureNotAvailT, msgs.CopyCutPasteFeature, [msgs.OK]);
}
break;
case keys.cut:
if (!simulated) return true;
try {
if (!document.execCommand('cut', false, null)) throw new Error('failed');
}catch(ex){
MessageBox(msgs.FeatureNotAvailT, msgs.CopyCutPasteFeature, [msgs.OK]);
}
break;
case keys.paste:
RTZ_preparePaste.call(this);
if (!simulated) return true;
try {
if (!document.execCommand('paste', false, null)) throw new Error('failed');
}catch(ex){
MessageBox(msgs.FeatureNotAvailT, msgs.CopyCutPasteFeature, [msgs.OK]);
}
break;
case keys.save :
this.save();
break;
case keys.preview:
this.openPreview();
break;
case keys.undo:
this.undo();
break;
case keys.redo:
this.redo();
break;
case keys.footnote:
this.insertFootnote();
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
default: 
return true;
}
return false;
}

function RTZ_enterKey () {
if (this.onenter) {
var re = this.onenter();
if (re===true || re===false) return re;
}
if (this.inlineOnly) return false;
this.pushUndoState2();
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
this.pushUndoState2();
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
this.pushUndoState2();
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

function RTZ_inlineFormat (tagName, allowNest, attrs, justCheck, recursions) {
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
this.pushUndoState2();
sel.selectNodeContents(same);
var extracted = sel.extractContents	();
same.parentNode.replaceChild(extracted, same);
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
};
return;
}

// Common case: surround the selection if possible
if (justCheck) return true;
this.pushUndoState2();
node = document.createElement2(tagName, attrs);
try {
sel.surroundContents(node);
} catch(e) {
if ((recursions = recursions || 4) <=1) return;
RTZ_adjustSelectionForInlineFormatting(this, sel, --recursions);
this.inlineFormat(tagName, allowNest, attrs, justCheck, recursions );
return;
}
if (!allowNest) node.$(tagName).each(function(o){ sel.selectNodeContents(o);  var ex = sel.extractContents();  o.parentNode.replaceChild(ex, o);  }); // If needed, let's clean duplicate tags, i.e. <b><b></b></b>, before forming the final new selection; this can happen for example when requesting <b> for a selection like a[b<b>c</b>d<b>e</b>f]g
if (!node.hasChildNodes()) node.appendText('\u00A0'); // Chrome: if the node is empty, the cursor is incorrectly placed after the node instead of inside it.
sel.selectNodeContents(node);
this.select(sel);
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_adjustSelectionForInlineFormatting (rtz, sel, recursions) {
if (sel.endContainer.nodeType==3 && sel.endOffset==0) {
var prev = sel.endContainer.previousSibling;
if (prev) prev = prev.getLastTextNode();
else prev=null;
if (prev) sel.setEnd(prev, prev.length);
}
if (sel.startContainer.nodeType==3 && sel.startOffset>=sel.startContainer.length) {
var next = sel.startContainer.nextSibling;
if (next) next = next.getFirstTextNode();
else next=null;
if (next) sel.setStart(next, 0);
}
if (recursions<3 && sel.startContainer.nodeType==3 && sel.startOffset==0 && !sel.startContainer.previousSibling) sel.setStartBefore(sel.startContainer.parentNode);
if (recursions<3 && sel.endContainer.nodeType==3 && sel.endOffset>=sel.endContainer.length  && !sel.endContainer.nextSibling) sel.setEndAfter(sel.endContainer.parentNode);
rtz.select(sel);
}

function RTZ_insertLinkDialog () {
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

function RTZ_modifyLinkDialog (link) {
var url = link.getAttribute('href') || '#';
var text = link.textContent || '';
var _this = this;
DialogBox(msgs.Link, [
{label:msgs.LinkURL, name:'url', value:url},
{label:msgs.LinkText, name:'ltext', value:text}
], function(){
var newText = this.elements.ltext.value;
link.setAttribute('href', this.elements.url.value);
if (newText!=text) link.innerHTML=newText;
if (this.elements.url.value.length<=0) {
var sel = document.createRange();
sel.selectNodeContents(link);
_this.select(sel);
_this.inlineFormat('a', false);
}
_this.zone.focus();
}, null); //DialogBox
}

function RTZ_formatAsCodeListing () {
if (this.inlineOnly) return false;
this.pushUndoState2();
var sel = this.getSelection();
var wasCollapsed = sel.collapsed;
var startNode = sel.startContainer.findAncestor(['p']);
var endNode = sel.endContainer.findAncestor(['p']);
if (!startNode || !endNode) return;
sel.setStartBefore(startNode);
sel.setEndAfter(endNode);
if (startNode.tagName!=endNode.tagName) return;
var extracted = sel.extractContents();
var pre = this.createObservedElement('pre');
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
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_simpleBlockFormat (tagName, attrs) {
if (this.inlineOnly) return false;
this.pushUndoState2();
var sel = this.getSelection();
var collapsed = sel.collapsed;
var node = sel.commonAncestorContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
if (!node && sel.commonAncestorContainer && sel.commonAncestorContainer.parentNode==this.zone) {
node = this.createObservedElement('p');
sel.selectNodeContents(sel.commonAncestorContainer);
sel.surroundContents(node);
sel.selectNodeContents(node);
}
if (!node) return;
if (!node.isInside(this.zone)) return;
var newNode = this.createObservedElement(tagName, attrs);
sel.selectNodeContents(node);
var extracted = sel.extractContents();
newNode.appendChild(extracted);
node.parentNode.replaceChild(newNode, node);
sel.selectNodeContents(newNode);
if (collapsed) sel.collapse(false);
this.select(sel);
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_formatAsList (listType, oddItemType, evenItemType) {
if (this.inlineOnly) return false;
this.pushUndoState2();
var sel = this.getSelection();
var wasCollapsed = sel.collapsed;
var startNode = sel.startContainer.findAncestor(['p', 'li']);
if (!startNode) return false;
var endNode = sel.endContainer.findAncestor(['p', 'li']);
sel.setStartBefore(startNode);
sel.setEndAfter(endNode);
if (startNode.tagName!=endNode.tagName) return;
if (startNode.tagName.toLowerCase()=='li') {
var node = this.createObservedElement(listType);
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
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
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
li = this.createObservedElement(++count%2? evenItemType : oddItemType);
li.appendChild(extracted);
cur.parentNode.replaceChild(li, cur);
newNodes.push(li);
first=false;
}
var node = this.createObservedElement(listType);
startNode = newNodes[0];
endNode = newNodes[newNodes.length -1];
sel.setStartBefore(startNode);
sel.setEndAfter(endNode);
sel.surroundContents(node);
if (wasCollapsed) { sel.selectNodeContents(li); sel.collapse(false); }
else sel.selectNode(node);
this.select(sel);
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}}

function RTZ_setListNumbering (ol, type) {
this.pushUndoState2();
ol.type=type;
ol.setAttribute('type', type);
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_hnSwitchNotoc (hn) {
if (hn.hasAttribute('data-notoc')) hn.removeAttribute('data-notoc');
else hn.setAttribute('data-notoc', true);
}

function RTZ_superBlockFormat (tagName, attrs) {
if (this.inlineOnly) return false;
this.pushUndoState2();
var sel = this.getSelection();
var wasCollapsed = sel.collapsed;
var realStartNode = sel.startContainer, realEndNode = sel.endContainer, realStartOffset = sel.startOffset, realEndOffset = sel.endOffset;
var startNode = sel.startContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'ul', 'ol', 'dl',]);
var endNode = sel.endContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'ul', 'ol', 'dl']);
sel.setStartBefore(startNode);
sel.setEndAfter(endNode);
var node = this.createObservedElement(tagName, attrs);
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
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_removeFormatting () {
if (this.inlineOnly) return false;
this.pushUndoState2();
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
var p = this.createObservedElement('p');
p.appendChild(frag2);
li.parentNode.insertBefore(p,li);
li.parentNode.removeChild(li);
}.bind(this));//each fragment child
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
var p = this.createObservedElement('p');
p.appendChild(frag2);
li.parentNode.insertBefore(p,li);
li.parentNode.removeChild(li);
}.bind(this));//each fragment child
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
var frag = this.createObservedElement('#fragment');
var p = frag.appendElement('p');
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
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_insertElement (tagName, attrs, text) {
this.pushUndoState2();
var sel = this.getSelection();
var node = document.createElement2(tagName, attrs, text);
sel.insertNode(node);
sel.setStartAfter(node);
sel.setEndAfter(node);
this.select(sel);
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
return false;
}

function RTZ_insertIcon (url, alt) {
this.pushUndoState2();
if (url.indexOf('\u007F')==0) url = url.substring(1, url.indexOf('\u007F',1));
var img = document.createElement2('img', {'alt':alt, 'src':url});
var sel = this.getSelection();
sel.insertNode(img);
sel.setStartAfter(img);
sel.setEndAfter(img);
sel.collapse(false);
this.select(sel);
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_insertIllustration (url, alt, caption, style) {
if (this.inlineOnly) return false;
this.pushUndoState2();
if (url.indexOf('\u007F')==0) url = url.substring(1, url.indexOf('\u007F',1));
var figure = this.createObservedElement('figure', {'class':style});
var img = figure.appendElement('img', {'alt':alt, 'src':url, 'width':'100%', 'height':'auto'});
var capt=null, captP=null;
if (caption){
capt = figure.appendElement('figcaption');
captP = capt.appendElement('p').appendText(caption);
}
var sel = this.getSelection();
var ancestor = sel.commonAncestorContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'dl', 'pre']);
ancestor.parentNode.insertBefore(figure, ancestor.nextSibling);
sel.selectNodeContents(captP? captP : figure);
sel.collapse(false);
this.select(sel);
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_insertMultimediaClip (type, urls, alt, caption, style) {
if (this.inlineOnly) return false;
this.pushUndoState2();
var figure = this.createObservedElement('figure', {'class':style});
var mm = RTZ_MMObject_embedding(urls[0].src, false);
if (mm) figure.appendChild(mm);
else {
mm = figure.appendElement(type, {'width':'100%', 'height':'auto', 'controls':'controls'});
for (var i=0; i<urls.length; i++) mm.appendElement('source', urls[i]);
mm.appendText(alt);
}
var capt = figure.appendElement('figcaption');
var captP = capt.appendElement('p').appendText(caption);
var sel = this.getSelection();
var ancestor = sel.commonAncestorContainer.findAncestor(['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'dl', 'pre']);
ancestor.parentNode.insertBefore(figure, ancestor.nextSibling);
sel.selectNodeContents(captP);
sel.collapse(false);
this.select(sel);
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_insertBox (type, position) {
if (this.inlineOnly) return false;
this.pushUndoState2();
var t = type.split('.');
var tagName = t[0];
var classNames = t[1] + ' ' + position;
var attrs = {'class':classNames};
if (this.styleData && this.styleData.htmlClassesToEpubTypes && this.styleData.htmlClassesToEpubTypes[t[1]]) attrs['epub:type'] = this.styleData.htmlClassesToEpubTypes[t[1]];
this.superBlockFormat(tagName, attrs);
}

function RTZ_insertFootnote () {
if (this.inlineOnly) return;
var sel = this.getSelection();
var inFootnotes = sel.commonAncestorContainer.queryAncestor('.footnotes');
var footnotes = document.querySelector('.footnotes');
if (!footnotes) footnotes = this.zone.appendElement('aside', {'class':'footnotes'});
var fnList = footnotes.querySelector('ol');
if (!fnList) fnList = footnotes.appendElement('ol', {'epub:type':'footnotes'});
if (inFootnotes) {
var li = sel.commonAncestorContainer.queryAncestor('li');
if (!li) return;
var backlink = li.querySelector('a');
if (!backlink) return;
var footnoteLink = this.zone.querySelector(backlink.href.substring(backlink.href.indexOf('#')));
if (!footnoteLink) return;
var sub = footnoteLink.queryAncestor('sub, sup');
if (!sub) return;
sel.setStartAfter(sub);
sel.setEndAfter(sub);
this.select(sel);
return;
}
var noteText = (sel.collapsed? null : sel.extractContents() );
var fnIndex = 1 + fnList.querySelectorAll('li').length;
var li = fnList.appendElement('li', {'epub:type':'footnote', 'id':'footnote'+fnIndex});
var backlink = li.appendElement('a', {href:'#footnoteref'+fnIndex}, '\u2191' );
if (noteText) li.appendChild(noteText);
var sub = document.createElement2('sub');
var link = sub.appendElement('a', {href:'#footnote'+fnIndex, 'id':'footnoteref'+fnIndex, 'epub:type':'noteref'}, fnIndex);
sel.insertNode(sub);
sel.setStartAfter(li.lastChild);
sel.setEndAfter(li.lastChild);
this.select(sel);
}

function RTZ_insertTable (nRows, nCols, captionText, thScheme, style) {
if (this.inlineOnly) return false;
this.pushUndoState2();
var table = this.createObservedElement('table', {'class':style, 'data-th':thScheme});
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
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_tableInsertRow (table, curCell, clear) {
this.pushUndoState2();
var curRow = curCell.parentNode;
var firstRow = table.querySelector('tr');
var secondRow = firstRow.nextElementSibling;
var newRow = secondRow.cloneNode(true);
if (clear) newRow.$('td,th').each(function(cell){ cell.innerHTML='&nbsp;'; });
curRow.parentNode.insertBefore(newRow, curRow.nextElementSibling);
var sel = this.getSelection();
sel.selectNodeContents(newRow.querySelector('th,td')); // Select the first new cell
this.select(sel);
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_tableInsertColumn (table, curCell, clear) {
this.pushUndoState2();
var curRow = curCell.parentNode;
var colIndex = Array.prototype.indexOf.call(curRow.$('th,td'), curCell);
var newSelectedCell = null;
table.$('tr').each(function(tr){
var cells = tr.$('th,td');
var secondCell = cells[1], theCell = cells[colIndex], newCell = secondCell.cloneNode(true);
if (clear) newCell.innerHTML='&nbsp;';
tr.insertBefore(newCell, theCell.nextElementSibling);
if (curCell==theCell) newSelectedCell= newCell;
});//
var sel = this.getSelection();
sel.selectNodeContents(newSelectedCell);
this.select(sel);
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_tableDeleteRow (table, curCell) {
this.pushUndoState2();
var curRow = curCell.parentNode;
var firstRow = table.querySelector('tr');
if (firstRow==curRow) return; // The first row can contain headers, we certainly don't want to delete it
curRow.parentNode.removeChild(curRow);
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_tableDeleteColumn (table, curCell) {
this.pushUndoState2();
var curRow = curCell.parentNode;
var colIndex = Array.prototype.indexOf.call(curRow.$('th,td'), curCell);
if (colIndex<=0) return; // The first column can contain headers, we certainly don't want to delete it
table.$('tr').each(function(tr){
var cells = tr.$('th,td');
var theCell = cells[colIndex];
tr.removeChild(theCell);
});//
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
}

function RTZ_hnChangeTocLabelDialog (hn) {
var toclabel = hn.getAttribute('data-toclabel') || '';
var _this = this;
DialogBox(msgs.HnChangeTocLabel, [
{label:msgs.HnChangeTocLabel, name:'toclbl', value:toclabel},
], function(){ 
toclabel = this.elements.toclbl.value;
if (toclabel) hn.setAttribute('data-toclabel', toclabel);
else hn.removeAttribute('data-toclabel');
_this.zone.focus();
});//DialogBox
}

function RTZ_insertAbbrDialog () {
if (!this.inlineFormat('abbr', false, null, true)) {
this.inlineFormat('abbr', false);
return;
}
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

function RTZ_modifyAbbrDialog (abbr) {
var abbrTitle = abbr.getAttribute('title') || '';
var _this = this;
DialogBox(msgs.Abbreviation, [
{label:msgs.AbbrTitle, name:'abbrtitle', value:abbrTitle},
], function(){ 
abbr.setAttribute('title', this.elements.abbrtitle.value);
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
{label:msgs.IconDeco, name:'deco', type:'checkbox'},
], function(){ 
_this.select(sel);
_this.insertIcon(this.elements.url.value, this.elements.alt.value, this.elements.deco.checked);
_this.zone.focus();
});//DialogBox
}

function RTZ_modifyIconDialog (img) {
var src = img.getAttribute('src') || '#';
var alt = img.getAttribute('alt') || '';
var _this = this;
DialogBox(msgs.Icon, [
{label:msgs.IconURL, name:'url', value:src},
{label:msgs.IconAlt, name:'alt', value:alt},
{label:msgs.IconDeco, name:'deco', type:'checkbox', checked:img.hasAttribute('data-decorative')},
], function(){ 
_this.pushUndoState2();
img.setAttribute('alt', this.elements.alt.value);
img.setAttribute('src', this.elements.url.value);
if (this.elements.deco.checked) img.setAttribute('data-decorative', true);
else img.removeAttribute('data-decorative');
setTimeout(function(){this.pushUndoState2()}.bind(_this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
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
{label:msgs.IlluCapt, name:'caption'},
{label:msgs.IlluStyle, name:'istyle', type:'select', values:this.positionalStyles},
{label:msgs.IconDeco, name:'deco', type:'checkbox'},
], function(){ 
_this.select(sel);
_this.insertIllustration(this.elements.url.value, this.elements.alt.value, this.elements.caption.value, this.elements.istyle.value, this.elements.deco.checked);
_this.zone.focus();
});//DialogBox
}

function RTZ_modifyIllustrationDialog (figure) {
var img = figure.querySelector('img');
if (!img) return;
var caption = figure.querySelector('figcaption');
var captionText = caption? caption.textContent : '';
var altText = img.getAttribute('alt') || '';
var src = img.getAttribute('src') || '';
var curclass = figure.getAttribute('class') || '';
var _this = this;
DialogBox(msgs.Illustration, [
{label:msgs.IlluURL, name:'url', value:src},
{label:msgs.IlluAlt, name:'alt', value:altText},
{label:msgs.IlluCapt, name:'caption', value:captionText},
{label:msgs.IlluStyle, name:'istyle', type:'select', value:curclass, values:this.positionalStyles},
{label:msgs.IconDeco, name:'deco', type:'checkbox', checked:img.hasAttribute('data-decorative')},
], function(){
_this.pushUndoState2();
var newCaptionText = this.elements.caption.value; 
img.setAttribute('src', this.elements.url.value);
img.setAttribute('alt', this.elements.alt.value);
figure.setAttribute('class', this.elements.istyle.value);
if (newCaptionText!=captionText){
if (!caption) caption = figure.appendElement('figcaption');
caption.innerHTML = '';
if (newCaptionText) caption.appendElement('p').appendText(newCaptionText);
else caption.parentNode.removeChild(caption);
}
if (this.elements.deco.checked) img.setAttribute('data-decorative', true);
else img.removeAttribute('data-decorative');
setTimeout(function(){this.pushUndoState2()}.bind(_this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
_this.zone.focus();
});//DialogBox
}

function RTZ_MMObject_embedding (url, onlyCheck) {
var m;
if ( 
(m = url.match(/^https?:\/\/(?:www\.)youtube\.com\/watch\?v=([-a-zA-Z_0-9]+)/))
|| (m = url.match(/^https?:\/\/youtu\.be\/([-a-zA-Z_0-9]+)/))
) { // Youtube 
url = '//www.youtube.com/embed/' + m[1];
if (onlyCheck) return true;
else {
var iframe = document.createElement2('iframe', {src:url});
var wrapper = document.createElement2('div', {'class':'videoWrapper'});
wrapper.appendChild(iframe);
return wrapper;
}}//end youtube
// Other recognized video websites should go here
return null;
}

function RTZ_MMObject_parseSources (urlsText) {
var type='undefined', utab = urlsText.split(/[\r\n\t,; ]+/g), urls = [];
for (var i=0; i<utab.length; i++) {
var url = utab[i];
var extidx = url.lastIndexOf('.');
if (extidx<0) continue;
var ext = url.substring(extidx+1).toLowerCase();
switch(ext){
case 'mp3': 
ext = 'mpeg';
case 'ogg': case 'wav':
type='audio'; break;
case 'ogv': 
ext='ogg';
case 'mp4': case 'webm':
type='video'; break;
default: 
if (/^https?:.*$/.test(url)) { ext='octetstream'; type='application'; }
else continue;
}
urls.push({'type':type+'/'+ext, src:url});
}
return urls;
}

function RTZ_insertMultimediaClipDialog (defUrl) {
if (this.inlineOnly) return false;
defUrl = defUrl || '';
var sel = this.getSelection();
var _this = this;
DialogBox(msgs.InsertMM, [
{type:'textarea', label:msgs.MMURLs, name:'urls', value:defUrl},
{label:msgs.MMAlt, name:'alt'},
{label:msgs.MMCapt, name:'caption'},
{label:msgs.MMStyle, name:'istyle', type:'select', values:this.positionalStyles},
], function(){ 
_this.select(sel);
var urls = RTZ_MMObject_parseSources(this.elements.urls.value + '');
var type = urls[0].type.substring(0,urls[0].type.indexOf('/'));
_this.insertMultimediaClip(type, urls, this.elements.alt.value, this.elements.caption.value, this.elements.istyle.value);
_this.zone.focus();
});//DialogBox
}

function RTZ_modifyMultimediaClipDialog (figure) {
var mm = figure.querySelector('video, audio, iframe');
if (!mm) return;
var isiframe = mm.tagName.toLowerCase()=='iframe';
var caption = figure.querySelector('figcaption') || figure.appendElement('figcaption');
var captionText = caption.textContent;
var altText = mm.textContent || '';
var curclass = figure.getAttribute('class') || '';
var srcs = [mm.getAttribute('src')] || mm.$('source, track').reduce(function(ar, cur, idx, lst){ ar.push(cur.getAttribute('src')); return ar; }, []);
var src = srcs.join(', ');
var _this = this;
DialogBox(msgs.MMObject, [
{type:'textarea', label:msgs.MMURLs, name:'urls', value:src},
{label:msgs.MMAlt, name:'alt', value:altText},
{label:msgs.MMCapt, name:'caption', value:captionText},
{label:msgs.MMStyle, name:'istyle', type:'select', value:curclass, values:this.positionalStyles},
], function(){
_this.pushUndoState2();
var newCaptionText = this.elements.caption.value; 
figure.setAttribute('class', this.elements.istyle.value);
if (newCaptionText!=captionText){
caption.innerHTML = '';
caption.appendElement('p').appendText(newCaptionText);
}
if (isiframe) mm.setAttribute('src', this.elements.urls.value);
else {
mm.innerHTML = '';
var urls = RTZ_MMObject_parseSources(this.elements.urls.value + '');
for (var i=0; i<urls.length; i++) mm.appendElement('source', urls[i]);
mm.appendText(this.elements.alt.value);
}
setTimeout(function(){this.pushUndoState2()}.bind(_this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
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

function RTZ_modifyTableDialog (table) {
var tablePosition = table.className || '';
var caption = table.querySelector('caption');
var captionText = caption? caption.textContent : '';
var _this = this;
DialogBox(msgs.Table, [
{name:'captionText', label:msgs.TableCaption, value:captionText},
{type:'select', name:'tstyle', label:msgs.TableStyle, value:tablePosition, values:this.positionalStyles},
], function(){
_this.pushUndoState2();
var newCaptionText = this.elements.captionText.value;
table.setAttribute('class', this.elements.tstyle.value);
if (newCaptionText!=captionText) {
if (!caption) caption = table.appendElement('caption');
caption.innerHTML = '';
caption.appendText(newCaptionText);
}
setTimeout(function(){this.pushUndoState2()}.bind(_this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
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

function RTZ_modifyBoxDialog (box) {
if (this.inlineOnly) return false;
var _this = this;
var classes = box.className.split(' ');
var boxClass = classes.length>0? classes[0] || '' : '';
var boxPosition = classes.length>1? classes[1] || '' : '';
var boxType = box.tagName.toLowerCase() + '.' + boxClass;
DialogBox(msgs.Box, [
{label:msgs.IBoxType, name:'type', type:'select', value:boxType, values:this.boxTypes},
{label:msgs.IBoxPosition, name:'position', type:'select', value:boxPosition, values:this.positionalStyles},
], function(){
try {
var sel = _this.getSelection();
sel.selectNodeContents(box);
var frag = sel.extractContents(), first=frag.firstChild, last=frag.lastChild;
box.parentNode.insertBefore(frag, box);
box.parentNode.removeChild(box);
sel.setStartBefore(first.getFirstTextNode());
sel.setEndAfter(last.getLastTextNode());
_this.select(sel);
_this.insertBox(this.elements.type.value, this.elements.position.value);
_this.zone.focus();
} catch(e){ }
});//Dialog box
}

function RTZ_quickUploadDialog () {
if (!window.FormData) { MessageBox(msgs.FeatureNotAvailT, msgs.FeatureNotAvail,[msgs.OK]); return; }
DialogBox(msgs.AddFiles, [
{name:'upload', label:msgs.Upload, type:'file', multiple:'multiple'}
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

function RTZ_cleanHTML (frag, inlineContext) {
if (typeof(inlineContext)=='undefined') inlineContext = this.inlineOnly;
var fragWasNull = !frag, curSel=null, sel = document.createRange();
if (fragWasNull) {
cursel = this.getSelection() || document.createRange();
var startNode = cursel.startContainer, endNode = cursel.endContainer, startOf = cursel.startOffset, endOf = cursel.endOffset;
sel.selectNodeContents(this.zone);
var frag = sel.extractContents();
}
this.cleanHTMLElement(sel, frag, inlineContext);
if (!inlineContext) cleanHTML2(sel, frag);
if (!inlineContext) frag.querySelectorAll('div, aside, section, header, footer, figure').each(cleanHTML2.bind(this, sel));
if (fragWasNull) {
this.zone.appendChild(frag);
try {
cursel.setStart(startNode, startOf);
cursel.setEnd(endNode, endOf);
this.select(cursel);
} catch(e) {} // Just in case the previous selection is no longer in the document
}
}

function RTZ_cleanHTMLElement (sel, o, inlineContext) {
var allowedElements = 'p h1 h2 h3 h4 h5 h6 ul ol li dl dt dd table tbody thead tfoot tr th td caption br a b i q s strong em abbr sup sub ins del code pre hr img audio video source track iframe object param section aside header footer figure figcaption mark var samp kbd dfn cite span div'.split(' ');
var trimableElements = 'p h1 h2 h3 h4 h5 h6 li dt dd th td caption pre div'.split(' ');
var ignoreElements = ['math', 'script'];
var allowedEmptyElements = ['br', 'img', 'hr', 'source', 'track', 'iframe'];
var allowedAttrs = {
'#':[ 'id', 'class', 'epub:type', 'xmlns:epub', 'role', 'aria-label', 'aria-level', 'aria-describedby' ],
a:['href', 'rel', 'rev', 'type', 'hreflang', 'title'],
abbr:['title'],
iframe:['src', 'width', 'height'],
img:['src', 'width', 'height', 'alt', 'data-decorative'],
ol:['type', 'start'],
source:['src', 'type'],
video:['width', 'height', 'controls', 'src'],
audio:['width', 'height', 'controls', 'src'],
track:['kind', 'srclang', 'src', 'label'],
};
var remove = false, rename=null, surround=null;
if (o.nodeType==1 && ignoreElements.indexOf(o.nodeName.toLowerCase())>=0) return; // Ignored type of element: don't go further
if (o.nodeType==11 && !inlineContext) { // document fragment
// Look for blocks incorrectly present within a big <p>; this can happen when pasting 
var blocks = o.querySelectorAll('ul, ol, dl, div, pre, p, h1, h2, h3, h4, h5, h6, aside, section, header, footer, figure');
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
if (o.parentNode.nodeType==1 && !inlineContext && ['div', 'aside', 'section', 'header', 'footer', 'figure', 'figcaption'].indexOf(o.parentNode.nodeName.toLowerCase())>=0) surround='p'; // idem
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

function RTZ_cut () {
this.pushUndoState2();
return true;
}

function RTZ_preparePaste (rtz) {
var sel = this.getSelection();
var sc = sel.startContainer, ec = sel.endContainer, so = sel.startOffset, eo = sel.endOffset;
var bcr = this.zone.getBoundingClientRect();
var div = document.createElement2('div', {contenteditable:true, tabindex:0, style:"position: absolute; z-index: -100; width: 1px; overflow: hidden; left: "+bcr.left+"px; top: "+bcr.top+"px;"}, '\u00A0');
div.onpaste = RTZ_paste.bind(this);
document.querySelector('body').appendChild(div);
div.focus();
sel.selectNodeContents(div);
this.select(sel);
setTimeout(function(){ 
sel.selectNodeContents(div);
var frag = sel.extractContents();
var pastedSomething = (frag.childNodes.length>0 && (frag.firstChild.nodeType!=3 || frag.firstChild.data.trim() )) ;
var inlineContext = !frag.querySelector('p, pre, h1, h2, h3, h4, h5, h6, ul, ol, li, dl, dd, dt, table, tr, td, th, div, header, footer, section, aside, figure');
div.parentNode.removeChild(div);
sel.setStart(sc,so);
sel.setEnd(ec,eo);
this.zone.focus();
this.select(sel);
if (!pastedSomething) return; 
this.cleanHTML(frag, inlineContext);
if (!inlineContext && sel.commonAncestorContainer.nodeType==3 && sel.commonAncestorContainer.parentNode.nodeName.toLowerCase()=='p') { // We are in the middle of a paragraph, we perhaps need to split it
if (sel.endOffset >= sel.endContainer.length) { // At the end of the paragraph, don't split it it is useless; just place the cursor after it
var node = sel.commonAncestorContainer.parentNode;
sel.setStartAfter(node);
sel.setEndAfter(node);
sel.collapse(false);
}
else { // We need to split the paragraph
var p = sel.commonAncestorContainer.parentNode;
var textNode = sel.endContainer.splitText(sel.endOffset);
var newP = p.cloneNode(false);
p.parentNode.insertBefore(newP, p.nextSibling);
newP.appendChild(textNode);
sel.setStartAfter(p);
sel.setEndAfter(p);
sel.collapse(false);
}}
else if (!inlineContext) { // We are pasting a block but aren't in the middle of a paragraph; we need to ensure that the cursor is placed at the end of the block before inserting the pasted contents, so that we don't produce completely incoherent HTML such as <p> inside <p>
var node = sel.commonAncestorContainer.queryAncestor('p, pre, h1, h2, h3, h4, h5, h6, ul, ol, li, dl, dd, dt, table, div, header, footer, section, aside, figure');
sel.setStartAfter(node);
sel.setEndAfter(node);
sel.collapse(false);
if (node.childNodes.length<=0 || (node.childNodes.length==1 && node.firstChild.nodeType==3 && !node.firstChild.data.trim() )) node.parentNode.removeChild(node); // Remove a possible empty paragraph
}
var ltn = frag.getLastTextNode();
sel.insertNode(frag);
if (ltn) { sel.selectNodeContents(ltn); sel.collapse(false); this.select(sel); }
setTimeout(function(){this.pushUndoState2();}.bind(this),1);
}.bind(this),1);
}

function RTZ_paste (e) {
this.pushUndoState2();
if (e && e.clipboardData && e.clipboardData.files && e.clipboardData.files.length>0) { // Paste some files
RTZ_uploadFiles(e.clipboardData.files, RTZ_dropFinishedWithFiles.bind(this));
if (e.preventDefault) e.preventDefault();
return false;
}
else if (e && e.clipboardData && e.clipboardData.getData) {
var result=false, text = null;
try { text = e.clipboardData.getData('Text'); } catch(ex){} // a weird security error may occur when retriving the text present in the clipboard, so we need to do it inside try...catch
if (text && /^https?:/ .test(text)) result = RTZ_dropFinishedWithFiles.call(this, text.trim() ); // We are pasting an URL; we can directly make a link out of it
else if (text && text.startsWith("\u007F")) result = RTZ_dropFinishedWithFiles.call(this, text.split('\u007F')[1].trim(), text.split('\u007F')[3].trim() ); // We are pasting an element from the file/spine/toc view; this could be a link or an image
if (result) { if (e.preventDefault) e.preventDefault(); return false; } // IF the paste opration ahs been handled in one of the case above, cancel the normal behavior
}
return true; // the default paste behavior will occur
}

function RTZ_save () {
if (window.readOnly) return;
this.cleanHTML();
if (this.onsave) this.onsave();
var saveBtn = document.querySelector('button[data-action=save]');
saveBtn.addClass('disabled');
saveBtn.setAttribute('aria-disabled', true);
this.saveBtn = saveBtn;
window.changed=false;
}

function RTZ_defaultSave (code, sync) {
var data = code || this.zone.innerHTML;
var url = encodeURI( window.actionUrl.replace('@@', 'save') );
ajax('POST', url, 'content='+encodeURIComponent(data), function(e){
debug(e, true);
}, function(re,xml){debug('Save failed'+re+xml.responseText);}, !sync);
};

function RTZ_contextmenu (e) {
e = e || window.event;
if (e.ctrlKey || e.shiftKey) return true; // Allow original OS/browser context menu if shift and/or Ctrl are held down; 
var items = [];
var sel = this.getSelection(), ca = sel.commonAncestorContainer;
var br = ca.getBoundingClientRect? ca.getBoundingClientRect() : {};
var x = e.pageX || e.clientX || br.left;
var y = e.pageY || e.clientY || br.top;
var ol = ca.findAncestor(['ol']);
var figure = ca.findAncestor(['figure']);
var box = ca.findAncestor(['aside', 'section', 'footer', 'header', 'div']);
var img = ca.parentNode.querySelector('img');
var mm = ca.parentNode.querySelector('video, audio');
var iframe = ca.parentNode.querySelector('iframe');
var hn = ca.findAncestor(['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
var table = ca.findAncestor(['table']);
var td = ca.findAncestor(['td', 'th']);
var abbr = ca.findAncestor(['abbr']);
var link = ca.findAncestor(['a']);
if (link) items.merge([msgs.LinkModify, RTZ_modifyLinkDialog.bind(this,link)]);
if (abbr) items.merge([msgs.AbbrModify, RTZ_modifyAbbrDialog.bind(this,abbr)]);
if (ol && ol.isInside(this.zone)){
var types = {NumberingArabic:'1', NumberingLowerAlpha:'a', NumberingUpperAlpha:'A', NumberingLowerRoman:'i', NumberingUpperRoman:'I'};
var curtype = ol.getAttribute('type') || '1';
for (var t in types) {
items.merge([{text:msgs[t], type:'menuitemcheckbox', checked:curtype==types[t]}, RTZ_setListNumbering.bind(this, ol, types[t]) ]);
}}
else if (hn && hn.isInside(this.zone)) {
var level = parseInt(hn.nodeName.substring(1)), toclabel = hn.getAttribute('data-toclabel');
for (var i=1; i<=6; i++) items.merge([{text:msgs["Heading"+i], type:'menuitemradio', checked:level==i}, RTZ_simpleBlockFormat.bind(this, 'h'+i, {role:'heading', 'aria-level':i, 'data-toclabel':toclabel})]);
items.merge([{text:msgs.HnSwitchNoToc, type:'menuitemcheckbox', checked:hn.hasAttribute('data-notoc')}, RTZ_hnSwitchNotoc.bind(this,hn)]);
items.merge([msgs.HnIChangeTocLabel + (toclabel? " ["+toclabel+"]" : ""), RTZ_hnChangeTocLabelDialog.bind(this,hn)]);
}
if (figure && iframe) items.merge([msgs.MMEModify, RTZ_modifyMultimediaClipDialog.bind(this,figure)]);
if (figure && mm) items.merge([msgs.MMModify, RTZ_modifyMultimediaClipDialog.bind(this,figure)]);
else if (figure && img) items.merge([msgs.IlluModify, RTZ_modifyIllustrationDialog.bind(this,figure)]);
else if (img) items.merge([msgs.IconModify, RTZ_modifyIconDialog.bind(this,img)]);
if (td && table && td.isInside(this.zone)) {
var nCols = td.parentNode.$('th,td').length;
var nRows = table.$('tr').length;
items.merge([msgs.TableInsertRow, RTZ_tableInsertRow.bind(this,table,td,true)]);
items.merge([msgs.TableInsertCol, RTZ_tableInsertColumn.bind(this,table,td,true)]);
items.merge([msgs.TableDuplRow, RTZ_tableInsertRow.bind(this,table,td,false)]);
items.merge([msgs.TableDuplCol, RTZ_tableInsertColumn.bind(this,table,td,false)]);
if (nRows>2) items.merge([msgs.TableDeleteRow, RTZ_tableDeleteRow.bind(this,table,td)]);
if (nCols>2) items.merge([msgs.TableDeleteCol, RTZ_tableDeleteColumn.bind(this,table,td)]);
}
if (table && table.isInside(this.zone)) items.merge([msgs.TableModify, RTZ_modifyTableDialog.bind(this,table)]);
if (box && box.isInside(this.zone)) items.merge([msgs.BoxModify, RTZ_modifyBoxDialog.bind(this,box)]);
if (this.oncontextmenu) this.oncontextmenu(items,sel);
if (items.length<=0) return true; // Show default OS context menu in case there is no useful option to present
items.merge([msgs.Cancel,null]);
Menu_show(items, this.zone, x+7, y+7);
if (e.preventDefault) e.preventDefault();
return false;
}

function RTZ_onfocus () {
this.pushUndoState2();
}

function RTZ_onblur () {
this.pushUndoState2();
}

function RTZ_onDragOver (e) {
if (e&&e.preventDefault) e.preventDefault();
}

function RTZ_onDrop (e) {
if (e.clientX && e.clientY) this.moveCaretToPoint(e);
if (!e.dataTransfer) return;
if (e.preventDefault) e.preventDefault();
if (e.dataTransfer.files && e.dataTransfer.files.length>0) RTZ_uploadFiles(e.dataTransfer.files, RTZ_dropFinishedWithFiles.bind(this));
else if (e.dataTransfer.getData) {
var text = null;
try { text = e.dataTransfer.getData('Text'); } catch(ex){}
if (!text) return;
if (/^https?:/ .test(text)) RTZ_dropFinishedWithFiles.call(this, text.trim() );
else if (text.startsWith("\u007F")) RTZ_dropFinishedWithFiles.call(this, text.split('\u007F')[1].trim(), text.split('\u007F')[3].trim() );
else this.insertElement(null, null, text);
}}

function RTZ_dropFinishedWithFiles (url, text) {
this.pushUndoState2();
var result = false;
if (!this.inlineOnly && url && RTZ_MMObject_embedding(url,true)) { // Some embedded object can be constructed from this url
this.insertMultimediaClipDialog(url);
result = true;
}
else if (url && /\.(?:png|gif|jpg|jpeg|svg)$/i .test(url)) { // Probably an image 
if (this.inlineOnly) this.insertIconDialog(url);
else this.insertIllustrationDialog(url);
result = true;
}
else if (!this.inlineOnly && url && /\.(?:avi|mp3|mp4|ogg|ogv|webm|wav)$/i .test(url)) { // Probably a multimedia clip (audio/video)
this.insertMultimediaClipDialog(url);
result = true;
}
else if (url) { // URL but of unknown type, let's make a link by default
this.insertElement('a', {href:url}, text || url);
result = true;
}
setTimeout(function(){this.pushUndoState2()}.bind(this),1); // Remember that MutationObserver is asynchrone; delay the call so that the mutation list is effectively filled with the modifications we have just made
return result;
}

function RTZ_uploadFiles (files, okFunc, forcedir) {
if (!files || files.length<=0) return; // empty or upload not supported
var url = window.location.href;
var data = new FormData();
data.append('addfiles', '1');
data.append('noredir', '1');
if (forcedir) data.append('forcedir', '1');
data.append('id', '');
data.append('fileName', '');
for (var i=0; i<files.length; i++) data.append('uploads[]', files[i], files[i].name);
ajax('POST', url, data, function(re){
debug(re);
if (okFunc && re.startsWith('Uploaded: ')) okFunc(re.substring(10, re.indexOf('\r\n')).trim());
}, function(){alert('Upload failed');});
}

function RTZ_undoMutationList (ml) {
this.stopRecordDOMChanges();
for (var ii=ml.length -1; ii>=0; ii--) {
var rec = ml[ii], tmp;
switch(rec.type){
case 'characterData':
tmp = rec.target.data;
rec.target.data = rec.oldValue;
rec.oldValue2 = tmp; // rec.oldValue seem to be read only so we need another property
break;
case 'attribute':
tmp = rec.target.getAttribute(rec.attributeName);
rec.target.setAttribute(rec.attributeName, rec.oldValue);
rec.oldValue2 = tmp; // idem as above
break;
case 'childList':
if (rec.removedNodes) for (var i=0; i<rec.removedNodes.length; i++) rec.target.insertBefore(rec.removedNodes[i], rec.nextSibling);
if (rec.addedNodes) for (var i=0; i<rec.addedNodes.length; i++) rec.target.removeChild(rec.addedNodes[i]);
break;
}}
this.startRecordDOMChanges();
}

function RTZ_redoMutationList (ml) {
this.stopRecordDOMChanges();
for (var ii=0; ii<ml.length; ii++) {
var rec = ml[ii], tmp;
switch(rec.type){
case 'characterData':
tmp = rec.target.data;
rec.target.data = rec.oldValue2;
rec.oldValue2 = tmp;
break;
case 'attribute':
tmp = rec.target.getAttribute(rec.attributeName);
rec.target.setAttribute(rec.attributeName, rec.oldValue2);
rec.oldValue2 = tmp;
break;
case 'childList':
if (rec.addedNodes) for (var i=0; i<rec.addedNodes.length; i++) rec.target.insertBefore(rec.addedNodes[i], rec.nextSibling);
if (rec.removedNodes) for (var i=0; i<rec.removedNodes.length; i++) rec.target.removeChild(rec.removedNodes[i]);
break;
}}
this.startRecordDOMChanges();
}

	function RTZ_createObservedElement (tagName, attrs) {
var node;
if (tagName=='#fragment') node = document.createDocumentFragment();
else node = document.createElement2(tagName, attrs);
if (this.mutationObserver) this.mutationObserver.observe(node, {childList:true, attributes:true, attributeOldValue:true, characterData:true, characterDataOldValue:true, subtree:true});
return node;
}

function RTZ_startRecordDOMChanges () {
if (!this.mutationObserver) return;
this.mutationObserver.observe(this.zone, {childList:true, attributes:true, attributeOldValue:true, characterData:true, characterDataOldValue:true, subtree:true});
}

function RTZ_stopRecordDOMChanges () {
if (!this.mutationObserver) return;
this.mutationObserver.disconnect();
}

function RTZ_toString () {
return "RTZ"+JSON.stringify(this);
}

function RTZ_debug_updateHTMLPreview () {
if (!this.htmlCodePreview) return;
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

function RTZ_moveCaretToPoint (e) {
try {
if (document.caretPositionFromPoint)  { // W3C standart as of 2016-02-26  
var r = document.caretPositionFromPoint(e.clientX, e.clientY);
var sel = document.createRange();
sel.setStart(r.offsetNode, r.offset);
sel.setEnd(r.offsetNode, r.offset);
sel.collapse();
this.select(sel);
return true;
}
else if (document.caretRangeFromPoint) { // Older W3C standard
var r = document.caretRangeFromPoint(e.clientX, e.clientY);
var sel = document.createRange();
sel.setStart(r.startContainer, r.startOffset);
sel.setEnd(r.startContainer, r.startOffset);
sel.collapse();
this.select(sel);
return true;
}
else if (e.rangeParent) { // Firefox alternative method
var offset = e.rangeOffset || 0;
var sel = document.createRange();
sel.setStart(e.rangeParent, offset);
sel.setEnd(e.rangeParent, offset);
this.select(sel);
return true;
}
else if (document.body && document.body.createTextRange) { // Internet explorer
var tr = document.body.createTextRange();
tr.moveToPoint(e.clientX, e.clientY);
tr.select();
return true;
}
} catch(ex) { debug(ex.message); }
return false;
}

function RTZ_findClonedNode (node, oldRoot, newRoot) {
var path = [];
while(node!=oldRoot){
var parent = node.parentNode;
var idx = parent.indexOf(node);
path.push(idx);
node = parent;
}
node = newRoot;
while(path.length>0) {
var idx = path.pop();
node = node.childNodes[idx];
}
return node;
}

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
$('.editor, *[contenteditable=true]').each(function(e){
var toolbarId = e.getAttribute('data-toolbar');
var toolbar = toolbarId? document.getElementById(toolbarId) : null;
e.setAttribute('tabindex',0);
var rtz = new RTZ( e, toolbar);
rtz.debug = DEBUG && e.tagName.toLowerCase()=='div';
rtz.init();
if (!rtz.onsave) rtz.onsave = RTZ_defaultSave;
});//each .editor/contenteditable
$('#topPanel a[href], #leftPanel a[href], #pageTabs a[href]').each(function(a){
if (a.textContent.trim().length<=1) return;
if (a.hasAttribute('data-nosavecfm')) return;
var oldonclick  = a.onclick;
if (a.hasAttribute('data-ajax')) a.onclick = function(e){
if (oldonclick) oldonclick.call(a,e);
return LeftPanelAJAXLoad(this.href);
};
else a.onclick = function(e){
if (!window.changed) return true;
MessageBox(msgs.Save, msgs.SaveChangesDlg, [msgs.Yes, msgs.No], function(btnIdx){ 
if (btnIdx==0) window.rtzs[0].onsave(null,true);
if (oldonclick) oldonclick.call(a,e);
window.location.href = a.href;
});
return false;
};});//each link
});

//alert('RTZ13 loaded');