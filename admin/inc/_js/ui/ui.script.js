/**
 * Foundry UI Scripts
 * @author Chris Donalds (chrisd@navigatormm.com)
 * @requires jQuery 1.4.4+
 */
/* ------------------------------------------------------------------------------------- */
var jQx = 0;
var jQy = 0;

jQuery(function($) {
    // IE 7/8 specific script handler
    if ($.browser.msie) {
        var elm = $("#admloginbox");
        if(elm.length > 0){
            var pos = $(elm).position();
            elm.after("<div id='admloginbox-ie-shadow'></div>");
            $("#admloginbox-ie-shadow").width($(elm).width() + 30).height($(elm).height() + 30).css("left", (pos.left - 15) + "px").css("top",(pos.top + 151) + "px");
        }
    }

	if (document.getElementById('dyncontainer')) {
		$("#dyncontainer").sortable({
			opacity: 0.6,
			cursor: 'move',
			revert: true
		});
	}
	if (document.getElementById('draggable')) {
		$("#draggable").draggable({
			connectToSortable: '#dyncontainer',
			helper: 'clone',
			revert: 'invalid'
		});
	}

	$("#helptabs").tabs();
	$("#settingstabs").tabs();
	$("#tabs-themes").tabs();
	$("#tabs-menus").tabs();
	$("#tabs-plugins").tabs();
	$("#tabs-adv").tabs();
	$("#cfgdiv").tabs();

	$('body').append('<div id="hovertip_div"></div>');
});

