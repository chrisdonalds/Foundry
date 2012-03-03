/**
 * @author Chris Donalds (chrisd@navigatormm.com)
 */
/* JQuery UI Administer Plugin
--------------------------------------------------------------------------------------- */

/* ReadCookie Script */
function readCookie(name) {
	var cookiename = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++){
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(cookiename) == 0) return c.substring(cookiename.length,c.length);
	}

	return null;
}

/* JQuery Handler */
$(document).ready(function(){
    if(readCookie('admlogin') != null){
        $("*[admin]").mouseover(function(){
            $(this).addClass('adm_hover');
            var p = $(this).position();
            var o = $(this).offset();
            var w1 = $(this).width();
            var w2 = $("#adm_label").width();
            var left = (parseInt(p.left) + parseInt(o.left) + parseInt(w1) - parseInt(w2)) + "px";
            var top = (p.top + o.top) + "px";
            $("#adm_label").css('left', left);
            $("#adm_label").css('top', top);
            $("#adm_label").css('display', 'inline');
            $("#adm_label").css('z-index', 1000);
        });
        $("*[admin]").mouseout(function(){
            $(this).removeClass('adm_hover');
            $("#adm_label").css('display', 'none');
        });
        $("*[admin]").click(function(){
            // get website root (offset to folder if not live)
            var url = window.location.href;
            var url_parts = url.split('/');
            var domain = url_parts[2];
            domain = domain.replace('www.', '');
            var vhost = '';
            if(domain == 'navigatormultimedia.com' || domain == 'stonehenge' || domain == 'badger' || domain == 'localhost'){
                // offset folder is virtual domain
                vhost = '/'+url_parts[3];
            }
            
            var link = $(this).attr('admin').split(':');
            switch(link[0]){
                case 'page':
                    window.location = vhost+'/admin/pages/edit-userpage.php?row_id=' + link[1];
                    break;
                case 'photos':
                    window.location = vhost+'/admin/photos/edit-photos.php?row_id=' + link[1];
                    break;
                case 'photos_cat':
                case 'gallery':
                    window.location = vhost+'/admin/photos/edit-photos_cat.php?row_id=' + link[1];
                    break;
                case 'event':
                    window.location = vhost+'/admin/events/edit-events.php?row_id=' + link[1];
                    break;
                case 'testimonial':
                    window.location = vhost+'/admin/testimonials/edit-testimonials.php?row_id=' + link[1];
                    break;
                case 'news':
                    window.location = vhost+'/admin/whatsnew/edit-whatsnew.php?row_id=' + link[1];
                    break;
                case 'settings':
                case 'contact':
                case 'contactus':
                    window.location = vhost+'/admin/index.php?qact=settings';
                    break;
            }
        });
    }
});
