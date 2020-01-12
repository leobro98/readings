function OnUnload() {
    if (warning)
        alert('Mitte kõik näidud on sisestatud!');
}

function setNoWarning() {
    warning = false;
}

function openPage(page) {
	var frm = document.forms[0];
	frm.action = page;
	frm.submit();
}

function openFlat() {
	var frm = document.forms['statistics'];
	var flat = document.getElementById('flatId');
	if (flat && flat.value && flat.value.length > 0)
		frm.submit();
	else {
		alert ('Korter ei ole sisestatud!');
		return false;
	}
}

function prefill() {
    var frm = document.forms['formReadings'];
    frm.action.value = 'fill';
    warning = false;
    frm.submit();
}

function deleteRow(id) {
	var frm = document.forms['formReadings'];
	frm.action.value = 'delete';
    frm.deletedId.value = id;
    warning = false;
	frm.submit();
}

function makeEditable(id) {
	var frm = document.forms['formReadings'];
	frm.edit.value = id;
    warning = false;
	frm.submit();
}

function selectElement(id) {
	var activeElement = document.getElementById(id);
	if (activeElement) {
		activeElement.focus();
		activeElement.select();
	}
}

function over(obj) {
    obj.className = obj.className + ' highlighted';
}

function out(obj) {
    var addedStyleStart = obj.className.indexOf(' highlighted');
    obj.className = obj.className.substr(0, addedStyleStart);
}
