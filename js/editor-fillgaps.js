function onRTZCreate (rtz) {
rtz.onsave = RTZ_FTG_save;
rtz.onkeydown = RTZ_FTG_keydown;
keys.mark = vk.ctrl+vk.g;
}

function RTZ_FTG_save () {
var introText = $('#intro')[0].innerHTML, gapText = $('#gaptext')[0].innerHTML, suggestions = document.getElementById('gaplist');
var data = {intro:introText, gaptext:gapText};
if (suggestions && suggestions.value.trim()) data.suggestions = suggestions.value.trim().split(/\r\n|\n|\r/mg);
data = JSON.stringify(data);
RTZ_defaultSave.call(this,data);
}

function RTZ_FTG_keydown (k) {
switch(k){
case keys.mark:
this.inlineFormat('mark', false);
return false;
}}

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
//todo
});//onload

//alert('FillGaps loaded');