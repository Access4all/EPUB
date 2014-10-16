function $css (selector, flags) {
var defstylesheet = null;
if (document.styleSheets) for (var j=0; j<document.styleSheets.length; j++) {
var stylesheet = document.styleSheets[j];
var rules = null;
try { rules = stylesheet.cssRules || stylesheet.rules; } catch(e){} // Against firefox security error
if (!rules) continue;
if (!defstylesheet) defstylesheet=stylesheet;
for (var i=0; i<rules.length; i++) {
var rule = rules[i];
if (rule.selectorText!=selector) continue;
if (flags&&(flags&1)) {
if (rules.deleteRule) rules.deleteRule(i);
else if (rules.removeRule) rules.removeRule(i);
}
else return rule;
}}
if (flags&&(flags&2)) {
var stylesheet = defstylesheet;
if (stylesheet.addRule) stylesheet.addRule(selector, null, 0);
else if (stylesheet.insertRule) stylesheet.insertRule(selector + ' { }', 0);
var rules = stylesheet.cssRules || stylesheet.rules;
return (rules[0].selectorText==selector? rules[0] : null);
}
return null;
}
$css.DELETE = 1;
$css.CREATE = 2;

function  dashedNameToCamelCaseName (key) {
if (key=='float') return 'cssFloat';
return key.replace(/-(\w)/g, function(_,l){ return l.toUpperCase(); })
}

function camelCaseNameToDashedName (key) {
if (key=='cssFloat') return 'float';
return key.replace(/[A-Z]/g, function(m){ return '-'+m.toLowerCase(); });
}

function StyleEditor (form) {
this.form = form;
this.curStyle = null;
this.parseCssText = STE_parseCssText;
this.init = STE_init;
this.updateUI = STE_updateUI;
this.updateValue = STE_updateValue;
this.saveTemplate = STE_saveTemplate;
this.newStyleDialog = STE_newStyleDialog;
this.createNewStyle = STE_createNewStyle;
this.populateStyleSelect = STE_populateStyleSelect;
}

function STE_init () {
var _this = this;
this.form.elements.saveTplBtn.onclick = STE_saveTemplate.bind(this);
this.form.elements.exportStyleBtn.onclick = STE_exportTemplate.bind(this);
this.form.elements.importStyleBtn.onclick = STE_importTemplate.bind(this);
this.form.elements.newStyleBtn.onclick = STE_newStyleDialog.bind(this);
this.form.styleSelect.onchange = function(){ _this.updateUI(this.value); };
this.form.elements.font.onchange = function(){ _this.updateValue('fontFamily', this.value, 'default'); };
this.form.elements.fontsize.onchange = function(){ _this.updateValue('fontSize', ((this.value/100) || 1) + 'em', '1em'); };
this.form.elements.fontcolor.onchange = function(){ _this.updateValue('color', this.value); };
this.form.elements.fontstyle.onclick = function() { _this.updateValue('fontStyle', this.checked?'italic':'normal', 'normal'); };
this.form.elements.fontweight.onclick = function(){ _this.updateValue('fontWeight', this.checked?'bold':'normal', 'normal'); };
this.form.elements.textalign.onchange = function(){ _this.updateValue('textAlign', this.value, 'initial'); };
this.form.elements.bgcolor.onchange = function(){ _this.updateValue('backgroundColor', this.value, 'transparent'); };
this.form.elements.width.onchange = function(){ _this.updateValue('width', this.value+0<=0? 'auto' : parseInt(this.value)+'%', 'auto'); };
this.form.elements.cssFloat.onchange = function(){ _this.updateValue('cssFloat', this.value, 'none'); };
this.populateStyleSelect();
}

