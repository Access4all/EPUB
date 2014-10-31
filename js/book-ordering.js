if (!window.onloads) window.onloads=[];
window.onloads.push(function(){
var quiz = document.getElementById('quiz');
quiz.setAttribute('data-submissionMode', 'local');
quiz.onsubmit = QuizSubmit_prepare;
$('.reorderableList li').each(function(li){
li.setAttribute('draggable', true);
li.ondragstart = SQO_dragStart;
li.ondragenter = SQO_dragenter;
li.ondragover = SQO_dragOver;
li.ondrop = SQO_drop;
li.onclick = SQO_click;
li.setAttribute('aria-grabbed', 'false');
li.setAttribute('tabindex', 0);	
});//each li
});

function arrayLevenshtein (s1, s2) {
var l1 = s1.length, l2 = s2.length;
var a = [];
for (var i=0; i<=l1; i++) {
a[i]=[];
for (var j=0; j<=l2; j++) a[i][j]=-1;
}
for (var i=0; i<=l1; i++) a[i][0] = i;
for (var j=0; j<=l2; j++) a[0][j]=j;
for (var i=1; i<=l1; i++) {
for (var j=1; j<=l2; j++) {
var c1 = s1[i -1], c2 = s2[j -1], p = 1;
if (c1==c2) p=0;
a[i][j] = Math.min(
1 + a[i -1][j], 
1 + a[i][j -1], 
p + a[i-1][j-1] );
}}
return a[l1][l2];
}

function QuizSubmit_prepare () {
$('.reorderableList li').each(function(li){
var input = li.querySelector('input');
var itemText = li.querySelector('.itemText') .textContent;
input.value = itemText;
});
var submit2 = window['QuizSubmit_'+quiz.getAttribute('data-submissionMode')];
return submit2.call(this);
}

function QuizSubmit_local () {
var lis = [], orig = [];
this.$('li').each(function(li){ lis.push(li); orig.push(li); });
lis.sort(function(li1,li2){ return parseInt(li1.getAttribute('data-order')) - parseInt(li2.getAttribute('data-order')); });
lis.each(function(li){ li.parentNode.appendChild(li); });
var total=lis.length, count = total-arrayLevenshtein(lis,orig);
alert("@QuizResult".replace('%1',count).replace('%2',total));
return false;
}

function QuizSubmit_file () {
var csv = '', fields = this.querySelectorAll('input');
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

function SQO_click (e) {
e = e || window.event;
if (window.grabbedItem && this.getAttribute('aria-dropeffect')) {
$('.reorderableList li').each(function(li){
li.removeAttribute('aria-dropeffect');
li.setAttribute('aria-grabbed', 'false');
});//each li
SQO_dragdrop(window.grabbedItem, this);
window.grabbedItem=null;
}
else {
window.grabbedItem=this;
this.setAttribute('aria-grabbed', 'true');
var _this=this;
$('.reorderableList li').each(function(li){
if (_this==li) return;
li.setAttribute('aria-dropeffect', 'move');
li.removeAttribute('aria-grabbed');
});//each li
}}

function SQO_dragStart (e) {
e = e || window.event;
e.dataTransfer.effectAllowed = 'move';
e.dataTransfer.setData('text', '#SQO#'+this.id);
}

function SQO_dragOver (e) {
e = e || window.event;
if (e.preventDefault) e.preventDefault();
}

function SQO_dragenter (e) {
e = e || window.event;
e.dropEffect = 'move';
}

function SQO_drop (e) {
e = e || window.event;
if (e.preventDefault) e.preventDefault();
var srcId = e.dataTransfer.getData('text');
if (!srcId || srcId.substring(0,5)!='#SQO#') return;
srcId = srcId.substring(5);
var src = document.getElementById(srcId);
if (!src) return;
SQO_dragdrop(src,this);
}

function SQO_dragdrop (src, dst) {
dst.parentNode.insertBefore(src, dst);
}

//alert('SQO3 loaded');