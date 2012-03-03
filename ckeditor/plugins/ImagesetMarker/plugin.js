/**
 *  --------------------------------------------------------------------------------------
 *  Navigator Scripts
 *  ----------------------------------------------------
 *  Modified	April 12, 2010
 *  @version	1.0.0
 *  @author	Chris Donalds <chrisd@navigatormm.com>
 *  ----------------------------------------------------
 *  --------------------------------------------------------------------------------------
 */

/**
 * @file Imageset Marker Placeholder
 */

// Register a plugin named "imagesetmarker".
(function(){
	var imagesetmarkerCmd = {
		canUndo : true,    // The undo snapshot will be handled by 'insertElement'.
		exec : function(editor){
			//editor.insertElement(editor.document.createElement('hr'));
            //editor.insertHtml('<div class="imagesetmarker"></div>');
            var element = CKEDITOR.dom.element.createFromHtml('<hr class="imagesetmarker" title="Imageset Placeholder"/>');
            editor.insertElement(element);
		}
	};

	var pluginName = 'ImagesetMarker';

	// Register a plugin named "imagesetmarker".
	CKEDITOR.plugins.add(pluginName, {
		init : function(editor){
            editor.addCss('.imagesetmarker {height: 50px; background-color: rgb(170, 170, 170); border: 2px dashed black;}');
            editor.resize_enabled = false;
			editor.addCommand(pluginName, imagesetmarkerCmd);
			editor.ui.addButton('ImagesetMarker', {
                label : editor.lang.ImagesetMarker,
                icon: this.path + 'images/imagesetmarker.png',
                command : pluginName
            });
		}
	});
})();