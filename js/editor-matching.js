function onRTZCreate (rtz) {
rtz.onenter = RTZ_MPB_onenter;
rtz.onsave = RTZ_MPB_save;
}

function RTZ_MPB_onenter () {
if (this.zone.tagName.toLowerCase()!='span') return;
MPB_selectOnTab.call(this.zone);
return false;
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

function MPB_selectOnKeyDown (e) {
e = e || window.event;
var k = e.keyCode || e.which;
if (k==vk.tab && !e.shiftKey && !e.ctrlKey && !e.altKey) return MPB_selectOnTab.call(this);
else return true;
}

function MPB_selectOnTab () {
var li = this.queryAncestor('li');
var zone = li.querySelector('.matchingItem');
if (li.nextElementSibling) return true;
if (zone.textContent.trim()) return MPB_createNewItem(zone);
else return true;
}

function MPB_createNewItem (zone) {
var oldLi = zone.queryAncestor('li');
var classNames=['matchingActivity_leftList', 'matchingActivity_rightList'];
var num = oldLi.parentNode.querySelectorAll('li').length;
var li = oldLi.cloneNode(true);
var field = li.querySelector('.matchingItem');
var select = li.querySelector('select');
var left = select.className.indexOf('left')>=0;
var theNumber = (left? 1+num : String.fromCharCode(65+num) );
var otherClassName = classNames[1-classNames.indexOf(select.className)];
var otherOlType = document.querySelector('ol.'+otherClassName).getAttribute('type');
select.setAttribute('id', select.getAttribute('id').replace(/\d+$/g, function(m){ return 1+parseInt(m); }));
field.setAttribute('aria-label', field.getAttribute('aria-label').replace(/\d+$/g, function(m){ return 1+parseInt(m); }));
field.innerHTML = '';
select.value = '-';
select.onkeydown = MPB_selectOnKeyDown;
select.onchange = MPB_selectOnChange;
select.title = select.getAttribute('data-langmsg1') .replace('%1', theNumber);
$('select.'+otherClassName).each(function(oSelect){
var label = (otherOlType=='A'? 1+num : String.fromCharCode(65+num));
oSelect.appendElement('option', {value:num}).appendText(label);
});
oldLi.parentNode.appendChild(li);
var rtz = new RTZ(field, null);
rtz.init();
field.focus();
return false;
}

function RTZ_MPB_save () {
var introText = $('#intro')[0].innerHTML, quiz = $('#quiz')[0];
var l1h = $('#leftListHeading')[0], l2h = $('#rightListHeading')[0];
var data = {intro:introText, list1h:l1h.innerHTML, list2h:l2h.innerHTML, matches:{}, list1:[], list2:[]};
var list1 = quiz.querySelector('ol');
var list2 = quiz.querySelectorLast('ol');
var f = function(list, li){
var itemText = li.querySelector('.matchingItem').innerHTML;
list.push(itemText);
};
list1.$('li').each(f.bind(null,data.list1));
list2.$('li').each(f.bind(null,data.list2));
list1.$('select').each(function(select){
var mapsfrom = parseInt(select.id.match(/\d+/));
var mapsto = parseInt(select.value);
if (!isNaN(mapsto)) data.matches[mapsfrom]=mapsto;
});
data = JSON.stringify(data);
RTZ_defaultSave.call(this,data);
}

if (!window.onloads) window.onloads=[];
window.onloads.push(function(){
$('select.matchingActivity_leftList, select.matchingActivity_rightList').each(function(select){
select.onkeydown = MPB_selectOnKeyDown;
select.onchange = MPB_selectOnChange;
select.setAttribute('data-oldValue', select.value);
});//
});

//alert('MPB loaded');