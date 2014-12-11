function onRTZCreate (rtz) {
rtz.onsave = RTZ_TFQ_save;
}

function RTZ_TFQ_save (_, sync) {
var introText = $('#intro')[0].innerHTML, quiz = $('#quiz')[0];
var data = {intro:introText, choices:[], questions:[]};
quiz.$('span.qchoice').each(function(c){ data.choices.push(c.innerHTML); });
quiz.$('tr.qrow').each(function(f){
var questionText = f.$('.questionText')[0].innerHTML;
var answers = [];
f.$('input').each(function(input,index){ if (input.checked) answers.push(index); });
data.questions.push({q:questionText, a:answers});
});//each row
data = JSON.stringify(data);
RTZ_defaultSave.call(this,data, sync);
}

function TFQ_onInputKeyDown (e) {
e = e || window.event;
var k = e.keyCode || e.which;
if (k==vk.tab && !e.shiftKey && !e.ctrlKey && !e.altKey) {
var tr = this.parentNode.parentNode;
if (tr.nextElementSibling) return true;
TFQ_createNewQuestion(tr);
return false;
}
return true;
}

function TFQ_createNewQuestion (last) {
var tr = last.cloneNode(true);
var qNumSpan = tr.querySelector('span.questionNumber');
var qLabel = tr.querySelector('span.qlabel');
var qNum = parseInt(qNumSpan.textContent);
var questionText = tr.querySelector('.questionText');
var qLabelId = 'qlbl'+qNum
tr.$('input').each(function(input, i){
var id = 'q' + qNum + '_' + i;
var name = 'q[' + qNum + (input.getAttribute('type')=='radio'? ']' : '][]');
input.setAttribute('id', id);
input.setAttribute('name', name);
input.onkeydown = TFQ_onInputKeyDown;
});
qNumSpan.innerHTML = (1+qNum);
qLabel.setAttribute('id', qLabelId);
questionText.setAttribute('aria-labelledby', qLabelId);
questionText.innerHTML='';
var rtz = new RTZ(questionText,null);
rtz.init();
last.parentNode.insertBefore(tr, last.nextElementSibling);
questionText.focus();
return false;
}

function TFQ_addColumn () {
var quiz = document.getElementById('quiz');
var th = quiz.querySelector('thead').querySelectorLast('th');
var thtr = th.parentNode;
th = th.cloneNode(true);
var answerText = th.querySelector('.qchoice');
var answerNum = 1+parseInt(answerText.id.match(/\d+$/));
answerText.setAttribute('id', 'choice'+answerNum);
answerText.setAttribute('aria-label', answerText.getAttribute('aria-label').replace(/\d+$/g, function(m){ return 1+parseInt(m); }));
answerText.innerHTML = '';
thtr.appendChild(th);
quiz.querySelector('tbody').$('tr').each(function(tr){
var td = tr.querySelectorLast('td') .cloneNode(true);
var input = td.querySelector('input');
input.setAttribute('value', answerNum);
input.setAttribute('aria-labelledby', 'choice'+answerNum);
input.setAttribute('id', input.id.substring(0, input.id.lastIndexOf('_')+1) + answerNum);
input.checked=false;
tr.appendChild(td);
});//each row
answerText.focus();
}

function TFQ_removeColumn (e) {
var quiz = document.getElementById('quiz');
if (quiz.querySelector('tr').querySelectorAll('th,td').length<=3) return; // Don't allow removing a column if there are only 2 answers or less
quiz.$('tr').each(function(tr){
tr.removeChild(tr.querySelectorLast('td,th'));
});//each row
}

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
$('.qanswerbox').each(function(input){ input.onkeydown = TFQ_onInputKeyDown; });
$('#addColBtn')[0].onclick = TFQ_addColumn;
$('#remColBtn')[0].onclick = TFQ_removeColumn;
});//onload

//alert('TFQ loaded');