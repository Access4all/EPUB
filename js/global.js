function include (url) {
var s = document.createElement('script');
s.setAttribute('type', 'text/javascript');
s.setAttribute('src', url);
document.getElementsByTagName('head')[0].appendChild(s);
}

function log (str) {
if (window.console && console.log) console.log(str);
alert(str);
}

function ajax (method, url, data, success, failure) {
var xhr = null;
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
xhr.open(method, url, true);
if (data) xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
xhr.send(data);
return xhr;
}

function domAddClass (o, name) {
var t = o.className.toString().split(' ');
if (t.indexOf(name)>=0) return;
t.push(name);
var s = t.join(' ');
o.className = s;
}

function domRemoveClass (o, name) {
var t = o.className.toString().split(' ');
var i = t.indexOf(name);
if (i<0) return;
t.splice(i,1);
var s = t.join(' ');
o.className = s;
}

function domRemove (elem) {
elem.parentNode.removeChild(elem);
}

function domHide (elem) {
elem.style.display='none';
}

function domShow (elem) {
elem.style.display='block';
}

function domIsVisible (elem) {
if (!elem) return true;
else if (!elem.style || !elem.style.display || elem.style.display=='') return domIsVisible(elem.parentNode);
else return elem.style.display!='none';
}

function domGenerateId () {
return 'rndid'+(new Date() .getTime()) +Math.floor(Math.random()*1000000);
}

/*function $$ (p1, p2, p3, p4) {
if (!p2) return document.querySelectorAll(p1);
else if (!p3) return p1.querySelectorAll(p2);
else return $(p1,p2,p3,p4);
}

function $ (p1, p2, p3, p4) {
if (!p2) return document.querySelector(p1);
else if (!p3) return p1.querySelector(p2);
else if (!p4) {
if (typeof(p1)=='string') p1 = document.querySelectorAll(p1);
if (p1.length()) for (var i=0; i<p1.length; i++) { p1[i][p2]=p3; }
else p1[p2]=p3;
}
else {
p2 = p1.querySelectorAll(p2);
if (p2.length()) for (var i=0; i<p2.length; i++) { p2[i][p3]=p4; }
}
}

function $c (tag, attrs) {
var el = document.createElement(tag);
if (attrs&&attrs.text) { 
if (typeof(attrs.text)=='string') el.innerHTML = attrs.text;
else if (attrs.text.isArray()) for (var i=0; i<attrs.text.length; i++) {
var x = attrs.text[i];
if (typeof(x)=='string') el.innerHTML+=x;
else el.appendChild(x);
}
else el.appendChild(attrs.text);
delete attrs.text;
}
if (attrs) for (var i in attrs) el.setAttribute(i, attrs[i]);
return el;
}*/

if (!Array.prototype.indexOf) Array.prototype.indexOf = function (o, start) {
if (typeof(start)!='number') start=0;
for (var i=start; i<this.length; i++) if (this[i]==o) return i;
return -1;
}

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
return this.replace(/^\s*/,'').replace(/\s*$/,'');
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

NodeList.prototype.each = function () {
var f = arguments[0], args = [];
for (var i=1; i<arguments.length; i++) args.push(arguments[i]);
for (var i=0; i<this.length; i++) f.call(this[i], args);
}

HTMLElement.prototype.$ = function (selector) { return this.querySelectorAll(selector); }
window.$ = function (selector) { return document.querySelectorAll(selector); }

Element.prototype.appendElement = function (tagName, attrs) {
var o = this.ownerDocument.createElement(tagName);
if (attrs) for (var i in attrs) o.setAttribute(i, attrs[i]);
this.appendChild(o);
return o;
}

Element.prototype.appendText = function (str) {
this.appendChild(this.ownerDocument.createTextNode(str));
return this;
}

Element.prototype.findAncestor = function (tagNames) {
if (typeof(tagNames)=='string') tagNames=[tagNames];
if (tagNames.indexOf( this.nodeName.toLowerCase() )>=0) return this;
else if (this.parentNode) return this.parentNode.findAncestor(tagNames);
else return null;
}

window.onload = function () {
if (window.onloads) {
for (var i=0; i<window.onloads.length; i++) 
//try { 
window.onloads[i](); 
//} catch (e) { log(e.message); }
}}

window.vk = {
'shift': 256, 'ctrl': 512, 'alt': 1024,
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
