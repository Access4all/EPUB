function A11Y_startAnalysis () {
var errdiv = document.getElementById('analysisResults'), 
errContext = {errors:[]},
checkfunc = A11Y_checkZone.bind(errContext);
if (errdiv) errdiv.innerHTML = '<p>' + msgs.AnalysisInProgress + '</p>';
$('.editor, *[contenteditable=true]').each(checkfunc);
if (errdiv) {
var clueMaxlen=50;
errdiv.innerHTML = '';
errContext.errors.sort(function(a,b){ return a.target==b.target? 0 : (a.target.isBefore(b.target)? -1 : 1); });
if (errContext.errors.length>0) {
var ol = errdiv.appendElement('ol');
for (var i=0; i<errContext.errors.length; i++) {
var err = errContext.errors[i];
var text = err.target.textContent.trim() || err.target.parentNode.textContent.trim();
if (text.length>clueMaxlen) {
var idx = Math.max(0, Math.min( text.indexOf(' ', clueMaxlen), text.length ));
text = text.substring(0,idx).trim() + '...';
}
var a = ol.appendElement('li').appendElement('a', {href:'#'});
a.innerHTML = msgs['MsgType_'+err.type] + ': ' + err.msg + ': ' + msgs.Near + ' ' + text.escapeHTML();
a.onclick = A11Y_msgClick.bind(a,err);
}}
else errdiv.innerHTML = '<p>' + msgs.A11YNoLocalError + '</p>';
}
return errContext;
}

function A11Y_msgClick (err) {
var sel = RTZ_getSelection();
try {
if (err.target.hasChildNodes()) sel.selectNodeContents(err.target);
else sel.selectNode(err.target);
} catch(e) { debug(e.message); }
err.zone.focus();
RTZ_select(sel);
return false;
}

