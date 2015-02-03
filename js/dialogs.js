function DialogBox (title, desc, okFunc, cancelFunc, readyFunc) {
var lastFocus = document.activeElement;
var first=null, overlay = document.createElement2('div', {'class':'overlay'}), form = document.createElement2('form', {'role':'dialog', 'class':'dialogBox'});
form.appendElement('h1').appendText(title);
for (var i=0; i<desc.length; i++) {
var item = desc[i];
var p = form.appendElement('p');
p.appendElement('label', {'for':item.name, 'id':item.name+'Label'}).appendText(item.label+':');
var type = item.type || 'text';
var value = item.value || '';
var input = null;
if (type=='select') {
input = p.appendElement('select', {'id':item.name, 'name':item.name, 'aria-labelledby':item.name+'Label'});
for (val in item.values) input.appendElement('option', {'value':val}).appendText(item.values[val]);
if (item.value) input.value = item.value;
}
else {
var exargs = ['min', 'max', 'step', 'pattern', 'maxlength', 'multiple', 'checked', 'disabled', 'readonly'];
var args = {'type':type, 'value':value, 'id':item.name, 'name':item.name, 'aria-labelledby':item.name+'Label'};
for (var j=0; j<exargs.length; j++) if (item[exargs[j]]) args[exargs[j]]=item[exargs[j]];
input = p.appendElement('input', args);
}
if (!first) first=input;
}
var p = form.appendElement('p');
var btnOk = p.appendElement('button', {'type':'submit'}).appendText(msgs.OK);
var btnCancel = p.appendElement('button', {'type':'reset'}).appendText(msgs.Cancel);
form.onsubmit = function(){ 
var re;
try {
if (okFunc) re = okFunc.call(this);
} catch(e){ debug(e.message); }
if (re!==false) {
this.parentNode.removeChild(this); 
overlay.parentNode.removeChild(overlay);
document.getElementById('fullWrapper').removeAttribute('aria-hidden');
if (lastFocus && lastFocus.focus) lastFocus.focus();
}
return false;
};
form.onreset = function(){ 
var re;
if (cancelFunc) re = cancelFunc.call(this);
if (re!==false) {
this.parentNode.removeChild(this); 
overlay.parentNode.removeChild(overlay);
document.getElementById('fullWrapper').removeAttribute('aria-hidden');
if (lastFocus && lastFocus.focus) lastFocus.focus();
}
return false; 
};
overlay.onclick = DialogBox_overlayClick; // When a dialog box is active, nothing should happen if the user clicks outside of the dialog box
var body = document.querySelector('body');
body.appendChild(overlay);
body.appendChild(form);
if (first.focus) first.focus();
if (first.select) first.select();
document.getElementById('fullWrapper').setAttribute('aria-hidden', true);
if (readyFunc) readyFunc.call(form);
}

function MessageBox (title, msg, btns, okFunc, readyFunc) {
var lastFocus = document.activeElement;
var first=null, overlay = document.createElement2('div', {'class':'overlay'}), form = document.createElement2('div', {'role':'alertdialog', 'class':'dialogBox'});
form.appendElement('h1').appendText(title);
var p = form.appendElement(msg.indexOf('<p>')>0?'div':'p', {id:'MessageBoxLabel', 'aria-live':'assertive'});
p.innerHTML = msg;
p = form.appendElement('p');
var btnClick = function(btnIndex, btnLabel){ 
return function(){
var re;
if (okFunc) re = okFunc.call(this, btnIndex, btnLabel);
if (re!==false) {
this.parentNode.removeChild(this); 
overlay.parentNode.removeChild(overlay);
document.getElementById('fullWrapper').removeAttribute('aria-hidden');
if (lastFocus && lastFocus.focus) lastFocus.focus();
} }; };
for (var i=0; i<btns.length; i++) {
var btn= p.appendElement('button', {'type':'button'});
btn.innerHTML = btns[i];
btn.onclick = btnClick(i, btns[i]).bind(form);
btn.setAttribute('aria-describedby', 'MessageBoxLabel');
btn.setAttribute('aria-label', btns[i].stripHTML() );
if (!first) first=btn;
}
var body = document.querySelector('body');
overlay.onclick = DialogBox_overlayClick; // When a dialog box is active, nothing should happen if the user clicks outside of the dialog box
body.appendChild(overlay);
body.appendChild(form);
document.getElementById('fullWrapper').setAttribute('aria-hidden', true);
if (first.select) first.select();
if (first.focus) first.focus();
if (readyFunc) readyFunc.call(form);
}

function DialogBox_overlayClick (e) {
e = e || window.event;
if (e.preventDefault) e.preventDefault();
if (e.stopPropagation) e.stopPropagation();
if (e.stopImmediatePropagation) e.stopImmediatePropagation();
e.returnValue = e.ReturnValue = false;
return false;
}

function HelpBox (name) {
var rnd = Math.random();
ajax('GET', window.root + '/editor/' + name + '/infobox?rnd='+rnd, null, function(text){
MessageBox(msgs.Help, text, [msgs.OK]);
});//
return false;
}

function HelpBox_BtnInit (btn) {
var name = btn.getAttribute('data-infobox');
btn.setAttribute('tabindex', 0);
btn.onclick = HelpBox.bind(btn, name);
}

//alert('Dialogs loaded');