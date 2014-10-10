function $css (selector, flags) {
if (document.styleSheets) for (var j=0; j<document.styleSheets.length; j++) {
var stylesheet = document.styleSheets[j];
var rules = stylesheet.cssRules || stylesheet.rules;
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
var stlylesheet = document.styleSheets[0];
if (stylesheet.addRule) stylesheet.addRule(selector, null, 0);
else if (stylesheet.insertRule) stylesheet.insertRule(selector + ' { }', 0);
var rules = stylesheet.cssRules || stylesheet.rules;
return (rules[0].selectorText==selector? rules[0] : null);
}
return null;
}
$css.DELETE = 1;
$css.CREATE = 2;


function StyleEditor (form) {
this.form = form;
this.curStyle = null;
this.parseCssText = STE_parseCssText;
this.init = STE_init;
this.updateUI = STE_updateUI;
this.updateValue = STE_updateValue;
}

function STE_init () {
var _this = this;
this.form.styleSelect.onchange = function(){ _this.updateUI(this.value); };
this.form.elements.font.onchange = function(){ _this.updateValue('fontFamily', this.value); };
this.form.elements.fontsize.onchange = function(){ _this.updateValue('fontSize', ((this.value/100) || 1) + 'em'); };
this.form.elements.fontcolor.onchange = function(){ _this.updateValue('color', this.value); };
}

function STE_parseCssText (text) {
var o = {};
// Let's just use replace to iterate through matches, instead of making a loop!
text.replace(/([-a-zA-Z_0-9]+)\s*:\s*(.*?);/g, function(_, key, value){
key = key.replace(/-(\w)/g, function(_,l){ return l.toUpperCase(); });
o[key]=value;
});
return o;
}

function STE_updateValue (property, value) {
if (!this.curStyle) return;
if (value=='default') this.curStyle.style[property] = null;
else this.curStyle.style[property] = value;
}

function STE_updateUI (selection) {
var selector = '#editor '+selection;
if (selection=='#') this.curStyle=null;
else if (selection) this.curStyle = $css(selector, $css.CREATE);
if (!this.curStyle) return;
var style = this.curStyle;
var cs = this.parseCssText(style.cssText);
this.form.elements.font.value = style.fontFamily || cs.fontFamily || 'default';
this.form.elements.fontsize.value = (100 * parseInt(style.fontSize || cs.fontSize)) || 100;
this.form.elements.fontcolor.value = style.color || cs.color || 'default';
}

if (!window.onloads) window.onloads=[];
window.onloads.push(function(){
var ste = new StyleEditor($('#styleEditor')[0]);
ste.init();
});

alert('Template editor loaded');