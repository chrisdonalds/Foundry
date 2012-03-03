/*
 *
 * File: validator.js
 *
 * Contains a list of function for field validations.
 *
 * Author: Terry Chen
 * Date: June12, 2006
 *
 */


function checkRequiredField(object, errMssg){
	var Elem = document.getElementById(object);

	if (Elem){
		if (Elem.value == "" && $(Elem).is(':visible')){
			if(errMssg != "") alert(errMssg);
			Elem.focus();
			return false;
		}
		return true;
	}
	return true;
}

function checkRequiredDualFields(object1, object2, op, focusobj, errMssg){
	var Elem1 = document.getElementById(object1);
	var Elem2 = document.getElementById(object2);
	var ElemFocus = document.getElementById(focusobj);

	if (Elem1 && Elem2 && ElemFocus){
		if (op == "or") {
    		if ((Elem1.value == "" || Elem2.value == "") && $(Elem1).is(':visible') && $(Elem2).is(':visible')){
    			if(errMssg != "") alert(errMssg);
    			ElemFocus.focus();
    			return false;
    		}
		} else if(op == "and") {
    		if ((Elem1.value == "" && Elem2.value == "") && $(Elem1).is(':visible') && $(Elem2).is(':visible')){
    			if(errMssg != "") alert(errMssg);
    			ElemFocus.focus();
    			return false;
    		}
		}
		return true;
	}
	return true;
}

function checkRequiredNumField(object, minval, maxval, errMssg){
	var Elem = document.getElementById(object);
	var fMin = parseFloat(minval);
	var fMax = parseFloat(maxval);

	if (Elem){
		var fVal = parseFloat(Elem.value);
		if ((Elem.value == "" || fVal < fMin || fVal > fMax) && $(Elem).is(':visible')){
			if(errMssg != "") alert(errMssg);
			Elem.focus();
			return false;
		}
		return true;
	}
	return true;
}

function checkRequiredArrayField(object, maxobject, objtype, errMssg){
	var MaxVal = document.getElementById(maxobject).value;
	var rtn = false;

	if (MaxVal > 0){
		for(i=0; i<MaxVal; i++) {
			var Elem = document.getElementById(object+'['+i+']');
			if(Elem) {
        		if (objtype == 'checkbox' && $(Elem).is(':visible')) {
    				if(Elem.checked) rtn = true;
    			}
			}
		}
		if (!rtn) {
			if(errMssg != "") alert(errMssg);
            document.getElementById(object+'[0]').focus();
            return false;
		}
        return true;
	}
	return true;
}

function checkRequiredArrayNumField(object, maxobject, minval, maxval, errMssg){
	var MaxVal = document.getElementById(maxobject).value;
	var fMin = parseFloat(minval);
	var fMax = parseFloat(maxval);

	if (MaxVal > 0){
		for(i=0; i<MaxVal; i++) {
			var Elem = document.getElementById(object+'['+i+']')
			if(Elem) {
				var fVal = parseFloat(Elem.value);
        		if ((Elem.value == "" || fVal < fMin || fVal > fMax) && $(Elem).is(':visible')) {
                    if(errMssg != "") alert(errMssg);
                    document.getElementById(object+'[0]').focus();
                    return false;
				}
			}
		}
		return true;
	}
	return true;
}

function checkDateField(yearfld, monthfld, dayfld, datefld, errMssg){
    var day = document.getElementById(dayfld).value;
    var month = document.getElementById(monthfld).value;
    var year = document.getElementById(yearfld).value;
	var baddate = false;

	if(isNaN(day) || isNaN(month) || isNaN(year)) baddate = true;

    // This instruction will create a date object
    source_date = new Date(year,month-1,day);

    if(year != source_date.getFullYear() || (month-1) != source_date.getMonth() || day != source_date.getDate()){
		baddate = true;
    }

	if(baddate){
		alert('Date entered is invalid. Please reenter.');
		document.getElementById(yearfld).focus();
		return false;
	}

    // put it all together in one field
    if(datefld != ""){
    	var dateout = document.getElementById(datefld)
		if(dateout != null){
			dateout.value = year+"/"+month+"/"+day;
		}else{
			alert('Date Field not found!');
			return false;
		}
	}

    return true;
}

function validateEmail(object){
	var emailElem = document.getElementById(object);

	if (emailElem){
		if(emailElem.value == ""){
			alert("Please enter the email address.");
			emailElem.focus();
			return false;
		}

		var emailPat = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    	var matchArray = emailElem.value.match(emailPat);
		if (matchArray == null) {
    		alert("Please enter a valid email address.");
			emailElem.focus();
			return false;
    	}

		return true;
	}

	return true;
}


function compareTheString(object1, object2, errMssg){
	var str1 = document.getElementById(object1);
	var str2 = document.getElementById(object2);

	if (str1 && str2){
		if(str1.value == str2.value){
			return true;
		}else{
			if(errMssg != "") alert(errMssg);
			str2.focus();
			return false;
		}
	}

	return true;
}

function checkMinChars(object, minLength, errMssg){
	var Elem = document.getElementById(object);

	if (Elem){
		if (Elem.value.length < minLength && Elem.style.display != 'none'){
			if(errMssg != "") alert(errMssg);
			Elem.focus();
			return false;
		}else{
			return true;
		}
	}

	return true;

}

function validateChars(object, errMssg){
	var iChars = "!@#$%^&*()+=-[]\\\';,./{}|\":<>?";
	var Elem = document.getElementById(object);

	if (Elem){
		if(Elem.style.display != 'none'){
			for (var i = 0; i < Elem.value.length; i++) {
				if (iChars.indexOf(Elem.value.charAt(i)) != -1) {
					if(errMssg != "") alert(errMssg);
					Elem.focus();
					return false;
				}
			}
		}
	}

	return true;
}


function checkExtension(object, ext, errMssg){
	var allowed = false;
	var ext_array = ext.split("-");

	var Elem = document.getElementById(object);

	if (Elem){
		if (Elem.value != "" && $(Elem).is(':visible')){
			var theExt = Elem.value.toLowerCase();

			for (var i=0; i<ext_array.length; i++){
				if(Elem.value.lastIndexOf(ext_array[i].toLowerCase())!=-1) {
					allowed = true;
				}
			}

			if (!allowed){
				if(errMssg != "") alert(errMssg);
				Elem.focus();
   				return false;
			}
		}

		return true;
	}

	return true;
}

function checkPositiveInt(object){
	var Elem = document.getElementById(object);

	var thisint = parseInt(Elem.value);
   	var thisfloat = parseFloat(Elem.value);

	if(Elem){
		if(Elem.style.display != 'none'){
			if (thisint != thisfloat) {
				alert("You didn't enter an integer!")
				Elem.focus()
				return false;
			}else if (thisint <= 0){
				alert("Please enter a value greater than zero.");
				Elem.focus()
				return false;
			}
		}
	}

	return true;
}

