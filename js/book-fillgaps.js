if (!window.onloads) window.onloads=[];
window.onloads.push(function(){
var quiz = document.getElementById('quiz');
quiz.onsubmit = window['QuizSubmit_'+quiz.getAttribute('data-submissionMode')];
});

function QuizSubmit_local () {
try {
var count=0, total=0, fields = this.querySelectorAll('input,select');
for (var i=0; i<fields.length; i++) {
var input = fields[i];
var answer = input.getAttribute('data-answer');
total++;
if (input.value==answer) count++;
input.value = answer;
}
alert("@QuizResult".replace('%1',count).replace('%2',total));
} catch(e) { alert(e.message); }
return false;
}

function QuizSubmit_file () {
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

function QuizSubmit_url () {
this.target = '_blank';
return true;
}


//alert('ftg1 loaded');