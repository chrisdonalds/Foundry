// retrieve dynamic values from page required to execute functions
var json = $('#fileuploader_params').val();
if(json == '' || json == undefined) {
	alert('Fileuploader.core.js parameter loading error!');
}else{
	json = unescape(json);
	var params = JSON.parse(json);
	var app_folder 			= params['app_folder'];
	var web_folder			= params['web_folder'];
	var dest_folder 		= params['dest_folder'];
	var dest_folder_svr 	= params['dest_folder_svr'];
	var dest_folder_web 	= params['dest_folder_web'];
	var uploadFilename 		= params['uploadFilename'];
	var width 				= params['width'];
	var height 				= params['height'];
	var hoverparent;

	function createUploader(elem, lastfile, lastthm, allowedExt, fileType){
		var is_image = false;
		var is_av = false;
		var deletebutton = '';

		var prepimgedit = '';
		var prepimgonclick = '';
		var prepavplay = '';
		var prepfiledld = '';
		var filetypelabel = '';
		var imgedit_included = ($('#imgeditordialog').length > 0);

		if(fileType == 'imgedit') {
			if(imgedit_included){
				prepimgedit = '<span id="' + elem + '_edit" class="qq-upload-edit" /><a href="" id="qq-upload-link-' + elem + '" class="qq-upload-imgedit" rel="' + elem + '">Edit Image</a></span>';
				prepimgonclick = ' title="Click to edit"';
			}
			filetypelabel = 'Image';
			is_image = true;
		} else if(fileType == 'img') {
			filetypelabel = 'Image';
			is_image = true;
		} else if(fileType == 'av') {
			prepavplay = '<span id="' + elem + '_edit" class="qq-upload-edit" /><a href="" id="qq-upload-link-' + elem + '" target="_blank">Play this File</a></span>';
			filetypelabel = 'File';
			is_av = true;
		} else {
			prepfiledld = '<span id="' + elem + '_edit" class="qq-upload-edit" /><a href="" id="qq-upload-link-' + elem + '" target="_blank">Download this File</a></span>';
			filetypelabel = 'File';
		}

		if(lastfile != ''){
			deletebutton = '<div class="qq-upload-delete" id="' + elem + '_del" rel="' + elem + '">Delete</div>';
		}

		var uploader = new qq.FileUploader({
			element: document.getElementById(elem),
			template: '<div class="qq-uploader">' +
				'<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>' +
				'<div class="qq-upload-group">' +
				'<div class="qq-upload-button" id="' + elem + '_but">Select ' + filetypelabel + '</div>' +
				deletebutton +
				'</div>' +
				'<ul class="qq-upload-list" id="qq-upload-itemlist-' + elem + '"></ul>' +
				'</div>',
			fileTemplate: '<li id="qq-upload-item-' + elem + '">' +
				'<img src="' + app_folder + 'images/blank.png" name="' + elem + '_img" id="' + elem + '_img" class="qq-upload-img qq-upload-imgedit qq-upload-hovertrig"' + prepimgonclick + ' />' +
				'<span class="qq-upload-file" id="' + elem + '_filetxt"></span>' +
				'<br/><span class="qq-upload-spinner" id="' + elem + '_spinner"></span>' +
				'<span class="qq-upload-size" id="' + elem + '_sizetxt"></span>' +
				'<img src="' + app_folder + 'images/blank.png" width="16" height="16" id="' + elem + '_cue" class="qq-upload-cue">' +
				prepimgedit +
				prepavplay +
				prepfiledld +
				'<a class="qq-upload-cancel" href="" id="' + elem + '_cancellink">Cancel</a>' +
				'<span class="qq-upload-failed-text" id=' + elem + '_failedtxt">Failed</span>' +
				'</li>',
			action: app_folder + 'process.php?dest=' + dest_folder_svr + '&file=' + uploadFilename,
			allowedExtensions: allowedExt,
			onSubmit: function(id, fileName){
				var qq = document.getElementById("qq-upload-itemlist-" + elem);
				if(qq.childNodes.length > 0) {
					var qc = document.getElementById("qq-upload-item-" + elem);
					qq.removeChild(qc);
				}
			},
			onComplete: function(id, fileName, responseJSON){
                fileName = responseJSON.filename;
				var fld = $('#' + elem + '_fld');
				var img = $('#' + elem + '_img');
				var edt = $('#' + elem + '_edit');
				var lnk = $('#qq-upload-link-' + elem);

				// delete the last file uploaded to temp folder (if any)
				if(fld.val() != '' && fld.val() != dest_folder + fileName){
					$.post(
						app_folder + 'delete.php',
						{'file':fld.val(), 'delrec':1, 'page_url':$('#page_url').val(), 'row_id':$('#row_id').val()},
						function(jsondata){
						},
						"json"
					);
				}

				fld.val(dest_folder + fileName);
				if(is_image){
					img.attr('src', dest_folder_web + fileName + "?" + new Date().getTime());
					img.attr('rel', elem);
					//if(width > 0) img.css('width', width+"px");
					if(height > 0) img.css('height', height+"px");
					img.show().css('display', 'block');
					$('#' + elem + '_dim').val(responseJSON.dim);
				}else if(is_av){
					if(lnk.length > 0) lnk.attr('href', dest_folder_web + fileName);
				}else{
					if(lnk.length > 0) lnk.attr('href', dest_folder_web + fileName);
				}
				$('#' + elem + '_cue').attr('src', app_folder + 'images/star.png');
				$('#' + elem + '_mod').val("fileuploader");
				if(edt.length > 0) edt.show();
			},
			messages: {
				// error messages, see fileuploader.js for details
				typeError: "{file} has an invalid extension. Only {extensions} are allowed.",
				sizeError: "{file} is too large, maximum file size is {sizeLimit}.",
				minSizeError: "{file} is too small, minimum file size is {minSizeLimit}.",
				emptyError: "{file} is empty, please select a file with content.",
				onLeave: "The files are being uploaded, if you leave now the upload will be cancelled."
			},
			showMessage: function(message){
				alert(message);
			}
		});

		//create preconstucted elements if we are recalling a file
		if(lastfile != ''){
			var cur_ul = $('#qq-upload-itemlist-' + elem);
			var css_width, css_height;
			if(cur_ul){
				var fileparts = lastfile.split("/");
				var lastfilename = fileparts[fileparts.length - 1];
				var new_li = '<li id="qq-upload-item-' + elem + '" class="qq-upload-success">';
				if(is_image){
					/* where to try to see if thumbnail exists */
					if(width > 0) css_width = 'width: '+width+'px; ';
					if(height > 0) css_height = 'height: '+height+'px; ';
					new_li += '<img style="' + css_height + 'display: block; border: 1px solid black;" src="' + web_folder + lastthm + '" name="' + elem + '_img" id="' + elem + '_img" class="qq-upload-img qq-upload-imgedit qq-upload-hovertrig"' + prepimgonclick + ' rel="' + elem + '" />';
					new_li += '<img style="display: none;" src="' + web_folder + lastfile + '" name="' + elem + '_orig" id="' + elem + '_orig" class="qq-upload-img qq-upload-imgorig qq-upload-hoverbox" />';
				}
				new_li += '<span class="qq-upload-file" id="' + elem + '_filetxt">' + lastfilename + '</span>';
				new_li += '<br/><span class="qq-upload-size" id="' + elem + '_sizetxt">--</span>';
				new_li += '<img src="' + app_folder + 'images/blank.png" id="' + elem + '_cue" class="qq-upload-cue" style="display: none" height="16" width="16">';
				var new_li_edit = '<span id="' + elem + '_edit" class="qq-upload-edit">';
				if(imgedit_included){
					new_li += new_li_edit + '<a href="" id="qq-upload-link-' + elem + '" class="qq-upload-imgedit" rel="' + elem + '">Edit Image</a></span>';
				}else if(is_av){
					new_li += new_li_edit + '<a href="' + web_folder + lastfile + '" id="qq-upload-link-' + elem + '" target="_blank">Play File</a></span>';
				}else if(!is_image && !is_av){
					new_li += new_li_edit + '<a href="' + web_folder + lastfile + '" id="qq-upload-link-' + elem + '" target="_blank">Download File</a></span>';
				}
				new_li += '<span class="qq-upload-failed-text" id="' + elem + '_failedtxt">Failed</span>';
				cur_ul.append(new_li);
			}
		}
	}

	$(document).delegate('.qq-upload-hovertrig', 'mouseenter', function(){
		hoverparent = $('.qq-upload-hoverbox').parent();
		//$('.qq-upload-hoverbox').appendTo('#wrapper').show();
	});
	$(document).delegate('.qq-upload-hovertrig', 'mouseleave', function(){
		//$('.qq-upload-hoverbox').appendTo(hoverparent).hide();
	});

	$(document).delegate('.qq-upload-delete', 'click', function(e){
		e.preventDefault();
		var elem = $(this).attr('rel');
		var fld = $('#'+elem + '_fld');
		var mod = $('#'+elem + '_mod');
		var img = $('#'+elem + '_img');
		var fil = $('#'+elem + '_filetxt');
		var edt = $('#'+elem + '_edit');
		var lnk = $('#qq-upload-link-' + elem);
		if(fil.length > 0){
			// temp file (selected during current page session): delete only
			// real file (attached to record): delete file and clear record data
			var delrec = ((mod.val() == '') ? 1 : 0);
			$.post(
				app_folder + 'delete.php',
				{'file':fld.val(), 'delrec':delrec, 'page_url':$('#page_url').val(), 'row_id':$('#row_id').val()},
				function(jsondata){
					if(jsondata.success){
						fld.val('');
						fil.html('');
						mod.val('deleted');
						$('#'+elem + '_sizetxt').html('');
						$('#'+elem + '_cue').attr('src', app_folder + 'images/blank.png');
						$('#'+elem + '_del').hide();
						if(img.length > 0) img.attr('src', app_folder + 'images/blank.png').hide();
						if(edt.length > 0) edt.html('');
						if(lnk.length > 0) lnk.html('').attr('href', '');
					}
				},
				"json"
			);
		}
	});
}