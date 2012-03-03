/* ------------------------------------------------------------------------------------
Author: 		Chris Satterthwaite
Updated: 		May.18.2010
Updated By: 	Chris Satterthwaite
--------------------------------------------------------------------------------------- */
/* Start The document Ready function
--------------------------------------------------------------------------------------- */
$(document).ready(function(){
    /* Form Script
    ----------------------------------------------------------------------------------- */
	$('input, textarea, select').focus(function(){
		$(this).addClass("over");
		}).blur(function(){
		$(this).removeClass("over");
	});
});


/* Email Hide Script
--------------------------------------------------------------------------------------- */
function parse_email(user, server, domain, subject) {
	if(user && server && domain){
		emailto = "mailto:"+user+'@'+server+'.'+domain+"?subject="+subject;
		window.location = emailto;
	}
}

