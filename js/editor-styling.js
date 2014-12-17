var sides = ['Top', 'Right', 'Bottom', 'Left'];

function $css (selector, flags) {
var defstylesheet = null;
if (document.styleSheets) for (var j=0; j<document.styleSheets.length; j++) {
var stylesheet = document.styleSheets[j];
var rules = null;
try { rules = stylesheet.cssRules || stylesheet.rules; } catch(e){} // Firefox: we might iterate over an external stylesheet coming from an extension, in which case any access raise a security error
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
this.styleData = {};
this.form.elements.saveTplBtn.onclick = STE_saveTemplate.bind(this);
this.form.elements.exportStyleBtn.onclick = STE_exportTemplate.bind(this);
this.form.elements.importStyleBtn.onclick = STE_importTemplate.bind(this);
this.form.elements.newStyleBtn.onclick = STE_newStyleDialog.bind(this);
this.form.styleSelect.onchange = function(){ _this.updateUI(this.value); };
this.form.elements.font.onchange = function(){ _this.updateValue('fontFamily', this.value, 'default'); };
this.form.elements.fontsize.onchange = function(){ _this.updateValue('fontSize', pt2rem(parseFloat(this.value) || 12)+'rem', '1rem'); };
this.form.elements.fontcolor.onchange = function(){ _this.updateValue('color', this.value); };
this.form.elements.fontstyle.onclick = function() { _this.updateValue('fontStyle', this.checked?'italic':'normal', 'normal'); };
this.form.elements.fontweight.onclick = function(){ _this.updateValue('fontWeight', this.checked?'bold':'normal', 'normal'); };
this.form.elements.textalign.onchange = function(){ _this.updateValue('textAlign', this.value, 'initial'); };
this.form.elements.lineHeight.onchange = function(){ _this.updateValue('lineHeight', ((parseInt(this.value)/100.0) || 1), 1); };
this.form.elements.letterSpacing.onchange = function(){ _this.updateValue('letterSpacing', this.value+0==0? 'normal' : parseInt(this.value)+'px', 'normal'); };
this.form.elements.wordSpacing.onchange = function(){ _this.updateValue('wordSpacing', this.value+0==0? 'normal' : parseInt(this.value)+'px', 'normal'); };
this.form.elements.bgcolor.onchange = function(){ _this.updateValue('backgroundColor', this.value, 'transparent'); };
this.form.elements.width.onchange = function(){ _this.updateValue('width', this.value+0<=0? 'auto' : parseInt(this.value)+'%', 'auto'); };
this.form.elements.cssFloat.onchange = function(){ _this.updateValue('cssFloat', this.value, 'none'); };
for (var i=0; i<sides.length; i++) {
var prop = 'border' + sides[i], type = prop + 'Style', color = prop + 'Color', width = prop + 'Width';
var corner = (!(i%2)? sides[i]+sides[i+1] : sides[(i+1)%4]+sides[i]), radius = 'border' + corner + 'Radius';
var margin = 'margin' + sides[i], padding = 'padding'+sides[i];
this.form.elements[type].onchange = (function(t, _this){ return function(){ _this.updateValue(t, this.value, 'none'); }; })(type, this);
this.form.elements[color].onchange = (function(c, _this){ return function(){ _this.updateValue(c, this.value, 'transparent'); }; })(color, this);
this.form.elements[width].onchange = (function(w, _this){ return function(){ _this.updateValue(w, parseInt(this.value)+'px', '0'); }; })(width, this);
this.form.elements[radius].onchange = (function(r, _this){ return function(){ _this.updateValue(r, parseInt(this.value)+'px', '0'); }; })(radius, this);
this.form.elements[margin].onchange = (function(w, _this){ return function(){ _this.updateValue(w, (parseFloat(this.value)/10.0)+'em', '0'); }; })(margin, this);
this.form.elements[padding].onchange = (function(w, _this){ return function(){ _this.updateValue(w, (parseFloat(this.value)/10.0)+'em', '0'); }; })(padding, this);
}
this.populateStyleSelect();
var rule = $css('#StyleData');
if (rule && rule.style.content) {
var str = rule.style.content;
str = str.trim().substring(1, str.length -1)
.replace(/\\(['"])/g, '$1').trim();
this.styleData = JSON.parse(str);
}}

function STE_populateStyleSelect () {
var ss = this.form.elements.styleSelect; 
if (document.styleSheets) for (var j=0; j<document.styleSheets.length; j++) {
var stylesheet = document.styleSheets[j];
var rules = null;
try { rules = stylesheet.cssRules || stylesheet.rules; } catch(e){} // Firefox: we might iterate over an external stylesheet coming from an extension, in which case any access raise a security error
if (!rules) continue;
for (var i=0; i<rules.length; i++) {
var rule = rules[i];
if (!/^\.editor / .test(rule.selectorText)) continue;
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

function STE_getComputedStyle (el) {
if (el.getComputedStyle) return el.getComputedStyle();
else if (el.style && el.style.getComputedStyle) return el.style.getComputedStyle();
else if (window.getComputedStyle) return window.getComputedStyle(el);
else if (el.currentStyle) return el.currentStyle;
}

function STE_createGhostElement (selector) {
selector = selector.split(' ')[1].split('.');
var tagName = selector[0] || 'div';
var className = selector.length>1? selector[1] : 'noclass';
var styles = {position:'absolute', width:'1px', height:'1px', overflow:'hidden', top:'-999999px', left:'-999999px'};
var div = document.createElement2('div', {'class':'editor'});
var elem = div.appendElement(tagName, {'class':className, 'data-ghost':true});
document.querySelector('body').appendChild(div);
for (var i in styles) div.style[i]=styles[i];
return elem;
}

function STE_updateUI (selection) {
var selector = ('.editor '+selection).trim();
if (selection=='#') this.curStyle=null;
else this.curStyle = $css(selector, $css.CREATE);
if (!this.curStyle) return;
var style = this.curStyle; 
var cd = this.parseCssText(style.cssText); //  will reflects the state of the style in the CSS code; more accurate than computed styles, but not always present, i.e. grouped properties like border/margin/padding
var elem = document.querySelector(selector) || STE_createGhostElement(selector), cs = STE_getComputedStyle(elem); // Reflect the true computed style; is always present but is less accurate (the browser often make unit conversion and such)
this.form.elements.font.value = style.fontFamily || cd.fontFamily || 'default';
this.form.elements.fontsize.value = rem2pt(parseFloat(style.fontSize || cd.fontSize) || 1.0); // Supposed to be in rem or em
this.form.elements.fontcolor.value = style.color || cd.color || 'default';
this.form.elements.fontweight.checked = style.fontWeight=='bold' || cd.fontWeight=='bold';
this.form.elements.fontstyle.checked = style.fontStyle=='italic' || cd.fontStyle=='italic';
this.form.elements.textalign.value = style.textAlign || cd.textAlign || 'initial';
this.form.elements.lineHeight.value = Math.round(100 * (parseFloat(style.lineHeight || cd.lineHeight) || 1)); // Supposed to be widthout unit
this.form.elements.letterSpacing.value = parseInt(style.letterSpacing || cd.letterSpacing) || 'normal'; // Supposed to be in px
this.form.elements.wordSpacing.value = parseInt(style.wordSpacing || cd.wordSpacing) || 'normal'; // Supposed to be in px
this.form.elements.bgcolor.value = style.backgroundColor || cd.backgroundColor || 'transparent';
this.form.elements.cssFloat.value = style.cssFloat || cd.cssFloat || 'none';
this.form.elements.width.value = parseInt(style.width || cd.width) || 'auto'; // Supposed to be in %
for (var i=0; i<sides.length; i++) {
var prop = 'border' + sides[i], type = prop + 'Style', color = prop + 'Color', width = prop + 'Width';
var corner = (!(i%2)? sides[i]+sides[i+1] : sides[(i+1)%4]+sides[i]), radius = 'border' + corner + 'Radius';
var margin = 'margin' + sides[i], padding = 'padding' + sides[i];
this.form.elements[radius].value = parseInt(style[radius] || cd[radius] || cs[radius]) || '0'; // Supposed to be in px
this.form.elements[width].value = parseInt(style[width] || cd[width] || cs[width]) || '0'; // Supposed to be in px
this.form.elements[color].value = style[color] || cd[color] || cs[color] || 'transparent';
this.form.elements[type].value = style[type] || cd[type] || cs[type] || 'none';
this.form.elements[margin].value = 10.0 * parseFloat(style[margin] || cd[margin] || cs[margin] || 0); // Supposed to be in em
this.form.elements[padding].value = 10.0 * parseFloat(style[padding] || cd[padding] || cs[padding] || 0); // Supposed to be in em
}
if (elem && elem.hasAttribute('data-ghost')) elem.parentNode.parentNode.removeChild(elem.parentNode);
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
var collected = "#StyleData {\r\nContent: '" 
+ JSON.stringify(this.styleData).replace(/"/g, '\\"')
+ "';\r\n}\r\n\r\n\r\n";
if (document.styleSheets) for (var j=0; j<document.styleSheets.length; j++) {
var stylesheet = document.styleSheets[j];
var rules = null;
try { rules = stylesheet.cssRules || stylesheet.rules; } catch(e){} // Firefox: we might iterate over an external stylesheet coming from an extension, in which case any access raise a security error
if (!rules) continue;
for (var i=0; i<rules.length; i++) {
var rule = rules[i];
if (/^\.editor/ .test(rule.selectorText)) collected += rule.cssText + ' ';
}}
//debug("CSS="+collected.replace(/\r\n|\n/g, '<br />'));
var url = window.actionUrl.replace('@@', 'saveTemplate');
ajax('POST', url, 'content='+encodeURIComponent(collected), function(e){
debug(e,true);
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
if (!up || !up.files || up.files.length<=0 || !up.files[0] || !window.FormData) return;
var data = new FormData(), file = up.files[0];
var url = window.actionUrl.replace('@@', 'importTemplate');
data.append('upload', file, file.name);
ajax('POST', url, data, function(text){ 
if (text=='OK') window.location.reload();
else debug('Upload succeeded? '+text); 
},  function(){ alert('Upload failed'); });
});//DialogBox
}

function pt2rem (pt) {
return pt/12.0;
}

function rem2pt (rem) {
return Math.floor(rem * 12.0 + 0.25);
}

if (!window.onloads) window.onloads=[];
window.onloads.push(function(){
var ste = new StyleEditor($('#styleEditor')[0]);
ste.init();
});

//alert('Template editor loaded');