function STE_populateStyleSelect () {
var ss = this.form.elements.styleSelect; 
if (document.styleSheets) for (var j=0; j<document.styleSheets.length; j++) {
var stylesheet = document.styleSheets[j];
var rules = null;
try { rules = stylesheet.cssRules || stylesheet.rules; } catch(e){} // Against firefox security error
if (!rules) continue;
for (var i=0; i<rules.length; i++) {
var rule = rules[i];
if (!/^#editor / .test(rule.selectorText)) continue;
var selector = rule.selectorText.substring(8).trim();
if(!/^\w*\.\w+$/ .test(selector)) continue; // Simple tags, i.e. p, h1, etc. are already on the list
ss.appendElement('option', {value:selector}).appendText(selector);
}}
}

function STE_parseCssText (text) {
var o = {};
// Let's just use replace to iterate through matches, instead of making a loop!
text.replace(/([-a-zA-Z_0-9]+)\s*:\s*(.*?);/g, function(_, key, value){
key = dashedNameToCamelCaseName(key);
o[key]=value;
});
return o;
}

function STE_updateValue (property, value, def) {
if (!this.curStyle) return;
if (value==def) this.curStyle.style.removeProperty(camelCaseNameToDashedName(property));
else this.curStyle.style[property] = value;
}

function STE_updateUI (selection) {
var selector = ('#editor '+selection).trim();
if (selection=='#') this.curStyle=null;
else this.curStyle = $css(selector, $css.CREATE);
if (!this.curStyle) return;
var style = this.curStyle;
var cs = this.parseCssText(style.cssText);
this.form.elements.font.value = style.fontFamily || cs.fontFamily || 'default';
this.form.elements.fontsize.value = Math.round(100 * parseFloat(style.fontSize || cs.fontSize)) || 100;
this.form.elements.fontcolor.value = style.color || cs.color || 'default';
this.form.elements.fontweight.checked = style.fontWeight=='bold' || cs.fontWeight=='bold';
this.form.elements.fontstyle.checked = style.fontStyle=='italic' || cs.fontStyle=='italic';
this.form.elements.textalign.value = style.textAlign || cs.textAlign || 'initial';
this.form.elements.bgcolor.value = style.backgroundColor || cs.backgroundColor || 'transparent';
this.form.elements.cssFloat.value = style.cssFloat || cs.cssFloat || 'none';
this.form.elements.width.value = parseInt(style.width || cs.width) || 'auto';
}

function STE_createNewStyle (tag, name) {
var ss = this.form.elements.styleSelect;
var selector = tag+'.'+name;
if (tag=='*') selector = '.'+name;
ss.appendElement('option', {value:selector}).appendText(selector);
ss.value = selector;
this.updateUI(selector);
}

function STE_newStyleDialog () {
var _this = this;
var styles = {}, styleArray = ['*', 'aside', 'section', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
for (var i=0; i<styleArray.length; i++) styles[styleArray[i]] = styleArray[i];
DialogBox(msgs.NewStyleDT, [
{type:'select', name:'tag', label:msgs.NewStyleTag, values:styles},
{label:msgs.NewStyleName, name:'name'},
],  function(){
_this.createNewStyle(this.elements.tag.value, this.elements.name.value);
});
}

function STE_saveTemplate () {
var collected = '';
if (document.styleSheets) for (var j=0; j<document.styleSheets.length; j++) {
var stylesheet = document.styleSheets[j];
var rules = null;
try { rules = stylesheet.cssRules || stylesheet.rules; } catch(e){} // Against firefox security error
if (!rules) continue;
for (var i=0; i<rules.length; i++) {
var rule = rules[i];
if (/^#editor/ .test(rule.selectorText)) collected += rule.cssText + ' ';
}}
var url = window.actionUrl.replace('@@', 'saveTemplate');
ajax('POST', url, 'content='+encodeURIComponent(collected), function(e){
var div = document.getElementById('debug3');
if (!div) { div=document.querySelector('body').appendElement('div', {id:'debug3'}); }
div.innerHTML = e;
}, 
function(){alert('failed');});
}

function STE_exportTemplate () {
var url = window.actionUrl.replace('@@', 'exportTemplate');
window.location.href = url;
}

function STE_importTemplate () {
var curUrl = window.location.href;
var newUrl = window.actionUrl.replace('@@', 'importTemplate');
DialogBox(msgs.ImportTemplate, [
{type:'file', label:msgs.ImportTemplateFile, name:'upload'}
], function(){
var up = this.elements.upload;
alert(up);
alert(up.files);
for (var i in up) alert(i);
});//DialogBox
}

if (!window.onloads) window.onloads=[];
window.onloads.push(function(){
var ste = new StyleEditor($('#styleEditor')[0]);
ste.init();
});

alert('Template editor loaded');