if (!window.onloads) window.onloads=[];
window.onloads.push(function(){
var quiz = document.getElementById('quiz');
quiz.onsubmit = window['FTG_QuizSubmit_'+quiz.getAttribute('data-submissionMode')];
quiz.onreset = FTG_QuizReset;
var btnsa = document.getElementById('btnShowAnswers');
btnsa.onclick = FTG_Quiz_LocalShowAnswers.bind(null, quiz);
if (quiz.getAttribute('data-submissionMode')!='local') btsa.disabled=true;
});

function FTG_QuizReset () {
this.$('input, select').each(function(f){ 
f.value='-';
f.value=''; 
f.removeAttribute('aria-invalid'); 
f.removeClass('wrong'); 
f.removeClass('correct');
}); }

function FTG_QuizSubmit_local () {
try {
var count=0, total=0, fields = this.querySelectorAll('input,select');
for (var i=0; i<fields.length; i++) {
var input = fields[i];
var answer = input.getAttribute('data-answer').trim();
var orig = input.value.trim();
total++;
if (orig==answer) count++;
else input.setAttribute('aria-invalid',true);
input.addClass(orig==answer?'correct':'wrong');
}
alert("@QuizResult".replace('%1',count).replace('%2',total));
} catch(e) { alert(e.message); }
return false;
}

function FTG_Quiz_LocalShowAnswers (form) {
try {
var fields = form.querySelectorAll('input,select');
for (var i=0; i<fields.length; i++) {
var input = fields[i];
var answer = input.getAttribute('data-answer');
input.value=answer.trim();
}
} catch(e) { alert(e.message); }
}

function FTG_QuizSubmit_file () {
var csv = '', fields = this.querySelectorAll('input,select');
for (var i=0; i<fields.length; i++) {
var input = fields[i];
csv += (i+1) + ',' + input.value + '\r\n';
}
var uri = 'data:text/csv,'+encodeURIComponent(csv);
var a = document.createElement2('a', {href:uri, type:'text/csv', download:'quiz.csv', target:'_blank'});
a.appendText('Download');
document.querySelector('body').appendChild(a);
a.click();
a.parentNode.removeChild(a);
return false;
}

function FTG_QuizSubmit_url () {
this.target = '_blank';
return true;
}


//alert('ftg1 loaded');