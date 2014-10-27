if (!window.onloads) window.onloads=[];
window.onloads.push(function(){
var quiz = document.getElementById('quiz');
quiz.onsubmit = window['QuizSubmit_'+quiz.getAttribute('data-submissionMode')];
});

function QuizSubmit_local () {
var count=0, total=0, fieldsets = this.querySelector('tbody').querySelectorAll('tr');
for (var j=0; j<fieldsets.length; j++) {
var fields = fieldsets[j].querySelectorAll('input');
var checkedSet = [], correctSet = [];
for (var i=0; i<fields.length; i++) {
var input = fields[i];
var hadToBeChecked = input.getAttribute('data-checked')=='true';
var isChecked = input.checked;
input.checked = hadToBeChecked;
if (isChecked) checkedSet.push(i);
if (hadToBeChecked) correctSet.push(i);
}
total++;
if (checkedSet.toString()==correctSet.toString()) count++;
}
alert("@QuizResult".replace('%1',count).replace('%2',total));
return false;
}

function QuizSubmit_file () {
var csv = '', fieldsets = this.querySelector('tbody').querySelectorAll('tr');
for (var j=0; j<fieldsets.length; j++) {
var fields = fieldsets[j].querySelectorAll('input');
csv += (j+1)+'';
for (var i=0; i<fields.length; i++) {
var input = fields[i];
if (input.checked) {
var label = input.getAttribute('title');
csv += ',' + label;
}}
csv += '\r\n';
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


//alert('tf1 loaded');