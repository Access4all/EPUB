function EBO_addNewRow () {
if (!this.value || this.value=='') return;
var row = this.queryAncestor('tr');
var newRow = row	.cloneNode(true);
newRow.$('input[type=text]').each(function(f){ f.onblur=EBO_addNewRow; f.value=''; });
newRow.$('input').each(function(i){ i.setAttribute('name', i.getAttribute('name').replace(/\[(\d+)\]/g, function(m){ return '['+(1+parseInt(m[1]))+']'; }) ); });
row.parentNode.appendChild(newRow);
this.onblur=null;
}

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
var rt = document.getElementById('rightstable');
if(rt){
var uNameEdit = rt.$('input[type=text]')[0];
uNameEdit.onblur = EBO_addNewRow;
}
});

//alert('EBOLoaded');