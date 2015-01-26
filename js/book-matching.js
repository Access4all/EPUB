if (!window.onloads) window.onloads=[];
window.onloads.push(function(){
var quiz = document.getElementById('quiz');
quiz.onsubmit = window['QuizSubmit_'+quiz.getAttribute('data-submissionMode')];
quiz.onreset = QuizReset;
quiz.$('select').each(function(select){ select.onchange = MPB_selectOnChange; });
var btnsa = document.getElementById('btnShowAnswers');
btnsa.onclick = Quiz_LocalShowAnswers.bind(null, quiz);
if (quiz.getAttribute('data-submissionMode')!='local') btsa.disabled=true;
});

function QuizReset () {
this.$('input, select').each(function(f){ 
f.value='-';
f.removeAttribute('aria-invalid'); 
f.removeClass('wrong'); 
f.removeClass('correct');
}); }


function QuizSubmit_local () {
var count=0, total=0, fields = this.querySelectorAll('select');
for (var i=0; i<fields.length; i++) {
var input = fields[i];
var answer = input.getAttribute('data-answer');
if (input.id.indexOf('left')>=0) {
total++;
if (input.value==answer) count++;
}
if (input.value!=answer) input.setAttribute('aria-invalid',true);
input.addClass(input.value==answer?'correct':'wrong');
}
alert("@QuizResult".replace('%1',count).replace('%2',total));
return false;
}

function Quiz_LocalShowAnswers (form) {
try {
var fields = form.querySelectorAll('input,select');
for (var i=0; i<fields.length; i++) {
var input = fields[i];
var answer = input.getAttribute('data-answer');
input.value=answer;
}
} catch(e) { alert(e.message); }
}


function QuizSubmit_file () {
var csv = '', fields = this.querySelector('ol.matchingActivity_leftList').querySelectorAll('select');
for (var i=0; i<fields.length; i++) {
var input = fields[i];
var val = (input.value=='-'?'-': String.fromCharCode(65+parseInt(input.value)));
csv += (i+1) + ',' + val + '\r\n';
}
var uri = 'data:text/csv,'+encodeURIComponent(csv);
var a = document.createElement2('a', {href:uri, type:'text/csv', download:'quiz.csv', target:'_blank'});
a.appendText('Download');
document.querySelector('body').appendChild(a);
a.click();
a.parentNode.removeChild(a);
return false;
}

function QuizSubmit_url () {
this.target = '_blank';
return true;
}

function MPB_selectOnChange () {
var newVal = this.value, oldVal = this.getAttribute('data-oldValue');
var left = this.id.indexOf('left')>=0;
if (newVal=='-') {
this.setAttribute('data-oldValue', this.value);
var otherSelect = document.getElementById('match'+(left?'right':'left')+oldVal);
if (otherSelect) { 
otherSelect.setAttribute('data-oldValue', otherSelect.value);
otherSelect.value = '-';
}
return true;
}
var oldSelect = document.getElementById('match'+(left?'right':'left')+oldVal);
var newSelect = document.getElementById('match'+(left?'right':'left')+newVal);
var newOtherSelect = document.getElementById('match'+(!left?'right':'left')+newSelect.value);
if (oldSelect) { oldSelect.value = '-'; /*newSelect.value;*/ oldSelect.setAttribute('data-oldValue', oldSelect.value); }
if (newOtherSelect) { newOtherSelect.value = '-'; /*oldVal;*/ newOtherSelect.setAttribute('data-oldValue', newOtherSelect.value); }
this.setAttribute('data-oldValue', this.value);
newSelect.value = this.id.match(/\d+/);
newSelect.setAttribute('data-oldValue', newSelect.value);
}



//alert('mpb2 loaded');