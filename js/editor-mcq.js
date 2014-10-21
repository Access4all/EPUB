function onRTZCreate (rtz) {
rtz.ontab = RTZ_MCQ_onTab;
}

function RTZ_MCQ_onTab () {
if (this.zone.tagName.toLowerCase()!='label') return true;
if (this.zone.parentNode.nextSibling) return true;
if (this.zone.textContent.trim()) return MCQ_createNewAnswer(this.zone);
var thisFieldset = this.zone.parentNode.parentNode, nextFieldset = thisFieldset.nextSibling;
if(this.zone.parentNode.parentNode.querySelectorAll('p').length>2) MCQ_deleteAnswer(this.zone);
if (nextFieldset) { nextFieldset.querySelector('*[contenteditable]').focus(); return false; }
return MCQ_createNewQuestion(thisFieldset);
}

function MCQ_createNewQuestion (last) {
var fieldset = last.cloneNode(true);
var qNumSpan = fieldset.querySelector('span');
var qNum = parseInt(qNumSpan.textContent);
var ps = fieldset.$('p');
var firstContentEditable = null;
for (var i=ps.length -1; i>=2; i--) ps[i].parentNode.removeChild(ps[i]);
for (var i=0; i<2; i++) {
var input = ps[i].querySelector('input');
var label = ps[i].querySelector('label');
var id = 'q' + qNum + '_' + i;
var name = 'q[' + qNum + (input.getAttribute('type')=='radio'? ']' : '][]');
input.setAttribute('id', id);
label.setAttribute('for', id);
}
qNumSpan.innerHTML = (1+qNum);
fieldset.$('*[contenteditable]').each(function(f){
f.innerHTML='';
var rtz = new RTZ(f,null);
rtz.init();
if (!firstContentEditable) firstContentEditable = f;
});//each contenteditable
last.parentNode.insertBefore(fieldset, last.nextSibling);
firstContentEditable.focus();
return false;
}

function MCQ_createNewAnswer (last) {
var p = last.parentNode.cloneNode(true);
var input = p.querySelector('input');
var label = p.querySelector('label');
var id = input.getAttribute('id');
var num = id.lastIndexOf('_');
id = id.substring(0, num+1) + (1+parseInt(id.substring(num+1)));
input.setAttribute('id', id);
label.setAttribute('for', id);
label.innerHTML = '';
last.parentNode.parentNode.appendChild(p);
var rtz = new RTZ(label, null);
rtz.init();
label.focus();
return false;
}

function MCQ_deleteAnswer (ref) {
ref.parentNode.parentNode.removeChild(ref.parentNode);
}

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
//todo
});//onload

var § = 3;
alert('MCQ loaded');