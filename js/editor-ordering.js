function onRTZCreate (rtz) {
rtz.ontab = RTZ_SQO_ontab;
rtz.onenter = RTZ_SQO_onenter;
rtz.onsave = RTZ_SQO_save;
}

function RTZ_SQO_onenter () {
if (this.zone.tagName.toLowerCase()!='span') return;
if (this.ontab) this.ontab();
return false;
}

function RTZ_SQO_ontab () {
var li = this.zone.queryAncestor('li');
if (li.nextElementSibling) return true;
if (this.zone.textContent.trim()) return SQO_createNewItem(this.zone);
else return true;
}

function SQO_createNewItem (zone) {
var oldLi = zone.queryAncestor('li');
var li = oldLi.cloneNode(true);
var field = li.querySelector('.itemText');
field.innerHTML = '';
field.setAttribute('aria-label', field.getAttribute('aria-label').replace(/\d+$/g, function(m){ return 1+parseInt(m); }));
oldLi.parentNode.appendChild(li);
var rtz = new RTZ(field, null);
rtz.init();
field.focus();
return false;
}

function RTZ_SQO_save () {
var introText = $('#intro')[0].innerHTML, quiz = $('#quiz')[0];
var data = {intro:introText, items:[]};
quiz.$('.itemText').each(function(item){ data.items.push(item.innerHTML); });
data = JSON.stringify(data);
RTZ_defaultSave.call(this,data);
}

if (!window.onloads) window.onloads=[];
window.onloads.push(function(){
});

alert('SQO loaded');