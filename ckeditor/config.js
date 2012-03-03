/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.uiColor = '#AADC6E';
    var host = window.location.hostname;
    if(host == 'localhost' || host == 'stonehenge' || host == 'badger' || host == 'navigatormultimedia.com' || host == 'www.navigatormultimedia.com'){
        var fpath = window.location.pathname;
        var parts = fpath.split('/');
        var dir  = '/'+parts[1];
    }else{
        dir = '';
    }
    config.filebrowserBrowseUrl = dir+'/ckfinder/ckfinder.html';
    config.filebrowserImageBrowseUrl = dir+'/ckfinder/ckfinder.html?Type=Images';
    config.filebrowserFlashBrowseUrl = dir+'/ckfinder/ckfinder.html?Type=Flash';
    config.filebrowserUploadUrl = dir+'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
    config.filebrowserImageUploadUrl = dir+'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
    config.filebrowserFlashUploadUrl = dir+'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
    config.uiColor = '# 9AB8F3';
    config.toolbar = 'Full';
    config.width = '750px';
    config.resize_maxWidth = 750;
    config.resize_minWidth = 750;
    config.resize_maxHeight = 520;
    config.startupFocus = true;
    config.contentsCss = dir+'/inc/_css/master.css';
    config.colorButton_colors = 'FFFFFF,666666,000000';
    config.extraPlugins = 'ImagesetMarker';
    config.format_tags = 'p;h2;h3;h4;h5;h6;pre;address;div';        // default is p;h1;h2;h3;h4;h5;h6;pre;address;div
    //config.enterMode = CKEDITOR.ENTER_BR;
    //config.scayt_autoStartup = true;

    config.toolbar_Custom =
    [
        ['Save','Preview'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord'],
		'/',
        ['Undo','Redo','-','Find','Replace','SelectAll'],['Maximize', 'ShowBlocks'],['Print', 'SpellChecker', 'Scayt'],
        '/',
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        '/',
        ['Styles','Format','FontSize'],['TextColor'],
        ['Link','Unlink','Anchor'],
        ['Image','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','ImagesetMarker']
    ];

    config.toolbar_CustomWithSource =
    [
        ['Source','-','Save','Preview'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord'],
		'/',
        ['Undo','Redo','-','Find','Replace','SelectAll'],['Maximize', 'ShowBlocks'],['Print', 'SpellChecker', 'Scayt'],
        '/',
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        '/',
        ['Styles','Format','FontSize'],['TextColor'],
        ['Link','Unlink','Anchor'],
        ['Image','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','ImagesetMarker']
    ];

    config.toolbar_FormatOnly =
    [
        ['Cut','Copy','Paste','PasteText','PasteFromWord'],
        ['Print', 'SpellChecker', 'Scayt'],
        ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
        '/',
        ['Format','FontSize'],['TextColor'],
        ['Link','Unlink','Anchor'],
		'/',
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Image','Table','HorizontalRule','Smiley','PageBreak','SpecialChar','ImagesetMarker']
    ];

    config.toolbar_FormatOnlyWithSource =
    [
        ['Source','-','Cut','Copy','Paste','PasteText','PasteFromWord'],
        ['Print', 'SpellChecker', 'Scayt'],
        ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
        '/',
        ['Format','FontSize'],['TextColor'],
        ['Link','Unlink','Anchor'],
		'/',
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Image','Table','HorizontalRule','Smiley','PageBreak','SpecialChar','ImagesetMarker']
    ];
};

