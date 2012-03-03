	    </div>
	</div>

	<script type="text/Javascript" language="javascript">
	<? if(getRequestVar('qact') == 'settings'){ ?>
		jQuery('#settingsdialog').load(
			'<?=WEB_URL.ADMIN_FOLDER?>settingsdialog.php',
			{parentpage: '<?=$_SERVER['PHP_SELF']?>'},
			function(){
				$('#settingsdialog').dialog('open');
			}
		);
	<?
	}

	showErrorMsg(CORE_ERR);
	showErrorMsg(DEBUGGER_ERR+RUNTIME_ERR);
	echo PHP_EOL;
	?>
	</script>

	<div id="footer">
	    <span class="alignleft">
	        <br />Copyright &copy; <?=date("Y")?> <a href="<?=COPYRIGHT_WEB?>" target="_blank"><?=COPYRIGHT_NAME?></a>. &nbsp;&nbsp;&nbsp;
	    </span>

	    <span class="alignright">
	        <br /><span style="color: blue"><?=SYS_NAME?>: <?= CODE_VER; ?></span>&nbsp;|&nbsp;PHP: <?=phpversion()?><? if(defined('DBHOST')) echo '&nbsp;|&nbsp;MySQL: '.mysql_get_server_info(); ?>
	    </span>
	</div>

	<?
	showHeadlines(true, false);
	?>
</body>
</html>

<head>
	<meta http-equiv="Pragma" content="no-cache"/>
	<meta http-equiv="Expires" content="-1"/>
</head>