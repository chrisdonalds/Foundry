/**
 * @author Chris
 */
function startUpload(){
	document.getElementById('upload_dropshadow').style.visibility = 'visible';
	return true;
}

function stopUpload(success, file){
	var result = '';
	if (success == ''){
		result = 'All files uploaded successfully!';
	} else {
		result = 'There was an error uploading:<br/>' + file;
	}
	document.getElementById('msg').innerHTML = result;
	if (success == '') {
		setTimeout('hideUploadDiv()', 2000);
	} else {
		document.getElementById('process_bar').style.visibility = 'hidden';
		document.getElementById('process_bar').style.display = 'none';
	}
	return true;   
}

function hideUploadDiv(){
	document.getElementById('upload_dropshadow').style.visibility = 'hidden';
	document.getElementById('upload_dropshadow').style.display = 'none';
}
