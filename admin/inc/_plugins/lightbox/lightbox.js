/**
 * @author Chris
 */
/* LightBox Script
--------------------------------------------------------------------------------------- */
$(function() {
	var host = window.location.hostname;
	if(host == 'stonehenge' || host == 'badger' || host == 'www.navigatordns.com' || host == 'www.navigatormultimedia.com'){
		var fpath = window.location.pathname;
	var parts = fpath.split('/');
	var path  = '/'+parts[1];
	}else{
		path = '';
	}
	$('#gallery a').lightBox({
	  	overlayBgColor:	'#000000',
		overlayOpacity:	0.8,
		imageLoading:	path+'/admin/inc/js/lightbox/lightbox-ico-loading.gif',
		imageBtnClose:  path+'/admin/inc/js/lightbox/lightbox-btn-close.gif',
		imageBtnPrev:	path+'/admin/inc/js/lightbox/lightbox-btn-prev.gif',
		imageBtnNext:	path+'/admin/inc/js/lightbox/lightbox-btn-next.gif',
		imageBlank:		path+'/admin/inc/js/lightbox/lightbox-blank.gif',
		containerResizeSpeed: 500,
		txtImage:		'Photo',
		txtOf:			'of',
		keyToClose:		'c',
		keyToPrev:		'p',
		keyToNext:		'n',
		fixedNavigation:true
	   });
});

/* LightBox Script
--------------------------------------------------------------------------------------- */
$(document).ready(function(){
    $('.content input, .content textarea, .content select').focus(function(){
        $(this).parents('.right').addClass("over");
    }).blur(function(){
        $(this).parents('.right').removeClass("over");
    });
});

/* DETECT NAVIGATION ON STATE SCRIPT
--------------------------------------------------------------------------------------- */
$(document).ready(function(){
	var url = location.pathname.substring(1);
	var path = url.split("/");
	//path.shift();  /required only if its tested under a subfolder
	if(path.length>1){
		path.pop();
		path='/'+path.join('/')+'/';
	}
	else path=path[0];

	if (url) {
		$('#nav li a[href*="' + url + '"]').addClass('selected');
	} else {
		$('.home a').addClass('selected');
	}

});