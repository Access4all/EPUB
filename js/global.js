DEBUG = true;

function debug (text, clear) {
if (!DEBUG) return;
var div = document.getElementById('debug3');
if (!div) { div=document.querySelector('body').appendElement('div', {id:'debug3'}); }
if (clear)  div.innerHTML = text + '<br />\r\n';
else  div.insertAdjacentHTML('beforeEnd', text + '<br />\r\n');
}

function include (url) {
var s = document.createElement('script');
s.setAttribute('type', 'text/javascript');
s.setAttribute('src', url);
document.getElementsByTagName('head')[0].appendChild(s);
}

function ajax (method, url, data, success, failure, async) {
var xhr = null;
if (async!==false) async=true;
if (window.XMLHttpRequest) xhr = new XMLHttpRequest();
else if (window.ActiveXObject) xhr = new ActiveXObject('Microsoft.XMLHTTP');
if (!xhr) return null;
xhr.onreadystatechange = function () {
if (xhr.readyState==4) {
if (xhr.status==200) success(xhr.responseText, xhr.responseXML, xhr);
else failure(xhr.status, xhr);
}};
xhr.onerror = xhr.onabort = function () { 
failure(-1, xhr); 
};
xhr.open(method, url, async);
if (data && typeof(data)!='object') xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
xhr.send(data);
return xhr;
}

if (!Array.prototype.indexOf) Array.prototype.indexOf = function (o, start) {
if (typeof(start)!='number') start=0;
for (var i=start; i<this.length; i++) if (this[i]==o) return i;
return -1;
}

if (!Array.prototype.each) Array.prototype.each = function () {
var f = arguments[0], args = [];
for (var i=1; i<arguments.length; i++) args.push(arguments[i]);
for (var i=0; i<this.length; i++) {
args.unshift(i);
args.unshift(this[i]);
f.apply(null, args);
args.shift();
args.shift();
}}

Array.prototype.merge = function (ar) {
for (var i=0; i<ar.length; i++) this.push(ar[i]);
}

String.prototype.indexOfIgnoreCase = function (s) {
return this.toLowerCase().indexOf(s.toLowerCase());
}

String.prototype.startsWith = function (str) {
return this.indexOf(str)==0;
}

String.prototype.startsWithIgnoreCase = function (str) {
return this.indexOfIgnoreCase(str)==0;
}

String.prototype.trim = function () {
return this.ltrim().rtrim();
}

String.prototype.ltrim = function () {
return this.replace(/^(?:\s|[\r\n\u00A0])*/gm,'');
}

String.prototype.rtrim = function () {
return this.replace(/(?:\s|[\r\n\u00A0])*$/mg,'');
}

String.prototype.splitn = function (sep, lim) {
var t = this.split(sep);
if (t.length>lim) {
t.push(
t.splice(lim -1, t.length -lim +1)
.join(sep));
}
return t;
}

String.prototype.escapeHTML = function () {
return this
.split('&').join('&amp;')
.split('<').join('&lt;')
.split('>').join('&gt;');
}

String.prototype.stripHTML = function () {
return this.replace(/<.*?>/g, '');
}

NodeList.prototype.join = Array.prototype.join;
NodeList.prototype.reduce = Array.prototype.reduce;
NodeList.prototype.filter = Array.prototype.filter;

NodeList.prototype.each = function () {
var f = arguments[0], args = [];
for (var i=1; i<arguments.length; i++) args.push(arguments[i]);
for (var i=0; i<this.length; i++) {
args.unshift(i);
args.unshift(this[i]);
f.apply(null, args);
args.shift();
args.shift();
}}

HTMLElement.prototype.addClass = function(name) {
var t = this.className.toString().split(' ');
if (t.indexOf(name)>=0) return;
t.push(name);
var s = t.join(' ');
this.className = s;
}

HTMLElement.prototype.removeClass = function(name) {
var t = this.className.toString().split(' ');
var i = t.indexOf(name);
if (i<0) return;
t.splice(i,1);
var s = t.join(' ');
this.className = s;
}

HTMLElement.prototype.hide = function() {
this.style.display='none';
}

HTMLElement.prototype.show = function() {
this.style.display='block';
}

HTMLElement.prototype.isVisible = function() {
if (!this.style || !this.style.display || this.style.display=='') {
if (this.parentNode) return this.parentNode.isVisible();
else return true;
}
else return this.style.display!='none';
}

function domGenerateId () {
return 'rndid'+(new Date() .getTime()) +Math.floor(Math.random()*1000000);
}

HTMLElement.prototype.querySelectorLast = function (selector) {
var tab = this.querySelectorAll(selector);
return (tab&&tab.length? tab[tab.length -1] : null);
}

HTMLElement.prototype.$ = function (selector) { return this.querySelectorAll(selector); }
window.$ = function (selector) { return document.querySelectorAll(selector); }

HTMLElement.prototype.getAbsoluteScreenPosition = function (cx, cy) {
cx = cx || 0; cy = cy || 0;
cx += this.offsetLeft;
cy += this.offsetTop;
if (this.offsetParent) return this.offsetParent.getAbsoluteScreenPosition(cx,cy);
else return {x:cx, y:cy};
}

