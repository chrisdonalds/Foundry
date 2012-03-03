/**
 *  --------------------------------------------------------------------------------------
 *  ImgEdit Core Javascript File
 *  --------------------------------------------------------------------------------------
 *  Modified	January 7, 2012
 *  @version	1.5.0
 *  @author	Chris Donalds <chrisd@navigatormm.com>
 *  @see jquery.imgedit.js for jCrop module
 *  --------------------------------------------------------------------------------------
 */

// retrieve dynamic values from page required to execute functions
var json = $('#imgedit_params').val();
if(json == '' || json == undefined){
	alert('Imgedit.core.js parameter loading error!');
}else{
	json = unescape(json);
	var params = JSON.parse(json);
	var web_url				= params['web'];
	var svr_path 			= params['svr'];
	var destfolder			= params['destfolder'];
	var ImgEditThmDims 		= params['thmdims'];
	var nosrc				= params['nosrc'];

	var ImgEditObj;
	var ImgEditMod = false;
	var ImgEditModField = '';
	var ImgEditScale = 1;
	var ImgEditAspect = 1;
	var ImgEditThmAspect = 1;
	var ImgEditThmWidth = 100;
	var ImgEditThmHeight = 100;
	var webimgsrc;
	var webthmsrc;
	var imgobj;
	var imgdim;
	var fileloc;
	var filemod;
	var imgeditloading;

	$("#imgedit_tabs").tabs();

    $("#imgeditordialog").dialog({
		autoOpen: false,
		width: 730,
        height: 600,
        top: 0,
		modal: true,
        resizable: false
    });

    $("#imgeditorlibrarydialog").dialog({
		autoOpen: false,
		width: 600,
        height: 500,
		modal: true,
        resizable: false
    });

	/* scroll zones */

	var $imgedit_dialog = $('#imgeditordialog');
	var $imgedit_cropleft = $("#imgedit_cropleft");
	var $imgedit_cropthumb = $("#imgedit_cropright");
	var imgedit_croptop;

	/* trigger opening of dialog */

	jQuery(document).delegate('.qq-upload-imgedit', 'click', function(e){
		e.preventDefault();

		// prep variables
		var imgelem = $(this).attr('rel');
		fileloc = imgelem+'_fld';
		filemod = imgelem+'_mod';
		imgobj  = imgelem+'_img';
		imgdim  = imgelem+'_dim';
		if(fileloc != '') {
			var imgsrc = jQuery('#'+fileloc).val();
			if(imgsrc == '') imgsrc = nosrc;
		}else{
			var imgsrc = nosrc;
		}
		webimgsrc = web_url + imgsrc;
		webthmsrc = $('.qq-upload-imgedit').attr('src');
		ImgEditAjaxFunction('saveinitialtemp', svr_path + imgsrc);

		// update editor file location and name textboxes
		imgeditloading = true;
		jQuery('#imgeditordialog').dialog('open');
		jQuery('#imgeditfileloc').val(imgsrc);
		jQuery('#imgeditfilename').html(imgsrc);

		// load the source image for use by ImgEditor
		jQuery('#imgedit_src').attr('src', webimgsrc);
		prepImgEditor();
	});

    function prepImgEditor(){
        // get original and thumbnail image dimensions and set the aspect ratio
        var imgwidth = parseInt(jQuery('#imgedit_src').css('width'));
        var imgheight = parseInt(jQuery('#imgedit_src').css('height'));
		if(jQuery('#'+imgdim).val() != '|'){
			// fallback method for getting width and height
			var dims = jQuery('#'+imgdim).val();
			var dim_split = dims.split('|');
			imgwidth = dim_split[0];
			imgheight = dim_split[1];
		}
		if(imgwidth > 0 && imgheight > 0) ImgEditAspect = imgwidth / imgheight;

		// resize the thumbnail preview box to the specific thumbnail dimensions
		if(parseInt(ImgEditThmDims[0])) ImgEditThmWidth = ImgEditThmDims[0];
		if(parseInt(ImgEditThmDims[1])) ImgEditThmHeight = ImgEditThmDims[1];
		jQuery("#imgedit_cropthumb").css({'width':ImgEditThmWidth+'px', 'height':ImgEditThmHeight+'px'});
		ImgEditThmAspect = ImgEditThmWidth / ImgEditThmHeight;

		// copy crop source to all other source images
		$('#imgedit_cropsrc').attr('src', $('#imgedit_src').attr('src'));
		$('#imgedit_rotsrc').attr('src', $('#imgedit_src').attr('src'));
		$('#imgedit_effectsrc').attr('src', $('#imgedit_src').attr('src'));
		$('#imgedit_colorsrc').attr('src', $('#imgedit_src').attr('src'));

		// scale images to the maximum size allowed for the editor
		var scalesize = constrainImage(imgwidth, imgheight, (620 - ImgEditThmWidth), 1000);
		jQuery('#imgedit_cropsrc').css({'width':scalesize[2]+'px', 'height':scalesize[3]+'px'});
		jQuery('#imgedit_rotsrc').css({'width':scalesize[2]+'px', 'height':scalesize[3]+'px'});
		jQuery('#imgedit_effectsrc').css({'width':scalesize[2]+'px', 'height':scalesize[3]+'px'});
		jQuery('#imgedit_colorsrc').css({'width':scalesize[2]+'px', 'height':scalesize[3]+'px'});
		ImgEditScale = (scalesize[0] / scalesize[2]);

		// finalize and show
		ImgEditModField = filemod;
		loadImgEditorThumbs();
		loadImgEditorImages();

		// move scrollbar to top
		$imgedit_dialog.scrollTop(0);
		imgedit_croptop = $imgedit_cropleft.position().top;
	}

	function constrainImage(width, height, maxwidth, maxheight){
		var scalew = 1;
		var scaleh = 1;
		var scale = 1;
		if(maxwidth > 0 && width > maxwidth){
			scalew = maxwidth / width;
		}
		if(maxheight > 0 && height > maxheight){
			scaleh = maxheight / height;
		}
		if(scalew < scaleh && scalew > 0) scale = scalew;
		if(scaleh < scalew && scaleh > 0) scale = scaleh;
		newwidth = Math.round(width * scale);
		newheight = Math.round(height * scale);

		return [width, height, newwidth, newheight];
	}

	function loadImgEditorThumbs(){
		jQuery('#imgedit_thumb').attr('src', webthmsrc);
		jQuery('#imgedit_thumb').css({
			'height': ImgEditThmHeight + 'px',
			'margin-top': '0px',
			'margin-left': '0px'
		});
	}

	function loadImgEditorImages(){
		// resets the image then loads the object into memory
		if(ImgEditObj) ImgEditObj.destroy();
		attachJCropObj();
		imgeditloading = false;
	}

	/* Crop */

	function attachJCropObj(){
		// initiate jCrop tool here
		ImgEditObj = $.Jcrop('#imgedit_cropsrc', {
			onSelect: showImgEditorCropPreview,
            onChange: showImgEditorCropPreview,
            aspectRatio: ImgEditThmAspect,
            keySupport: false
		});
	}

	function showImgEditorCropPreview(coords){
		var rx = ImgEditThmWidth / coords.w;
		var ry = ImgEditThmHeight / coords.h;
		var rw = parseInt(jQuery('#imgedit_cropsrc').css('width'));
		var rh = parseInt(jQuery('#imgedit_cropsrc').css('height'));

		if($('#imgedit_thumb').attr('src') != $('#imgedit_cropsrc').attr('src')){
			// initial state of thumbnail
			$('#imgedit_thumb').attr('src', $('#imgedit_cropsrc').attr('src'));
		}else{
			// thumbnail produced while controlling image editor
			$('#imgedit_thumb').css({
				width: Math.round(rx * rw) + 'px',
				height: Math.round(ry * rh) + 'px',
				marginLeft: '-' + Math.round(rx * coords.x) + 'px',
				marginTop: '-' + Math.round(ry * coords.y) + 'px'
			});

			updateCoords(coords);
			ImgEditMod = true;
		}
	};

	function updateCoords(c){
		$('#x').val(c.x);
		$('#y').val(c.y);
		$('#w').val(c.w);
		$('#h').val(c.h);
		jQuery('#cropx').html(parseInt(c.x * ImgEditScale));
		jQuery('#cropy').html(parseInt(c.y * ImgEditScale));
		jQuery('#cropw').html(parseInt(c.w * ImgEditScale));
		jQuery('#croph').html(parseInt(c.h * ImgEditScale));
	}

	function checkCoords(){
		if (parseInt($('#w').val()) || ImgEditMod) return true;
		//alert('Please select a crop region then press `Update & Close`.');
		// nothing to do.  Just close gracefully
		jQuery('#imgeditordialog').dialog('close');
		return false;
	}

	/* All */

	$(document).delegate('#imgeditorupdate', 'click', function(){
		if(checkCoords()){
			ImgEditMod = false;
			var filepath = jQuery('#imgeditfileloc').val();
			var fileparts = filepath.split('/');
			var file = fileparts[fileparts.length - 1];
			var destfilepath = destfolder + 'thm_' + file;
			var x = $('#x').val() * ImgEditScale;
			var y = $('#y').val() * ImgEditScale;
			var w = $('#w').val() * ImgEditScale;
			var h = $('#h').val() * ImgEditScale;
			ImgEditAjaxFunction('update', svr_path + filepath, svr_path + destfilepath, ImgEditThmWidth, ImgEditThmHeight, x, y, w, h);
			jQuery('#'+ImgEditModField).val('thumbmod');
			jQuery('#imgeditordialog').dialog('close');
		}
	});

	$(document).delegate('#imgeditorclose', 'click', function(){
		var oktoclose = true;
		if(ImgEditMod){
			if(!confirm('The image was modified.  Discard changes?')) oktoclose = false;
		}
		if(oktoclose){
			ImgEditMod = false;
			jQuery('#imgeditordialog').dialog('close');
		}
	});

	$('#imgeditordialog').scroll(function(){
		var top = $imgedit_dialog.scrollTop() - imgedit_croptop;
		if(top < 0) top = 0;
		if($('#imgedit_cropright').is(':visible')){
			$imgedit_cropthumb
            	.stop()
            	.animate({"marginTop": top + "px"}, "fast" );
		}
	});

	/* Library functions */

	$(document).delegate('#imgeditorlibraryclose', 'click', function(){
		closeImgEditorLibrary();
	});

	$(document).delegate('#imgeditorlibraryselect', 'click', function(){
		var filespec = jQuery('#imgeditfileloc').val();
		ajaxUploadFunction("imgeditfileloc", "image", "imgupload", filespec);
		closeImgEditorLibrary();
	});

	function closeImgEditorLibrary(){
		jQuery('#imgeditorlibrarydialog').dialog('close');
	}
}