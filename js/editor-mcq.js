function onRTZCreate (rtz) {
rtz.ontab = RTZ_MCQ_onTab;
rtz.onenter = RTZ_MCQ_onenter;
rtz.onsave = RTZ_MCQ_save;
rtz.oncontextmenu = RTZ_MCQ_contextMenu;
}

function RTZ_MCQ_onenter () {
if (this.zone.tagName.toLowerCase()!='label') return;
this.ontab();
return false;
}

function RTZ_MCQ_onTab () {
if (this.zone.tagName.toLowerCase()!='label') return true;
if (this.zone.parentNode.nextElementSibling) return true;
if (this.zone.textContent.trim()) return MCQ_createNewAnswer(this.zone);
var thisFieldset = this.zone.parentNode.parentNode, nextFieldset = thisFieldset.nextElementSibling;
if(this.zone.parentNode.parentNode.querySelectorAll('p').length>2) MCQ_deleteAnswer(this.zone);
if (nextFieldset) { nextFieldset.querySelector('*[contenteditable]').focus(); return false; }
return MCQ_createNewQuestion(thisFieldset);
}

function RTZ_MCQ_save () {
var introText = $('#intro')[0].innerHTML, quiz = $('#quiz')[0];
var data = {intro:introText, questions:[]};
quiz.$('fieldset').each(function(f){
var questionText = f.$('.questionText')[0].innerHTML;
var choices = [];
var answers = [];
f.$('label').each(function(l){ choices.push(l.innerHTML); });
f.$('input').each(function(input,index){ if (input.checked) answers.push(index); });
data.questions.push({q:questionText, c:choices, a:answers});
});//each fieldset
data = JSON.stringify(data);
RTZ_defaultSave.call(this,data);
}

function RTZ_MCQ_contextMenu (items, sel) {
var qt = this.zone.queryAncestor('.questionText');
var fieldset = this.zone.queryAncestor('fieldset');
if (!qt&&!fieldset) return;
var qr = this.zone.tagName.toLowerCase()=='label'?  this.zone : fieldset.querySelectorLast('label');
items.merge([msgs.InsertNewQuestion, MCQ_createNewQuestion.bind(this, fieldset)]);
if (qr) items.merge([msgs.InsertNewAnswer, MCQ_createNewAnswer.bind(this, qr)]);
if (qr) items.merge([msgs.DeleteAnswer, MCQ_deleteAnswer.bind(this, qr)]);
}

function MCQ_createNewQuestion (last) {
var fieldset = last.cloneNode(true);
var qNumSpan = fieldset.querySelector('span.questionNumber');
var qNum = parseInt(qNumSpan.textContent);
var ps = fieldset.$('p');
var firstContentEditable = null;
for (var i=ps.length -1; i>=2; i--) ps[i].parentNode.removeChild(ps[i]);
for (var i=0; i<2; i++) {
var label = ps[i].querySelector('label'), input = ps[i].querySelector('input');
var id = 'q' + qNum + '_' + i;
var name = 'q[' + qNum + (input.getAttribute('type')=='radio'? ']' : '][]');
input.setAttribute('name', name);
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
last.parentNode.insertBefore(fieldset, last.nextElementSibling);
while (fieldset = fieldset.nextElementSibling) {
fieldset.$('.questionNumber').each(function(l){ l.innerHTML  = l.innerHTML.toString().replace(/(\d+)/, function(n){ return 1+parseInt(n); }); });
fieldset.$('label').each(function(l){ l.setAttribute('for', l.getAttribute('for').replace(/(\d+)/, function(n){ return 1+parseInt(n); })); });
fieldset.$('input').each(function(i){ i.setAttribute('id', i.getAttribute('id').replace(/(\d+)/, function(n){ return 1+parseInt(n); })); });
fieldset.$('input').each(function(i){ i.setAttribute('name', i.getAttribute('name').replace(/(\d+)/, function(n){ return 1+parseInt(n); })); });
}
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
label.setAttribute('aria-label', label.getAttribute('aria-label').replace(/\d+$/g, function(m){ return 1+parseInt(m); }));
last.parentNode.parentNode.insertBefore(p, last.parentNode.nextSibling);
var rtz = new RTZ(label, null);
rtz.init();
while(p = p.nextElementSibling) {	
p.$('label').each(function(l){ l.setAttribute('aria-label', l.getAttribute('aria-label').replace(/(\d+)$/, function(n){ return 1+parseInt(n); })); });
p.$('label').each(function(l){ l.setAttribute('for', l.getAttribute('for').replace(/(\d+)$/, function(n){ return 1+parseInt(n); })); });
p.$('input').each(function(i){ i.setAttribute('id', i.getAttribute('id').replace(/(\d+)$/, function(n){ return 1+parseInt(n); })); });
}
label.focus();
return false;
}

function MCQ_deleteAnswer (ref) {
var p = ref.parentNode, p0=p;
while(p = p.nextElementSibling) {	
p.$('label').each(function(l){ l.setAttribute('aria-label', l.getAttribute('aria-label').replace(/(\d+)$/, function(n){ return -1+parseInt(n); })); });
p.$('label').each(function(l){ l.setAttribute('for', l.getAttribute('for').replace(/(\d+)$/, function(n){ return -1+parseInt(n); })); });
p.$('input').each(function(i){ i.setAttribute('id', i.getAttribute('id').replace(/(\d+)$/, function(n){ return -1+parseInt(n); })); });
}
p0.parentNode.removeChild(p0);
}

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
//todo
});//onload

//alert('MCQ loaded');