Document.prototype.createElement2 = function (tagName, attrs, text) {
var e = this.createElement(tagName);
if (attrs) for (var i in attrs) if (attrs[i]!=null) e.setAttribute(i, attrs[i]);
if (text) e.appendText(text);
return e;
}

Node.prototype.appendElement = function (tagName, attrs, text) {
var o = this.ownerDocument.createElement2(tagName, attrs, text);
this.appendChild(o);
return o;
}

Node.prototype.appendText = function (str) {
this.appendChild(this.ownerDocument.createTextNode(str));
return this;
}

Node.prototype.insertElementBefore = function (tagName, ref, attrs, text) {
var o = this.ownerDocument.createElement2(tagName, attrs, text);
this.insertBefore(o,ref);
return o;
}

Node.prototype.isAfter = function(x) { return 0!=(this.compareDocumentPosition(x)&2); };
Node.prototype.isBefore = function(x) { return 0!=(this.compareDocumentPosition(x)&4); };
Node.prototype.isInside = function(x) { return 0!=(this.compareDocumentPosition(x)&8); };
Node.prototype.containsNode = function(x) { return 0!=(this.compareDocumentPosition(x)&16); };

Node.prototype.findAncestor = function (tagNames) {
if (typeof(tagNames)=='string') tagNames=[tagNames];
if (tagNames.indexOf( this.nodeName.toLowerCase() )>=0) return this;
else if (this.parentNode) return this.parentNode.findAncestor(tagNames);
else return null;
}

Node.prototype.queryAncestor = function (selector) {
if (this.matches && this.matches(selector)) return this;
else if (this.parentNode) return this.parentNode.queryAncestor(selector);
else return false;
}

Node.prototype.normalize2 = function () {
if (this.nodeType==3 && this.nextSibling && this.nextSibling.nodeType==3) {
var adjacent = this.nextSibling;
this.appendData(adjacent.data);
this.parentNode.removeChild(adjacent);
}
for (var i=this.childNodes.length -1; i>=0; i--) this.childNodes[i].normalize2();
};

Node.prototype.eachChild = function () {
var k=0, f = arguments[k], args = [], reverse=false, start=0, end=this.childNodes.length, step=1;
if (typeof(f)=='boolean') { reverse=f; f=arguments[++k]; }
for (var i=1+k; i<arguments.length; i++) args.push(arguments[i]);
if (reverse) { start=this.childNodes.length -1; end=-1; step=-1; }
for (var i=start; i!=end; i+=step) {
args.unshift(i);
args.unshift(this.childNodes[i]);
f.apply(null, args);
args.shift();
args.shift();
}}

Node.prototype.indexOf = function (subnode) {
for (var i=0; i<this.childNodes.length; i++) if (this.childNodes[i]==subnode) return i;
return -1;
}

if (!Element.prototype.matches) Element.prototype.matches = Element.prototype.webkitMatchesSelector || Element.prototype.mozMatchesSelector || Element.prototype.msMatchesSelector;

window.onload = function () {
if (window.onloads) {
for (var i=0; i<window.onloads.length; i++) 
//try { 
window.onloads[i](); 
//} catch (e) { log(e.message); }
}}

window.vk = {
'shift': 256, 'ctrl': 512, 'alt': 1024, 'impossible':2048,
'a': 65, 'b': 66, 'c': 67, 'd': 68, 'e': 69, 'f': 70, 'g': 71,
'h': 72, 'i': 73, 'j': 74, 'k': 75, 'l': 76, 
'm': 77, 'n': 78, 'o': 79, 'p': 80, 'q': 81,
'r': 82, 's': 83, 't': 84, 'u': 85, 'v': 86, 'w': 87, 'x': 88, 'y': 89, 'z': 90,
'tab':9, 'home':36, 'end':35, 'prior':33, 'next':34, 'insert':45, 
'backspace': 8, 'enter': 13, 'pause': 19, 'escape': 27, 'space': 32,
'n0': 48, 'n1': 49, 'n2': 50, 'n3': 51, 'n4': 52, 'n5': 53, 'n6': 54, 'n7': 55, 'n8': 56, 'n9': 57,
'context': 0x5D, 'del': 46, 'left': 37, 'up': 38, 'right': 39, 'down': 40,
'f1': 0x70, 'f2': 0x71, 'f3': 0x72, 'f4': 0x73, 'f5': 0x74, 'f6': 0x75, 'f7': 0x76, 'f8': 0x77, 'f9': 0x78, 'f10': 0x79, 'f11': 0x7A, 'f12': 0x7B,
'kp0': 0x60, 'kp1': 0x61, 'kp2': 0x62, 'kp3': 0x63, 'kp4': 0x64, 'kp5': 0x65, 'kp6': 0x66, 'kp7': 0x67, 'kp8': 0x68, 'kp9': 0x69,
'kpMul': 0x6A, 'kpAdd': 0x6B, 'kpEnter': 0x6C, 'kpMinus': 0x6D, 'kpDot': 0x6E, 'kpDiv': 0x6F
}; // keys

var html5 = ('main nav header footer article section aside time mark').split(' ');
for (var i=0; i<html5.length; i++) document.createElement(html5[i]);
