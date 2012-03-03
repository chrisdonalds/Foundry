/**
 * Foundry UI Scripts -- Form Control
 * @author Chris Donalds (chrisd@navigatormm.com)
 * @requires jQuery 1.4.4+ and ui.script.js
 */
/* ------------------------------------------------------------------------------------- */
jQuery(function($){
	$(document).ready(function(){
		var base_url = $('#base_url').val();
	    var page_url = $('#page_url').val();
	    var page_subject = $('#page_subject').val();
	    var page_ingroup = $('#page_ingroup').val();
	    var page_parentgroup = $('#page_parentgroup').val();
	    var page_childsubject = $('#page_childsubject').val();
	    var full_delete = $('#full_delete').val();

		// Search

	    $("#search_content_toggle").click(function(e){
	    	e.preventDefault();
	        if($(this).hasClass('toggleclose')){
	            $("#search_content").hide("blind", { direction: "vertical" }, 500);
	            $(this).attr('class', 'toggleopen').css('background-position', 'top');
	        }else{
	            $("#search_content").show("blind", { direction: "vertical" }, 500);
	            $(this).attr('class', 'toggleclose').css('background-position', 'bottom');
	        }
	        return false;
	    });

	    $("#page_search").click(function(e){
	    	$('#cmd').val(cmd_val);
	    	$('#list_form').submit();
	    });

	    // List Pages

	    $('.listcol-sort').click(function(e){
	    	e.preventDefault();
	    	var dir = $(this).attr('rel');
	    	$('#sort_dir').val(dir);
	    	$('#page_search').trigger('click');
	    });

	    $('.list_button_page_prev, .list_button_page_next').click(function(e){
	    	e.preventDefault();
	    	var rel = $(this).attr('rel');
	    	$('#page').val(rel);
	    	$('#list_form').submit();
	    });

	    // List Page Bulk Action/Checkboxes

	    $('html').click(function(){
	    	// hide bulk option box
	    	var this_id = $(this).attr('id');
	    	if(this_id != 'listrow-check-act' && this_id != 'listrow-check-optdiv' && this_id != 'listrow-check-opt'){
		    	$('#listrow-check-optdiv').hide();
	    	}
	    });

	    $('#listrow-check-act').click(function(e){
	    	e.stopPropagation();
	    	var chkstate = $(this).is(':checked');
	   		$('#listrow-check-optdiv').show();
	   		$('#listrow-check-opt').val('');
	    });
	    $('#listrow-check-optdiv, #listrow-check-opt').click(function(e){
	    	e.stopPropagation();
	    });
	    $('#listrow-check-opt').change(function(){
	    	var action = $(this).val();
	    	var more = ((full_delete && action == 'delete') ? ' (this cannot be undone)' : '');
	    	$('#listrow-check-optdiv').hide();

	    	if(action != '' && action != '-' && action != 'select_all' && action != 'deselect_all'){
	    		var numselected = $('.listrow-check:checked').length;
	    		if(numselected > 0){
		    		var pluralsubject = ((numselected > 1) ? page_subject.pluralize() : page_subject);
	    	    	if(!confirm('Are you sure you want to '+action.toUpperCase()+' the selected '+pluralsubject+more + '?')){
	    	    		action = ''; // do nothing...
	    	    		return false;
	    	    	}
	    		}else{
	    			alert('Nothing was selected to '+action.toUpperCase()+'.');
	    			return false;
	    		}
	    	}

	    	$('.listrow').each(function(){
	    		var check_elem = $(this).find('.listrow-check');
	    		if(action == 'select_all'){
	    			check_elem.attr('checked', true);
	    		}else if(action == 'deselect_all'){
	    			check_elem.attr('checked', false);
	    		}else if(action != '' && action != '-' && check_elem.is(':checked')){
	    			var action_elem = $(this).find('.action_'+action);
		    	    var row_id   = action_elem.attr('rel');
	    			if(row_id != '' && row_id != null){
		    	    	var altparam = action_elem.attr('altparam');
		    	    	var altgroup = action_elem.attr('altgroup');
		    	    	var addquery = action_elem.attr('addquery');
		    	    	var result = do_listrow_action(action, row_id, true);
	    			}
	    		}
	    	});
	    });

	    // List Page Hover Box

	    $('.listrow-hoverbox').mouseenter(function(e){
	    	var rel = $(this).attr('rel');
	    	$('#'+rel).appendTo('body');
	    });
	    $('.listrow-hoverbox').mouseover(function(e){
	    	var rel = $(this).attr('rel');
	    	$('#'+rel).show();
	        $('#'+rel).css({
	            left:  e.pageX,
	            top:   e.pageY
	        });
	    });
	    $('.listrow-hoverbox').mouseout(function(){
	    	var rel = $(this).attr('rel');
	    	$('#'+rel).hide();
	    });

	    // List Page Row Actions

	    $('.listtable').delegate('.listrow', 'mouseover', function(){
            var hl = $(this).attr('class');
	        $(this).addClass('listrow-hover').find('.list-actions').show();
            $(this).attr('rel', hl);
	    });

	    $('.listtable').delegate('.listrow', 'mouseout', function(){
            var hl = $(this).attr('rel');
	        $(this).attr('class', hl).find('.list-actions').hide();
	    });

	    $(document).delegate('.action_publish, .action_delete, .action_archive, .action_activate, .action_unpublish, .action_undelete, .action_unarchive, .action_deactivate', 'click', function(){
	    	var row_id   = $(this).attr('rel');
	    	var action   = $(this).attr('class').substr(7);
	    	var altparam = $(this).attr('altparam');
	    	var altgroup = $(this).attr('altgroup');
	    	var addquery = $(this).attr('addquery');
	    	var more     = ((full_delete && action == 'delete') ? ' (this cannot be undone)' : '');
	    	if(confirm('Are you sure you want to ' + action.toUpperCase() + ' this '+page_subject+more + '?')){
	    		var result = do_listrow_action(action, row_id, true);
	    	}
	    });

	    $(document).delegate('.action_edit', 'click', function(){
	    	var row_id = $(this).attr('rel');
	    	if($(this).hasClass('editfromorg')){
	    		if($('#organize_mod').val() != ''){
	    			if(confirm('You have changed the list order.  Do you want to save the changes first?')){
	    				$('.action_saveorg').trigger('click');
	    			}
	    		}
	    	}
	    	window.location = 'edit-'+page_ingroup+'.php?row_id='+row_id;
	    });

	    $(document).delegate('.action_add', 'click', function(){
	    	var row_id = $(this).attr('rel');
	    	if(!isNaN(row_id) && row_id != null && row_id != ''){
	    		window.location = 'add-'+page_ingroup.replace(/(_cat)/i, '')+'.php?cat_id='+row_id;
	    	}else{
	    		window.location = 'add-'+page_ingroup+'.php';
	    	}
	    });

	    $(document).delegate('.action_view', 'click', function(){
	    	var row_id = $(this).attr('rel');
	    	window.location = 'view-'+page_ingroup+'.php?row_id='+row_id;
	    });

	    $(document).delegate('.action_viewrecs', 'click', function(){
	    	var row_id = $(this).attr('rel');
	    	if(page_childsubject == '' || page_childsubject == undefined || page_childsubject == null) page_childsubject = page_ingroup.replace(/_cat/i, '');
	    	window.location = 'list-'+page_childsubject+'.php?cat_id='+row_id;
	    });

	    $(document).delegate('.action_viewpages', 'click', function(){
	    	var row_id = $(this).attr('rel');
	    	if(page_childsubject == '' || page_childsubject == undefined || page_childsubject == null) page_childsubject = page_ingroup.replace(/_cat/i, '');
	    	window.location = 'list-'+page_childsubject+'.php?cat_id='+row_id;
	    });

	    $(document).delegate('.action_editmeta', 'click', function(){
	    	var row_id = $(this).attr('rel');
	    	window.location = 'edit-meta.php?row_id='+row_id;
	    });

	    $(document).delegate('.action_clone', 'click', function(){
	    	var row_id   = $(this).attr('rel');
	    	var action   = $(this).attr('class').substr(7);
	    	var tag		 = $(this).attr('tag');
			var cloneinput = prompt('Enter a unique title for the new record:', tag+'_new');
			if(cloneinput != '' && cloneinput != null){
				if(tag.toLowerCase() == cloneinput.toLowerCase()) {
				   alert("The new record must have a unique title.");
				}else{
					alert("Attention: Please remember that the new record will share the same images, documents and other uploaded files as the original record.\n\nKeep this in mind when deleting either record.")
					$('#x_data').val(cloneinput);
					var result = do_listrow_action(action, row_id, true);
				}
			}
	    });

	    $(document).delegate('.action_default', 'click', function(){
	    	var row_id = $(this).attr('rel');
	    	var action   = $(this).attr('class').substr(7);
	    	if(confirm('Are you sure you want to set this '+page_subject+' as the gallery default?')){
	    		var result = do_listrow_action(action, row_id, true);
	    	}
	    });

	    // - do the action via AJAX
	    function do_listrow_action(action, row_id, showalerts){
	    	var x_data = $('#x_data').val();
    		var param = {'action':action, 'row_id':row_id, 'x_data':x_data};
    		var pagedata = {'page_url':encodeURIComponent(page_url), 'page_subject':page_subject, 'page_ingroup':page_ingroup};
            $.post(
                base_url+"inc/_core/ajaxwrapper.php",
                {'op':'executelistaction', 'param':param, 'pagedata':pagedata},
                function(jsondata){
                	if(jsondata.success){
                		if(showalerts && jsondata.rtndata.alert != 'null' && jsondata.rtndata.alert != null) alert(jsondata.rtndata.alert);
                		if(jsondata.rtndata.gotopage != 'null' && jsondata.rtndata.gotopage != null){
                			window.location = jsondata.rtndata.gotopage;
                		}else{
                			$('#listbody').html(jsondata.rtndata.html);
                		}
                	}else{
                		if(showalerts) alert('The process to '+action+' the record(s) did not succeed. '+jsondata.rtndata.alert);
                	}
                	return jsondata.success;
                },
                "json"
            );
	    }

	    // List Page Pagination Block Button Actions

	    $(document).delegate('.action_goback', 'click', function(){
	    	if(page_parentgroup == '' || page_parentgroup == undefined || page_parentgroup == null) page_parentgroup = page_ingroup + '_cat';
	    	window.location = 'list-'+page_parentgroup+'.php';
	    });

	    $(document).delegate('.action_organize', 'click', function(){
    		var pagedata = {'page_url':encodeURIComponent(page_url), 'page_subject':page_subject, 'page_ingroup':page_ingroup};
			var tag = $('<div id="organizepanel"></div>'); // This tag will the hold the dialog content.
            $.post(
                base_url+"inc/_core/ajaxwrapper.php",
                {'op':'loadorganizer', 'pagedata':pagedata},
                function(html){
                	// - display the dialog
					tag.html(html)
						.dialog({modal: false, width: 700, title: 'Organize'})
						.dialog('open');
					$("#organize").sortable({
						opacity: 0.6,
						cursor: 'move',
						revert: true,
						update: function(event, ui) { $('#organize_mod').val('touched'); }
					});
                },
                "text"
            );
	    });

	    $(document).delegate('.action_saveorg', 'click', function(){
	    	// - collect the ids in DOM order from the .orgitem objects
	    	var id = 0;
	    	var rtn = '';
	    	var rank = 1;
	    	$('.orgitem').each(function(){
	    		id = $(this).val();
	    		if(rtn != '') rtn += ',';
	    		rtn += id+':'+rank;
	    		rank++;
	    	});
	    	$('#x_data').val(rtn);
	    	var result = do_listrow_action('saveorganize', 0, true);
	    	$('#organizepanel').dialog('close').remove();
	    });

	    // Editor Pages

	    $(document).delegate('.editor_button_status', 'click', function(e){
	    	e.preventDefault();
	    	$('#stats_content').dialog('open');
	    });

	    $(document).delegate('.editor_button_prev_page', 'click', function(e){
	    	e.preventDefault();
	    	window.location = $(this).attr('rel');
	    });

	    $(document).delegate('.editor_button_goback', 'click', function(e){
	    	e.preventDefault();
	    	window.location = "list-"+$(this).attr('rel')+".php";
	    });

	    $(document).delegate('.editor_button_preview', 'click', function(e){
	    	e.preventDefault();
            var url;
	    	if($('#pagename') != undefined){
	    		url = $('#pagename').val();
	    	}else{
	    		url = $('#code').val();
	    	}
    		url = base_url.replace("admin/", "")+url;
    		window.open(url, '_blank');
	    });

	    $(document).delegate('.editor_button_info', 'click', function(e){
	    	e.preventDefault();
	    	$('#info_content').dialog('open');
	    });

	    $(document).delegate('#editpage_setashome', 'click', function(e){
	    	e.preventDefault();
	    	$('#pagealias').val('index');
	    	$('#pagename').val('');
	    });

	    $(document).delegate('.editpage_copydata', 'click', function(e){
	    	e.preventDefault();
	    	var rel = $(this).attr('rel').split(',');
	    	$('#'+rel[1]).val(removeHTMLTags($('#'+rel[0]).val(), false, 255));
	    });
	});
});
