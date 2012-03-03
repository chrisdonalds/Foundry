/**
 *  --------------------------------------------------------------------------------------
 *  ImgEdit Ajax Javascript Complement
 *  --------------------------------------------------------------------------------------
 *  Modified	January 5, 2012
 *  @version	1.5.0
 *  @author	Chris Donalds <chrisd@navigatormm.com>
 *  --------------------------------------------------------------------------------------
 */

function ImgEditAjaxFunction(action, src, dest, fw, fh, x, y, w, h){
	var base_url = $('#base_url').val();

	if(action == 'update'){
		x = parseInt(x);
		y = parseInt(y);
		w = parseInt(w);
		h = parseInt(h);
	    //alert(base_url + 'inc/_plugins/imgedit/imgedit.upload.php?src='+src+'&dest='+dest+'&fw='+fw+'&fh='+fh+'&x='+x+'&y='+y+'&w='+w+'&h='+h);
	    $.post(
	    	base_url + "inc/_plugins/imgedit/imgedit.ajax.php",
	    	{action: action, src: src, dest: dest, fw: fw, fh: fh, x: x, y: y, w: w, h: h}
	    );
	}else if (action == 'savetemp' || action == 'saveinitialtemp'){
	    $.post(
	    	base_url + "inc/_plugins/imgedit/imgedit.ajax.php",
	    	{action: action, src: src}
	    );
	}
}