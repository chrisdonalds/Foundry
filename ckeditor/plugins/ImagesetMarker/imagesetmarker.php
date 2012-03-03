<?php
/**
 *  --------------------------------------------------------------------------------------
 *  CKEditor Toolbar Plugin
 *  ----------------------------------------------------
 *  Modified	April 12, 2010
 *  @version	1.0.0
 *  @author	Chris Donalds <chrisd@navigatormm.com>
 *  ----------------------------------------------------
 *  --------------------------------------------------------------------------------------
 */

?>
<script language="javascript">
var CKEDITOR = window.parent.CKEDITOR;

var okListener = function(event){
	this._.editor.insertHtml('<?php echo 'content to send in the CKEditor\'s window';?>');
	CKEDITOR.dialog.getCurrent().removeListener("ok", okListener);
};
CKEDITOR.dialog.getCurrent().on("ok", okListener);
</script>