jQuery(function($){
	$(document).ready(function(){
		var base_url = $('#base_url').val();
	    var admin_core_url = $('#admin_folder').val();
        if(admin_core_url == undefined) admin_core_url = base_url + 'inc/_core/';

	    $("#helpdialog").dialog({
			autoOpen: false,
			width: 750,
			height: 600,
			modal: true,
	        resizable: false
	    });

	    $("#hintdialog").dialog({
			autoOpen: false,
			width: 500,
			modal: true,
	        resizable: false
	    });

	    $("#stats_content").dialog({
			autoOpen: false,
			height: 300,
			width: 400,
			modal: false,
	        resizable: false
	    });

	    $("#info_content").dialog({
			autoOpen: false,
			height: 300,
			width: 400,
			modal: false,
	        resizable: false
	    });

	    $("#mapdialog").dialog({
			autoOpen: false,
			height: 400,
			width: 400,
			modal: false,
	        resizable: false
	    });

	    $("#sendpanel").dialog({
			autoOpen: false,
			width: 600,
			modal: false,
	        resizable: false
	    });

	    $("#genpanel").dialog({
			autoOpen: false,
			width: 600,
	        height: 400,
			modal: true,
	        resizable: false
	    });

	    $("#genpanel2").dialog({
			autoOpen: false,
			width: 600,
	        height: 600,
			modal: true,
	        resizable: false
	    });

	    $("#genpanel3").dialog({
			autoOpen: false,
			width: 450,
	        height: 400,
			modal: true,
	        resizable: false
	    });

	    $(".accordion").accordion({
	        autoHeight: false,
	        navigation: true,
	        collapsible: true,
	        header: '.cfgpanel'
	    });

        // Help

        $('.triggerhelp').click(function(e){
            e.preventDefault();
            $('#help a').trigger('click');
        });

        $('#help_phpcfg_link').click(function(e){
            e.preventDefault();
            $.post(
                admin_core_url+"ajaxwrapper.php",
                {'op':'getphpinfo'},
                function(html){
                    if(html != ''){
                        $('#help_phpcfg').html(html);
                        $('#help_phpcfg').show();
                    }
                }
            );
        });

        $('#aboutbtn').click(function(e){
            e.preventDefault();
            $('#helpdialog').load(
                base_url + 'aboutdialog.php',
                function(){
                    $('#helpdialog').dialog('open');
                });
            return false;
        });

	    // Form Control

	    if(base_url != undefined){
	    	$.getScript(base_url+'inc/_js/ui/ui.script_form.js');
	    }

	    // Hover Tip (tm)

	    $(document).delegate('.hovertip', 'mouseenter', function(e){
	    	var contents = $(this).attr('alt');
	    	if(contents != ''){
	    		$('#hovertip_div').html(contents);
	    	}
	    });
	    $(document).delegate('.hovertip', 'mousemove', function(e){
	    	if($(this).attr('alt') != ''){
	    		var hw = $('#hovertip_div').width();
	    		var hh = $('#hovertip_div').height();
	    		var ww = $(document).width();
	    		var wh = $(document).height();
	    		var px = ((e.pageX + 20 + hw < ww) ? e.pageX + 20 : ww - hw);
	    		var py = ((e.pageY + 4 + hh < wh) ? e.pageY + 4 : wh - hh);
		    	$('#hovertip_div').show();
		        $('#hovertip_div').css({
		            left:  px,
		            top:   py
		        });
	    	}
	    });
	    $(document).delegate('.hovertip', 'mouseleave', function(e){
	    	$('#hovertip_div').hide();
	    });

	    // Settings Dialog

	    $("#settingsdialog").mouseover(function(e){
	        jQx = e.pageX - this.offsetLeft;
	        jQy = e.pageY - this.offsetTop;
	    });

	    $("#pluginsettingsdialog").dialog({
			autoOpen: false,
			width: 600,
	        height: 400,
			modal: true,
	        resizable: false
	    });

        // Themes

	    $("#chgtheme").click(function(){
	        var root = $("#themebody").attr("rel");
	        var val = $("#themechgsel").val();
	        if(val != ''){
	            $.ajax({
	                type: "POST",
	                url: root+"ajaxwrapper.php",
	                data: "op=updatethemefields&val="+val,
	                success: function(msg){
	                    var msgarry = msg.split('|');
	                    alert(msgarry[1]);
	                    $("div #themebody").html(msgarry[2]);
	                    $("#curtheme").html("'"+val+"'");
	                }
	            });
	        }
	        return false;
	    });

	    $("#copytheme").click(function(){
	        var root = $("#themebody").attr("rel");
	        var newtheme = $("#themename").val();
	        if(newtheme != ''){
	            $.ajax({
	                type: "POST",
	                url: root+"ajaxwrapper.php",
	                data: "op=copytheme&val="+newtheme,
	                success: function(msg){
	                    var msgarry = msg.split('|');
	                    alert(msgarry[1]);
	                    if(msgarry[0] == '1'){
	                        var options = $('#themechgsel').attr('options');
	                        options[options.length] = new Option(newtheme, newtheme, true, true);
	                        $("#themechgsel").val('');
	                        options = $('#themeremsel').attr('options');
	                        options[options.length] = new Option(newtheme, newtheme, true, true);
	                        $("#themeremsel").val('');
	                        $("#themename").val('');
	                    }
	                }
	            });
	        }else{
	            alert("Please enter a unique name of the new theme.");
	        }
	        return false;
	    });

	    $("#remtheme").click(function(){
	        var root = $("#themebody").attr("rel");
	        var val = $("#themeremsel").val();
	        if(val != ''){
	            if(confirm("Are you sure you want to remove '"+val+"'?")){
	                $.ajax({
	                    type: "POST",
	                    url: root+"ajaxwrapper.php",
	                    data: "op=remtheme&val="+val,
	                    success: function(msg){
	                        var msgarry = msg.split('|');
	                        alert(msgarry[1]);
	                        if(msgarry[0] == '1' || msgarry[0] == '2'){
	                            $("#themechgsel option[value='"+val+"']").remove();
	                            $("#themechgsel").val('');
	                            $("#themeremsel option[value='"+val+"']").remove();
	                            $("#themeremsel").val('');
	                            if(msgarry[0] == '2'){
	                                $("div #themebody").html(msgarry[2]);
	                                $("#curtheme").html("'Default'");
	                            }
	                        }
	                    }
	                });
	            }
	        }
	        return false;
	    });

        // Menus

        $('#adminmenu_subnavigation').sortable({
            opacity: 0.8
        });

        $('#adminmenu_navigation').sortable({
            opacity: 0.8,
            stop: function(){
                var newmenus = [];
                $('#adminmenu_navigation li').each(function(){
                    newmenus.push($(this).attr('id').substring(8));
                });
                $.ajax({
                    type: "POST",
                    url: admin_core_url+"ajaxwrapper.php",
                    data: "op=updateadminmenulayout&val="+newmenus,
                    success: function(){
                    }
                });
            }
        });

        $('.adminmenu_topelem').click(function(e){
            e.preventDefault();
            var rel = $(this).attr('rel');
            var mnu = $(this).parent();
            $('#adminmenu_navigation li').each(function(){
               $(this).removeClass('chosen selected').addClass('unchosen');
            });
            mnu.removeClass('unchosen').addClass('chosen selected');
  	        $.post(
	            admin_core_url+"ajaxwrapper.php",
	            {op:'getadminmenueditorhtml', val:rel, level:'top'},
	            function(html){
                    var html_parts = html.split("||");
                    $('#adminmenu_editor').html(html_parts[0]);
                    $('#adminmenu_subnavigation').html(html_parts[1]);
	            }
            );
        });

        $('ul').delegate('.adminmenu_subelem', 'click', function(e){
            e.preventDefault();
            var rel = $(this).attr('rel');
            var mnu = $(this).parent();
            $('#adminmenu_subnavigation li').each(function(){
               $(this).removeClass('chosen selected').addClass('unchosen');
            });
            mnu.removeClass('unchosen').addClass('chosen selected');
            $('#adminmenu_navigation li.selected').removeClass('selected');
  	        $.post(
	            admin_core_url+"ajaxwrapper.php",
	            {op:'getadminmenueditorhtml', val:rel, level:'sub'},
	            function(html){
                    $('#adminmenu_editor').html(html);
	            }
            );
        });

        $('div').delegate('#adminmenu_savetop', 'click', function(e){
            e.preventDefault();
            var key   = $('#adminmenu_code').val();
            var title = $('#adminmenu_title').val().replace(/(\|)/g, '');
            var table = $('#adminmenu_table').val();
            if(key != 'pages'){
                var alias = $('#adminmenu_filealias').val().replace(/([^a-z0-9_\-])/ig, '');
                var targettype= $('#adminmenu_target').val();
            }else{
                var alias = '';
                var targettype = '';
            }
            var errmsg= '';
            if(table == '- Unknown -' && key != 'pages') errmsg = 'The table is required';
            $('#adminmenu_navigation .unchosen a').each(function(){
                if($(this).text().toLowerCase() == title.toLowerCase()) errmsg = 'The menu title must be unique';
            });
            if(title == '') errmsg = 'The menu title is required.';
            if(errmsg != '') {
                alert(errmsg);
            }else{
                $.post(
                    admin_core_url+"ajaxwrapper.php",
                    {op:'saveadmintopmenu', key:key, title:title, table:table, alias:alias, targettype:targettype, level:'top'},
                    function(jsondata){
                        if(jsondata.success){
                            alert('The menu changes have been saved.');
                            $('#adminmenu_navigation .chosen a').text(title);
                        }else{
                            alert('There was a problem saving the menu changes.');
                        }
                    },
                    "json"
                );
            }
            return false;
        });

        $('div').delegate('#adminmenu_savesub', 'click', function(e){
            e.preventDefault();
            var key   = $('#adminmenu_code').val();
            var parent= $('#adminmenu_parent').val();
            var title = $('#adminmenu_title').val().replace(/(\|)/g, '');
            var table = $('#adminmenu_table').val();
            var alias = $('#adminmenu_filealias').val().replace(/([^a-z0-9_\-])/ig, '');

            var errmsg= '';
            if(table == '- Unknown -') errmsg = 'The table is required';
            $('#adminmenu_subnavigation .unchosen a').each(function(){
                if($(this).text().toLowerCase() == title.toLowerCase()) errmsg = 'The menu title must be unique';
            });
            if(title == '') errmsg = 'The menu title is required.';
            if(errmsg != '') {
                alert(errmsg);
            }else{
                $.post(
                    admin_core_url+"ajaxwrapper.php",
                    {op:'saveadminsubmenu', key:key, parent:parent, title:title, table:table, alias:alias, level:'sub'},
                    function(jsondata){
                        if(jsondata.success){
                            alert('The menu changes have been saved.');
                            $('#adminmenu_subnavigation .chosen a').text(title);
                        }else{
                            alert('There was a problem saving the menu changes.');
                        }
                    },
                    "json"
                );
            }
            return false;
        });

        $('div').delegate('#adminmenu_table, #adminmenu_filealias, #adminmenu_target', 'blur', function(){
            var table = $('#adminmenu_table').val();
            var alias = $('#adminmenu_filealias').val().replace(/([^a-z0-9_\-])/ig, '');
            var target = $('#adminmenu_target').val();
            $.post(
                admin_core_url+"ajaxwrapper.php",
                {op:'getadminmenutarget', table:table, alias:alias, targettype:target},
                function(html){
                    if(html != '') $('.adminmenu_targeturl').html(html);
                });
            return false;
        });

	    // Plugins

	    $('div').delegate('.plugin_row', 'mouseover', function(){
	        $(this).css('background-color','#efefef').find('.plugin_actions').show();
	    });

	    $('div').delegate('.plugin_row', 'mouseout', function(){
	        $(this).css('background-color','#fff').find('.plugin_actions').hide();
	    });

	    $('#plugin_bulkcheck, #plugin_fw_bulkcheck').click(function(){
	    	var state = $(this).is(':checked');
	    	$('.plugin_checks').each(function(){
	    		if($(this).is(':visible')) $(this).attr('checked', state);
	    	});
	    });

	    $('div').delegate('.plugin_act', 'click', function(e){
	        e.preventDefault();
	        var elem = $(this);
	        var p_row = elem.closest('.plugin_row');
	        var p_id = p_row.find('.plugin_id').val();
	        var tostate = 0;
	        if(elem.text() == 'Activate') tostate = 1;
	        $.post(
	            admin_core_url+"ajaxwrapper.php",
	            {op:'setpluginactivestate', val:tostate, id:p_id},
	            function(jsondata){
	                if(jsondata.success){
	                    if(jsondata.rtndata == 1){
	                        // activated
	                        elem.text('Deactivate');
	                        p_row.find('.plugin_name').removeClass('notactive');
	                    }else{
	                        // deactivated
	                        elem.text('Activate');
	                        p_row.find('.plugin_name').addClass('notactive');
	                    }
	                    p_row.find('.plugin_info').hide('fast');
	                    p_row.find('.plugin_more').text('More');
	                }
	            },
	            'json');
	        e.stopPropagation();
	        return false;
	    });

	    $('div').delegate('.plugin_del', 'click', function(e){
	        e.preventDefault();
	        var p_row = $(this).closest('.plugin_row');
	        var p_id = p_row.find('.plugin_id').val();
	        var p_name = p_row.find('.plugin_title').val();
	        var p_slug = p_row.find('.plugin_slug').val();
	        if(confirm("Do you want to delete the plugin '" + p_name + "'?")){
	            $.post(
	                admin_core_url+"ajaxwrapper.php",
	                {op:'deleteplugin', val:'', id:p_id},
	                function(jsondata){
	                    var lead = '<br/>&nbsp;&nbsp;&bull;&nbsp;';
	                    if(jsondata.success){
	                        $('#plugin_problem').append(jsondata.rtndata.row);
	                        if(jsondata.rtndata.setting != '') $('#plugin_setting_'+jsondata.rtndata.setting).remove();
	                        p_row.hide("blind", {direction: "vertical"}, 300)
	                        p_row.remove();
	                    }else{
	                        $('#issue-plugins').removeClass('disabled');
	                        $('#issue-plugins').append(lead + jsondata.rtndata);
	                    }
	                },
	                'json');
	        }
	        e.stopPropagation();
	        return false;
	    });

	    $('div').delegate('.plugin_undel', 'click', function(e){
	        e.preventDefault();
            var elem = $(this);
	        var p_row = elem.closest('.plugin_row');
	        var p_id = p_row.find('.plugin_id').val();
	        var p_slug = p_row.find('.plugin_slug').val();
	        $.post(
	            admin_core_url+"ajaxwrapper.php",
	            {op:'undeleteplugin', val:'', id:p_id},
	            function(jsondata){
	                if(jsondata.success){
	                    alert('The plugin has been restored.');
	                    $('#plugin_installed').append(jsondata.rtndata.row);
	                    if(jsondata.rtndata.setting != '') $('#plugin_settings').append(jsondata.rtndata.setting);
	                    p_row.hide("blind", {direction: "vertical"}, 300)
	                    p_row.remove();
	                }else{
	                    alert('The plugin could not be restored.');
	                }
	            },
	            'json');
	        e.stopPropagation();
	        return false;
	    });

	    $('div').delegate('.plugin_scrap', 'click', function(e){
	        e.preventDefault();
            var elem = $(this);
	        var p_row = elem.closest('.plugin_row');
	        var p_id = p_row.find('.plugin_id').val();
	        var p_name = p_row.find('.plugin_title').val();
	        if(confirm("Do you want to permanently scrap the plugin '" + p_name + "'?")){
	            $.post(
	                admin_core_url+"ajaxwrapper.php",
	                {op:'scrapplugin', val:'', id:p_id},
	                function(jsondata){
	                    var lead = '<br/>&nbsp;&nbsp;&bull;&nbsp;';
	                    if(jsondata.success){
	                    	p_row.effect("pulsate", {times: 2}, 200, function(){p_row.remove();});
	                    }else{
	                        $('#issue-plugins').removeClass('disabled');
	                        $('#issue-plugins').append(lead + jsondata.rtndata);
	                    }
	                },
	                'json');
	        }
	        e.stopPropagation();
	        return false;
	    });

	    $('div').delegate('.plugin_fix', 'click', function(e){
	    	// part 1: prepare and open repair dialog
	        e.preventDefault();
            var elem = $(this);
	        var p_row = elem.closest('.plugin_row');
	        var p_id = p_row.find('.plugin_id').val();
	        $('#genpanel3').load(
	            admin_core_url+"ajaxwrapper.php",
	            {op:'getpluginrepairform', id:p_id},
	            function(){
	                $('#genpanel3').dialog('open');
	                $('#ui-dialog-title-genpanel3').text('Plugin Configuration Repair');
	                p_row.attr('id', 'prob_id_'+p_id);
	            }
	        );
	        e.stopPropagation();
	        return false;
	    });

	    $('div').delegate('#plugin_fix_submit', 'click', function(e){
            alert('1');
	    	// part 2: try to repair plugin.info file
	        e.preventDefault();
	        var p_row = $('#prob_id_'+$('#subj_plugin_id').val());
	        var form_data = $('#plugin_repair_form').serialize();
	        if(!e.isPropagationStopped()){
		        $.post(
		            admin_core_url+"ajaxwrapper.php",
		            {op:'repairplugincfg', val:form_data},
		            function(jsondata){
		                if(jsondata.success){
		                    $('#plugin_installed').append(jsondata.rtndata.row);
		                    if(jsondata.rtndata.setting != '') $('#plugin_settings').append(jsondata.rtndata.setting);
		                    p_row.hide(400);
		                    p_row.remove();
		                    $('#issues-plugins').html(jsondata.rtndata.plugins_issues);
		                    $('#issues').html(jsondata.rtndata.settings_issues);
		                    if($('#issues-plugins').text == '') $('#issues-plugins').hide();
		                    if($('#issues').text == '') $('#issues').hide();
		                    alert('The plugin.info file has been repaired.');
		                	$('#genpanel3').dialog('close');
		                }else{
		                    alert('Unable to repair the plugin.info file because:\n\n'+jsondata.rtndata);
		                }
		            },
		            "json");
	        }
	        e.stopPropagation();
	        return false;
	    });

	    $('div').delegate('.plugin_more, .plugin_more_prob', 'click', function(e){
	        e.preventDefault();
	        var elem = $(this);
	        var p_row = elem.closest('.plugin_row');
	        var is_prob = ((elem.attr('class') == 'plugin_more_prob') ? '_prob' : '');
	        if(elem.text() == 'More'){
	            // show
	            var p_id = p_row.find('.plugin_id').val();
	            $.post(
	                admin_core_url+"ajaxwrapper.php",
	                {op:'getplugindata', val:is_prob, id:p_id},
	                function(jsondata){
	                    if(jsondata.success){
	                        p_row.find('.plugin_info'+is_prob).html(jsondata.rtndata).show(500);
	                        elem.text('Less');
	                    }
	                },
	                'json');
	        }else{
	            // hide
	            p_row.find('.plugin_info'+is_prob).hide(500);
	            elem.text('More');
	        }
	        e.stopPropagation();
	        return false;
	    });

        $('div').delegate('.plugin_datamod', 'blur', function(){
            var elem = $(this);
            var rel = elem.attr('rel');
            var val = elem.val();
            if(val != ''){
	            $.post(
	                admin_core_url+"ajaxwrapper.php",
	                {op:'updateplugindata', val:val, rel:rel},
	                function(jsondata){
	                    if(jsondata.success){
                            alert('The setting was updated.');
	                    }
	                },
	                'json');
            }else{
                alert('The value cannot be blank.  Nothing was saved.');
            }
            return false;
        });

	    $('div').delegate('.plugin_settings_link', 'click', function(e){
	        e.preventDefault();
	        var settingsfunc = $(this).attr('rel');
	        if(settingsfunc != ''){
		        $.post(
		            admin_core_url+"ajaxwrapper.php",
		            {op:'runpluginsettingsfunc', val:settingsfunc},
		            function(jsondata){
		                if(jsondata.contents != ''){
		                	// build and show dialog box & save button
		                    var save_button = '<p class="clearfix" style="float: right"><input type="button" id="plugin_settings_dialog_save" rel="'+settingsfunc+'" value="Save"/></p>';
                            var contents = '<form action="" method="POST" id="pluginsettingsform">' + jsondata.contents + save_button + '</form>';
		                    $('#pluginsettingsdialog').html(contents).dialog('open').dialog('option', 'title', jsondata.title);
		                    $('#pluginsettingsdialog').attr('rel', settingsfunc);
		                }else{
		                	alert("There was a problem calling the settings function '"+jsondata.func+"' in this plugin.");
		                }
		            },
		            'json');
	        }
	        e.stopPropagation();
	        return false;
	    });

	    $('div').delegate('#plugin_settings_dialog_save', 'click', function(e){
	        e.preventDefault();
	        var elem = $(this);
	        var settingsfunc = elem.attr('rel');
            var settingsdata = $('#pluginsettingsform').serialize();
	        if(settingsfunc != '' && !e.isPropagationStopped()){
	            $.post(
	                admin_core_url+"ajaxwrapper.php",
	                {op:'runpluginsettingsaction', val:settingsfunc, action:'1', data:settingsdata},
	                function(jsondata){
	                    if(jsondata.success){
	                    	// respond to save button and close dialog
	                        if(jsondata.message != '') alert(jsondata.message);
	                        if(jsondata.closedialog) {
	                        	$('#pluginsettingsdialog').dialog('close');
	                        	elem.attr('rel', '');
	                        }
	                    }
	                },
	                'json');
	        }
	        e.stopPropagation();
	        return false;
	    });

	    $('#pluginsettingsdialog').bind("dialogbeforeclose", function(e) {
	        var elem = $(this);
	        var settingsfunc = elem.attr('rel');
	        if(settingsfunc != '' && !e.isPropagationStopped()){
	        	elem.attr('rel', '');
	        	$.post(
	                admin_core_url+"ajaxwrapper.php",
	                {op:'runpluginsettingsaction', val:settingsfunc, action:'2'},
	                function(jsondata){
	                    if(jsondata.success){
	                    	// respond to close button (x)
	                        if(jsondata.message != '') alert(jsondata.message);
	                        $('#pluginsettingsdialog').dialog('close');
                        }
	                    e.stopPropagation();
                        return false;
	                },
	                'json');
	        }
	    });

	    $('div').delegate('.plugin_help_link', 'click', function(e){
	        e.preventDefault();
            var elem = $(this);
	        var helpfile = elem.attr('rel');
	        var p_row = elem.closest('.plugin_row');
	        var p_id = p_row.find('.plugin_id').val();
	        var p_name = p_row.find('.plugin_title').val();
	        $.post(
	                admin_core_url+"ajaxwrapper.php",
	                {op:'getsettingshelpfile', val:helpfile},
	                function(html){
	                	$('#genpanel3').html(html);
	                    $('#genpanel3').dialog('open');
	                	$('#ui-dialog-title-genpanel3').text('Plugin Help - '+p_name);
	                },
	                'html'
	        );
	        e.stopPropagation();
	        return false;
	    });

	    $('#plugin_bulkact, #plugin_fw_bulkact').click(function(e){
	    	e.preventDefault();
	    	var opt = (($(this).attr('id') == 'plugin_bulkact') ? $('#plugin_bulkopt').val() : $('#plugin_fw_bulkopt').val());
	    	if(opt != ''){
	    		$('.plugin_checks').each(function(){
	    			if($(this).is(':checked') && $(this).is(':visible')){
	    				if(opt == 'deactivate'){
	    					$(this).siblings('.plugin_actions').find('.plugin_act').trigger('click');
	    				}else if(opt == 'delete'){
	    					$(this).siblings('.plugin_actions').find('.plugin_del').trigger('click');
	    				}
	    			}
	    		});
	    	}
	    });

	    // Users

	    $('.user_row').mouseover(function(){
	        $(this).css('background-color','#efefef').find('.user_actions').show();
	    });

	    $('.user_row').mouseout(function(){
	        $(this).css('background-color','#fff').find('.user_actions').hide();
	    });

	    $('div').delegate('.user_act', 'click', function(e){
	        e.preventDefault();
	        var elem = $(this);
	        var u_row = elem.closest('.user_row');
	        var u_id = u_row.find('.user_id').val();
	        var u_adms = parseInt($('#users_admins').val());
	        var tostate = 0;
	        if(elem.text() == 'Activate') tostate = 1;
	        if(u_adms < 2 && tostate == 0){
	            alert('This user is the last active admin and cannot be deactivated.');
	        }else{
		        $.post(
		            admin_core_url+"ajaxwrapper.php",
		            {op:'setuseractivestate', val:tostate, id:u_id},
		            function(jsondata){
		                if(jsondata.success){
		                    if(jsondata.rtndata == 1){
		                        // activated
		                        elem.text('Deactivate');
		                        u_row.find('.user_name').removeClass('notactive');
		                        u_adms++;
		                    }else{
		                        // deactivated
		                        elem.text('Activate');
		                        u_row.find('.user_name').addClass('notactive');
		                        u_adms--;
		                    }
		                    $('#users_admins').val(u_adms);
		                }
		            },
		            'json');
	        }
	        return false;
	    });

	    $('div').delegate('.user_del', 'click', function(e){
	        e.preventDefault();
            var elem = $(this);
	        var u_row = elem.closest('.user_row');
	        var u_id = u_row.find('.user_id').val();
	        var u_name = u_row.find('.user_name').text();
	        var u_cur = u_row.find('.user_cur').val();
	        var u_count = parseInt($('#users_count').val());
	        if(u_count < 2 && u_id > 0){
	            alert('This user is the last user account and cannot be deleted.');
	            return false;
	        }
	        if(u_cur != ''){
	            alert('You cannot delete your own account.');
	            return false;
	        }
	        if(confirm("Delete the user '" + u_name + "'?\n\nThis cannot be undone.")){
	            if(u_id > 0){
	                $.post(
	                    admin_core_url+"ajaxwrapper.php",
	                    {op:'deleteuserdata', val:'', id:u_id},
	                    function(jsondata){
	                        if(jsondata.success){
	                            $('users_count').val(u_count--);
	                            $('#issue-users').html('User "' + u_name + '" has been deleted.').removeClass('disabled');
                                u_row.effect("pulsate", {times: 2}, 200, function(){u_row.remove();});
	                        }else{
	                            $('#issue-users').html(jsondata.rtndata).removeClass('disabled');
	                        }
	                    },
	                    'json');
	            }else{
	                $('users_count').val(u_count--);
	                u_row.remove();
	            }
	        }
	        return false;
	    });

	    $('div').delegate('.user_editprofile', 'click', function(e){
	        e.preventDefault();
	        var u_row = $(this).closest('.user_row');
	        u_profile_div = u_row.find('.user_profile');
	        u_profile_div.toggle('slow');
	        $(this).text((($(this).text() == 'Hide Profile') ? 'View' : 'Hide')+' Profile');
	        return false;
	    });

	    $('#admnewuser').click(function(e){
	        e.preventDefault();
	        var u_count = parseInt($('#users_count').val());
	        $.post(
	            admin_core_url+"ajaxwrapper.php",
	            {op:'addnewuser', val:u_count},
	            function(data){
	                $('#tabs-users').append(data);
	                $('users_count').val(u_count++);
	            },
	            'text');
	        return false;
	    });

	    $('#user_bulkcheck').click(function(){
	    	var state = $(this).is(':checked');
	    	$('.user_checks').each(function(){
	    		if($(this).is(':visible')) $(this).attr('checked', state);
	    	});
	    });

	    $('#user_bulkact').click(function(e){
	    	e.preventDefault();
	    	var opt = $('#user_bulkopt').val();
	    	if(opt != ''){
	    		$('.user_checks').each(function(){
	    			if($(this).is(':checked')){
	    				if(opt == 'deactivate'){
	    					$(this).siblings('.user_actions').find('.user_act').trigger('click');
	    				}else if(opt == 'delete'){
	    					$(this).siblings('.user_actions').find('.user_del').trigger('click');
	    				}
	    			}
	    		});
	    	}
	    });

        // Advanced

        // Data Aliases

        $(document).delegate('.dataalias_meta', 'blur', function(){
            var elem = $(this);
            var meta = elem.val();
            $.post(
                admin_core_url+"ajaxwrapper.php",
                {'op':'validatedataaliasmeta', 'val':meta},
                function(jsondata){
                    var notice = elem.siblings('.dataalias_notice');
                	if(jsondata.success){
                        notice.removeClass('red').addClass('gray');
                        notice.html(jsondata.rtndata);
                	}else{
                        notice.removeClass('gray').addClass('red');
                        notice.html(jsondata.rtndata);
                	}
                    elem.val(elem.val().toLowerCase());
                },
                "json"
            );
            return false;
        });

	    // Mod-Rewrites

	    $(".ht_toggle").click(function(){
	       var tag = $(this).attr('id').substring(0, 6) + '_data';
	       var label = $(this).text();
	       if(label == 'Less'){
	           $('#'+tag).hide(500);
	           $(this).text('More');
	       }else{
	           $('#'+tag).show(200);
	           $(this).text('Less');
	       }
	    });

	    $(".ht_seo_from, .ht_seo_to, .ht_301_from, .ht_301_to").delegate('change', function(){
	        var orig = $(this).attr('rel');
	        $("#ht_mod1").val((($(this).val() != orig) ? 'y' : ''));
	    	return false;
	    });

	    $(".ht_seo_active, .ht_301_active").delegate('click', function(){
	        var orig = $(this).attr('rel');
	        if($(this).attr('checked')){
	            $("#ht_mod1").val(((orig == '') ? 'y' : ''));
	        }else{
	            $("#ht_mod1").val(((orig == 'y') ? 'y' : ''));
	        }
	    	return false;
	    });

	    $(".ht_delete").delegate('click', function(e){
	    	e.preventDefault();
	    	if(confirm("Delete this row?")){
	    		$(this).closest("div").remove();
	    	}
	    	return false;
	    });

	    $("#ht_www1").change(function(){
	        ht_sel($(this).val(), 'www');
	    });

	    $("#ht_www_data").keyup(function(){
	        $("#ht_www1").val('');
	        $("#ht_mod2").val('y');
	    });

	    $("#ht_img1").change(function(){
	        ht_sel($(this).val(), 'img');
	    });

	    $("#ht_img_data").keyup(function(){
	        $("#ht_img1").val('');
	        $("#ht_mod2").val('y');
	    });

	    function ht_sel(sel, sect){
	        if(sel == ''){
	            $("#ht_"+sect+"_data").html($("#"+sect+"_data").val());
	            $("#ht_mod2").val('');
	        }else if(sel == 'disable'){
	            var data = $("#ht_"+sect+"_data").html().split(/(\r\n|\n|\r)/gm);
	            for(var i in data){
	                var line = data[i].replace(/(\r\n|\r|\n)/, '');
	                if(line.substr(0, 1) != '#' && line != '') data[i] = '#'+line;
	            }
	            $("#ht_"+sect+"_data").html(data.join(''));
	            $("#ht_mod2").val('y');
	        }else{
	            $("#ht_"+sect+"_data").html($("#"+sel).val());
	            $("#ht_mod2").val('y');
	        }
	    }

	    // Robots

	    $("#robots_revision").click(function(e){
	    	e.preventDefault();
	    	var file = $("#robots_revfile").val();
	        $.get(admin_core_url+file, function(data){
	        	if(confirm("The selected file's contents are:\n\n"+data+"\nIs this the one you want?")){
	                $.post(
	                    admin_core_url+"ajaxwrapper.php",
	                    {op:'revertrobotfile', val:file},
	                    function(jsondata){
	                    	if(jsondata.success){
	                    		alert('The reversion was successful.');
	                    	}else{
	                    		alert('There was a problem trying to revert the robots file.');
	                    	}
	                    },
	                    'json'
	                );
	        	}
	        });
	    });

	    // Colorpicker

	    $("#settingscolorpicker").dialog({
	        autoOpen: false,
	        width: 240,
	        height: 250,
	        modal: true,
	        resizable: false,
	        close: function(){
	            var button_id = $("#colorpicker_buttonid").val();
	            $(button_id).removeClass('active');
	        }
	    });

	    $(document).delegate('.colorpicker_button', 'click', function(){
	        if($('#settingscolorpicker').html() == ''){
		        $.post(
                    admin_core_url+"ajaxwrapper.php",
                    {op:'getcolorpickercontents', val:''},
                    function(html){
                        $('#settingscolorpicker').html(html);
                    },
                    "html"
		        );
	    	}
	        $('#settingscolorpicker').dialog('option', 'position', [jQx, jQy]);
	        $('#settingscolorpicker').dialog('open');

	        var button_id = "#"+this.id;
	        var field_id = this.id;
	        field_id = field_id.substr(3);
	        var field_color = $("#"+field_id).val();
	        $(button_id).addClass('active');

	        $("#colorpicker_buttonid").val(button_id);
	        $("#colorpicker_fieldid").val(field_id);
	        $("#colorpicker_fieldcolor").html(field_color);
	    });

	    $(".colorpicker_swatch").click(function(){
	        var button_id = $("#colorpicker_buttonid").val();
	        var field_id = $("#colorpicker_fieldid").val();
	        var newcolor = $("#colorpicker_hoverdiv").html();
	        $(button_id).css('background-color', newcolor);
	        $(button_id+"__reset").removeClass('disabled');
	        $("#"+field_id).val(newcolor);
	    });

	    $(".colorpicker_swatch").mouseover(function(){
	        $("#colorpicker_hoverdiv").html($(this).attr('title').trim());
	    });

	    $(".colorpicker_reset").click(function(){
	        var oldcolor = $(this).attr('rel');
	        var this_id = $(this).attr('id');
	        var button_id = "#"+this_id.substring(0, this_id.indexOf('__reset'));
	        var field_id = "#"+button_id.substring(4);
	        $(button_id).css('background-color', oldcolor);
	        $(button_id+"__reset").addClass('disabled');
	        $(field_id).val(oldcolor);
	    });

	    $("#colorpicker_closebutton").click(function(){
	        $("#settingscolorpicker").dialog('close');
	        return false;
	    });
	});
});