function A11Y_checkZone (zone) {
var _this = this;
var inline = ['div', 'section', 'aside', 'header', 'footer'].indexOf(zone.tagName.toLowerCase())<0;
//zone.$('p').each(function(p){ _this.errors.push({msg:'Test error message', type:'warn', target:p, 'zone':zone}); });

// Check image alternate texts
zone.$('img').each(function(img){ 
var alt = img.getAttribute('alt');
if (alt==='') _this.errors.push({msg:msgs.ImgEmptyAlt, type:'info', target:img, 'zone':zone}); 
else if (!alt) _this.errors.push({msg:msgs.ImgNoAlt, type:'error', target:img, 'zone':zone}); 
else if (
/^.*\.\w{2,4}$/i .test(alt) // ends like a file extension: typical from word/ppt defaults
) _this.errors.push({msg:msgs.ImgBadAlt.replace('%1', alt), type:'warn', target:img, 'zone':zone}); 
});

// Check that every figure has exactly one figcaption
zone.$('figure').each(function(fig){
var captions = fig.$('figcaption');
if (captions.length==0) _this.errors.push({msg:msgs.FigNoCaption, type:'error', target:fig, 'zone':zone});
else if (captions.length>=2) _this.errors.push({msg:msgs.FigDoubleCaption, type:'error', target:captions[1], 'zone':zone});
else if (captions[0]!=fig.firstElementChild && captions[0]!=fig.lastElementChild) _this.errors.push({msg:msgs.FigBadPlacedCaption, type:'error', target:captions[0], 'zone':zone});
if (captions.length>0 && !captions[0].textContent.trim()) _this.errors.push({msg:msgs.FigEmptyCaption, type:'warn', target:captions[0], 'zone':zone});
});//each figure

// Check heading structure: here umproper sequences like h(X+2) following h(X) without any h(X+1) inbetween
{
var lastLevel = 0;
zone.$('h1, h2, h3, h4, h5, h6').each(function(hn){
var level = parseInt(hn.tagName.substring(1));
if (lastLevel>0 && level>lastLevel+1) _this.errors.push({msg:msgs.HnBadStructure.replace('%1', level).replace('%2', lastLevel), type:'error', target:hn, 'zone':zone});
lastLevel = level;
});
} // end check heading structure

// If lastLevel==0, it means that there was no heading in the whole document
if (lastLevel==0) _this.errors.push({msg:msgs.HnNoHeading, type:'error', target:zone.getFirstTextNode(), 'zone':zone});

// Looking for two headings which are immediately next to eachother and have the same level; they may probably be joined tegether
zone.$('h1+h1, h2+h2, h3+h3, h4+h4, h5+h5, h6+h6').each(function(hn){ 
_this.errors.push({msg:msgs.HnDoubleSameLevel, type:'warn', target:hn, 'zone':zone}); 
});

// Looking for unproper heading sequence; here where hX follows directly hY where Y>X without any text inbetween
zone.$('h2+h1, h3+h2, h4+h3, h5+h4, h6+h5, h3+h1, h4+h2, h5+h3, h6+h4, h4+h1, h5+h2, h6+h3, h5+h1, h6+h2, h6+h1').each(function(hn){ 
var l1 = parseInt(hn.tagName.substring(1)), l2 = parseInt(hn.previousElementSibling.tagName.substring(1));
_this.errors.push({msg:msgs.HnBadStructure.replace('%1', l1).replace('%2', l2), type:'error', target:hn, 'zone':zone}); 
});

// Usually, a document should start with an heading
{
var node = zone.firstElementChild;
while(node.matches('section, header, footer, main')) node = node.firstElementChild;
if (!node.matches('h1, h2, h3, h4, h5, h6')) _this.errors.push({msg:msgs.HnStartingHeading, type:'warn', target:zone.getFirstTextNode(), 'zone':zone}); 
}

// IF the ratio between headings and non-heading is greater than 40%, we can honnestly assume that there are paragraphs that are incorrectly marked as headings
{
var nHeadings = zone.$('h1, h2, h3, h4, h5, h6').length,
nPara = zone.$('p, ul, ol, dl, table, pre').length,
headingRatio = nHeadings / (nHeadings+nPara);
if (headingRatio>=0.4) _this.errors.push({msg:msgs.HnBigRatio, type:'warn', target:zone.getFirstTextNode(), 'zone':zone}); 
}

// Looking for short paragraphs that are entirely in bold; they might be false headings
zone.$('p>b:only-child, p>strong:only-child').each(function(p){
if (p.parentNode.textContent!=p.textContent) return; // Internet Explorer: pseudo-class :only-child don't take text nodes into acount, so we might catch things like <p>foo<b>bar</bbat></p> before this point
if (p.textContent.trim().length>100) return; // More than 100 characters, definitely too long for an heading
_this.errors.push({msg:msgs.HnFalseBold, type:'info', target:p, 'zone':zone}); 
});//each paragraph

// Looking for headings that are entirely in bold. It should not occur, or it means that bold hasn't been defined in the appropriate style
zone.$('h1, h2, h3, h4, h5, h6').each(function(hn){
var b = hn.querySelector('strong, b');
if (!b) return;
if (b.textContent==hn.textContent) _this.errors.push({msg:msgs.HnBadBold, type:'info', target:hn, 'zone':zone}); 
});//each heading

// Let's handle boxes, asides, etc.
zone.$('section, aside').each(function(box){
var highestLevel = 7, highestLevelCount = 0, lastHeading=null;
// Each box should normally contain at least one heading
// There shouldn't be more than one top-level heading
box.$('h1, h2, h3, h4, h5, h6').each(function(hn){
var level = parseInt(hn.tagName.substring(1));
if (level<highestLevel) { highestLevel=level; highestLevelCount=1; }
else if (level==highestLevel) { highestLevelCount++; lastHeading=hn; }
});//each heading inside the box
if (highestLevelCount<=0) _this.errors.push({msg:msgs.HnNoHnInBox, type:'warn', target:box, 'zone':zone});
else if (highestLevelCount>=2) _this.errors.push({msg:msgs.HnDoubleHighestHnInBox, type:'error', target:lastHeading, 'zone':zone}); 
});//each aside and section box

// Now, analyse tables
zone.$('table').each(function(table){
var trs = table.$('tr'),
nRows = trs.length,
nCols = nRows>0? trs[0].$('th,td').length : 0,
cells = table.$('th,td'),
nCells = cells.length,
nThs = table.$('th').length,
captions = table.$('caption'),
caption = captions.length>0? captions[0] : null;
if (nThs<=0) _this.errors.push({msg:msgs.TblNoTh, type:'error', target:table, 'zone':zone}); 
if (captions.length>1) _this.errors.push({msg:msgs.TblMultiCaption, type:'error', target:captions[1], 'zone':zone}); 
else if (captions.length<=0) _this.errors.push({msg:msgs.TblNoCaption, type:'warn', target:table, 'zone':zone});
else if (caption && !caption.textContent.trim()) _this.errors.push({msg:msgs.TblEmptyCaption, type:'warn', target:caption, 'zone':zone}); 
else if (caption && caption!=table.firstElementChild && caption!=table.lastElementChild) _this.errors.push({msg:msgs.TblBadPlacedCaption, type:'error', target:caption, 'zone':zone});
if (nCells!=nRows*nCols) _this.errors.push({msg:msgs.TblIrregular, type:'warn', target:table, 'zone':zone}); 
if (nRows<=2 || nCols<=2) _this.errors.push({msg:msgs.TblWSmall, type:'info', target:table, 'zone':zone});
if (nThs/nCells>=0.56) _this.errors.push({msg:msgs.TblTooManyTh, type:'info', target:table, 'zone':zone}); 
cells.each(function(td){
if (td==cells[0] || td==cells[cells.length -1]) return;
if (!td.textContent.trim()) _this.errors.push({msg:msgs.TblEmptyTd, type:'warn', target:td, 'zone':zone}); 
});//each cell
trs.each(function(tr){
if (tr==trs[0] || tr==trs[trs.length -1]) return;
var cols = tr.$('th,td');
tr.$('th').each(function(th){
if (th==cols[0] || th==cols[cols.length -1]) return;
_this.errors.push({msg:msgs.TblBadTh, type:'warn', target:th, 'zone':zone}); 
});//each header cell of the row
});//each row
});//each table

// Analysing lists
zone.$('li:only-child').each(function(li){ _this.errors.push({msg:msgs.LstOnlyLi, type:'warn', target:li, 'zone':zone});  });//li:only-child
zone.$('ul+ul, ol+ol').each(function(l){ _this.errors.push({msg:msgs.LstConsecutiveLists, type:'info', target:l, 'zone':zone});  });//each list

//other messages
}

if (!window.onloads) window.onloads = [];
window.onloads.push(function(){
document.getElementById('redoAnalysis').onclick = A11Y_startAnalysis;
A11Y_startAnalysis();
});
alert('a11y loaded');