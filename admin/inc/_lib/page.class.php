<?php

/* PAGE GLOBAL CLASSES */

/**
 * PAGECLASS
 * Stores and manages page structure data
 * - header
 * - footer
 * - menu
 * - help
 * - sectionid
 * - headlines[]
 */
class PageClass {
	// overloaded data
	private $_page = array();
	private $_keys = array("header", "footer", "menu", "help", "sectionid");
	private $_arykeys= array("headlines");
	private $_subkeys = array(
							"search_list", "search_text",
							"search_by", "sort_by",
							"sort_dir", "offset", "limit", "group",
							"pagenum", "uri", "title",
							"row_id", "cat_id", "where_clause",
							"concat", "custom_query",
							"subject", "ingroup",
							"childsubject", "parentgroup",
							"altparams", "altgroups", "addqueries",
							"nonce", "savebuttonpressed", "titlefld",
							"imagefld", "thumbfld");
	protected static $_instance = null;

	private function __construct() {
	}

	private function __clone(){
	}

	public static function init(){
		$s = new self;
		$s->checkCaller("init");
		return $s;
	}

	public function show_properties(){
        foreach($this->_keys as $k) print "[$k] = ".getIfSet($this->_users[$k])."<br>";
        foreach($this->_arykeys as $k)
            if(isset($this->_users[$k])) print "[$k] = <pre>".printr($this->_users[$k])."</pre><br/>";
        foreach($this->_subkeys as $k){
            if(isset($this->_users['subkeys'][$k])) {
                print "[$k] = ";
                if(is_array($this->_users['subkeys'][$k]))
                    print "<pre>".print_r($this->_users['subkeys'][$k], true)."</pre><br/>";
                else
                    print $this->_users['subkeys'][$k]."<br/>";
            }
        }
	}
	
	public function showProperties(){
		printr(array_merge($this->_keys, $this->_arykeys, $this->_subkeys));
	}

	public function __get($name){
		if(in_array($name, $this->_keys)){
			// return scalar value
			return getIfSet($this->_page[$name]);
		}elseif(in_array($name, $this->_arykeys)){
			// return array
			return getIfSet($this->_page[$name]);
		}elseif(in_array($name, $this->_subkeys)){
			// return subarray value
			return getIfSet($this->_page['subkeys'][$name]);
		}else{
			addErrorMsg("Cannot get '$name'.  It is not a valid _PAGE property. ", CORE_ERR);
		}
	}

	public function __set($name, $value){
		if(in_array($name, $this->_keys)){
			// set scalar value
			//$this->checkCaller("set");
			$this->_page[$name] = $value;
		}elseif(in_array($name, $this->_arykeys)){
			// set array
			$this->_page[$name][] = $value;
		}elseif(in_array($name, $this->_subkeys)){
			// set subarray value
			$this->_page['subkeys'][$name] = $value;
		}else{
			addErrorMsg("Cannot set '$name'.  It is not a valid _PAGE property. ", CORE_ERR);
		}
	}

	private function checkCaller($action){
		$stack = debug_backtrace();
		$callerfile = $stack[1]['file'];
		if(strpos($callerfile, "_core") === false){
			die("Calling Page::$action in $callerfile not allowed!");
		}
	}

	/**
	 * Prepare the list page search querying capability.  Returns: $_page->where_clause and $_page->concat
	 * @param array $initarray array("sort_by", "sort_dir", "search_list", "custom_query")
	 * @tutorial 	"sort_by" => default value of sort_by if nothing passed
	 * 				"sort_dir" => default value of sort_dir if nothing passed
	 * 				"search_list" => array("field1", "field2"...)<br/>
	 * 				"custom_query" => array("key matched to search_by" => "SQL query portion")
	 */
	public function prepSearch($initarray = null){
		if(is_array($initarray)){
			foreach($initarray as $key => $initvalue){
				if(in_array($key, $this->_subkeys)){
					if(isBlank($this->_page['subkeys'][$key])){
						$this->_page['subkeys'][$key] = $initvalue;
					}
				}else{
					addErrorMsg("Cannot set search parameter '$key'.  It is not a valid PAGE SEARCH property. ", CORE_ERR);
					return;
				}
			}
		}else{
			addErrorMsg("An array is required as the optional parameter for _page::prepSearch.", CORE_ERR);
			return;
		}

		$search_text = getIfSet($this->_page['subkeys']['search_text']);
		$search_by = getIfSet($this->_page['subkeys']['search_by']);
		$search_list = getIfSet($this->_page['subkeys']['search_list']);
		$where_clause = "";
		$concat = "";
		$search_clause = "";

		if(is_array($search_list)) $search_clause = join(" like '%".$search_text."%' OR ", $search_list);
		if($search_text != "" && $search_by != ""){
		    switch($search_by){
		        case 'all':
		            $where_clause .= $concat." ({$search_clause} like '%$search_text%') ";
		    		$concat = " AND";
		            break;
		        case (isset($custom_query[$search_by])):
		        	$where_clause .= $concat.$custom_query[$search_by];
		        	$concat = " AND";
		        	break;
		        default:
		            $where_clause .= $concat." ".$search_by." like '%$search_text%' ";
		    		$concat = " AND";
		            break;
			}
		}else{
		    switch($search_by){
		        case 'published':
		            $where_clause .= $concat." published = 1 ";
		    		$concat = " AND";
		            break;
		        case 'activated':
		            $where_clause .= $concat." activated = 1 ";
		    		$concat = " AND";
		            break;
		        case 'archived':
		            $where_clause .= $concat." archived = 1 ";
		    		$concat = " AND";
		            break;
		        case 'unpublished':
		            $where_clause .= $concat." published = 0 ";
		    		$concat = " AND";
		            break;
		        case 'deactivated':
		            $where_clause .= $concat." activated = 0 ";
		    		$concat = " AND";
		            break;
		        case 'unarchived':
		            $where_clause .= $concat." archived = 0 ";
		    		$concat = " AND";
		            break;
		        case 'deleted':
		            $where_clause .= $concat." deleted = 1 ";
		    		$concat = " AND";
		            break;
		        case 'draft':
		            $where_clause .= $concat." draft = 1 OR published = 0 ";
		    		$concat = " AND";
		            break;
		        default:
		    	    break;
			}
		}
		$this->_page['subkeys']['where_clause'] = $where_clause;
		$this->_page['subkeys']['concat'] = $concat;
	}
}

/**
 * JSBLOCK
 * Constructs Javascript code
 */
class JSBlock {
	private $output;
	private $checkitems;
	private $jq_output;
	public $subject;
	public $section;
	protected static $_instance = null;

	function __constructor(){
		$this->checkitems = array();
		$this->subject = '';
		$this->section = '';
		$this->output = '';
	}

	private function __clone(){
	}

	public static function init(){
		$s = new self;
		$s->checkCaller("init");
		return $s;
	}

	private function checkCaller($action){
		$stack = debug_backtrace();
		$callerfile = $stack[1]['file'];
		if(strpos($callerfile, "_core") === false){
			die("Calling JSBlock::$action in $callerfile not allowed!");
		}
	}

	/**
	 * Create the main check_form function
	 */
	public function buildCheckFormFunc(){
		$this->output = "$(document).delegate('.editor_button_save', 'click', function(e){\n";
		if(count($this->checkitems)){
			foreach($this->checkitems as $item){
				$this->output .= $item."\n";
			}
		}
		$this->output .= "
		$('#_savebuttonpressed').val($(this).attr('name'));
		$('#edit_form').submit();
	});";
	}

    /**
     * Create a jQuery event function
     * @param string $obj
     * @param string $jq_event
     * @param string $code
     */
	public function buildJQueryCode($obj, $jq_event, $code){
		$this->jq_output .= <<<EOT
$("{$obj}").{$jq_event}(function(e){
	{$code}
});

EOT;
	}

    /**
     * Create a jQuery Ajax function
     * @param string $obj
     * @param string $objtype
     * @param string $destobj
     * @param string $destlabel
     * @param string $table
     * @param string $idfld
     * @param string $datafld
     * @param string $crit
     * @param string $limit
     * @param string $order
     */
	public function buildJQueryAjaxCode($obj, $objtype, $destobj, $destlabel,
                                        $table, $idfld, $datafld,
                                        $crit = "", $limit = "", $order = ""){
		switch($objtype){
			case 'selectmenu':
				$jq_event = 'change';
				$jq_destprocess = "$('#{$destobj}').find('option').remove().end().append('<option value=\"\">-- Select {$destlabel} --</option>').val('');
					if(jsonResponse.success){
						var arry = eval(jsonResponse.rtndata);
						var nr = arry.length;
						for(var i=0; i<nr; i++){
							var arry_elem = arry[i];
							$('#{$destobj}').append('<option value=\"'+arry_elem['id']+'\">'+arry_elem['{$datafld}']+'</option>'+\"\\n\");
						}
						if(nr == 1) $('#{$destobj}').val(arry[0]['id']);
					}";
				break;
			case 'selectoption':
				$jq_event = 'change';
				$jq_destprocess = "
					if(jsonResponse.success){
						var arry = eval(jsonResponse.rtndata);
						if(arry.length > 0){
							var arry_elem = arry[0];
							$('#{$destobj}').val(arry[0]['id']);
						}
					}";
				break;
			case 'text':
				$js_event = 'blur';
				$jq_destprocess = "$('#{$destobj}').val(jsonResponse.rtndata);";
				break;
			default:
				die("Invalid datatype in jsblock->buildJQueryCode!");
				return false;
		}

        $web = WEB_URL;
		$this->jq_output .= <<<EOT
$("#{$obj}").{$jq_event}(function(e){
	e.preventDefault();
	var val = $(this).val();
	var crit = '{$crit}';
	if(val != ''){
		$.ajax({
			type: "POST",
			url: "{$web}admin/inc/_core/ajaxwrapper.php",
			data: {op:'{$objtype}', table:'{$table}', fld:'{$datafld}', crit:crit, limit:'{$limit}', order:'{$order}'},
			dataType: "json",
			cache: false,
			success: function(jsonResponse){
			{$jq_destprocess}
			}
		});
	}
});
EOT;
	}

	/**
	 * Add a validation test to check_form function
	 * @param string $object
	 * @param string $msg
	 */
	public function addCheckReqEntry($object, $msg){
		$item = "";
		if($object != '' && $msg != ''){
			$item .= "	if(!checkRequiredField('{$object}', '{$msg}')) return false;";
			$this->checkitems[] = $item;
		}else{
			Throw new exception("Object and message are required for ".__FUNCTION__);
		}
	}

	/**
	 * Add a CKEditor validation test to check_form function
	 * @param string $object
	 * @param string $msg
	 */
	public function addCheckReqCKEditorEntry($object, $msg){
		if($object != '' && $msg != ''){
			$item = "	$('#{$object}').val(CKEDITOR.instances.{$object}.getData());\n";
			$item.= "	if(!checkRequiredField('{$object}', '{$msg}')) return false;";
			$this->checkitems[] = $item;
		}else{
			Throw new exception("Object and message are required for ".__FUNCTION__);
		}
	}

	/**
	 * Add a number validation test to check_form function
	 * @param string $object
	 * @param float $min
	 * @param float $max
	 * @param string $msg
	 */
	public function addCheckReqNumEntry($object, $min, $max, $msg){
		if($object != '' && $msg != ''){
			$min = floatval($min);
			$max = floatval($max);
			$item.= "	if(!checkRequiredNumField('{$object}', {$min}, {$max}, '{$msg}')) return false;";
			$this->checkitems[] = $item;
		}else{
			Throw new exception("Object, min, max, and message are required for ".__FUNCTION__);
		}
	}

    /**
     * Create a minimum character validation function
     * @param string $object
     * @param integer $min
     * @param string $msg
     */
	public function addCheckReqMinCharEntry($object, $min, $msg){
		if($object != '' && $msg != ''){
			$min = floatval($min);
			$item.= "	if(!checkMinChars('{$object}', {$min}, '{$msg}')) return false;";
			$this->checkitems[] = $item;
		}else{
			Throw new exception("Object, min, and message are required for ".__FUNCTION__);
		}
	}

    /**
     * Create an email validation function
     * @param string $object
     */
	public function addCheckReqEmailEntry($object){
		if($object != ''){
			$item.= "	if(!validateEmail('{$object}')) return false;";
			$this->checkitems[] = $item;
		}else{
			Throw new exception("Object is required for ".__FUNCTION__);
		}
	}

    /**
     * Create a string comparison function
     * @param string $object
     * @param string $object2
     * @param string $msg
     */
	public function addCheckReqCompareStr($object, $object2, $msg){
		if($object != '' && $object2 != ''){
			$item.= "	if(!compareTheString('{$object}', '{$object2}', '{$msg}')) return false;";
			$this->checkitems[] = $item;
		}else{
			Throw new exception("Object and object2 are required for ".__FUNCTION__);
		}
	}

	/**
	 * Create a checkdate function
	 */
	public function buildCheckDateFunc(){
		$this->output .= "
function updateDate(obj){
	var m = $('#'+obj+'_m').val();
	var d = $('#'+obj+'_d').val();
	var y = $('#'+obj+'_y').val();
	$('#'+obj).val(y+'-'+m+'-'+d);
}
";
	}

	/**
	 * Create a copydata function
	 */
	public function buildCopyDataFunc(){
		$this->output .= "
function copydata(src, target){
	$('#'+target).val(removeHTMLTags(src, false));
}
";
	}

	/**
	 * Create a password icon indicator function
	 */
	public function buildPasswordIconFunc(){
		$this->output .= "

function showpswdicon(elem) {
	$('#passok').hide();
	$('#passbad').hide();
	$('#cpassok').hide();
	$('#cpassbad').hide();
	ok = false;
	if($('#password').val() == $('#cpassword').val()) {
		ok = true;
	}
	if (elem == 'password') {
		if (ok == true) {
			$('#passok').show();
			$('#cpassok').show();
		}else{
			$('#passbad').show();
		}
	}else{
		if (ok == true) {
			$('#passok').show();
			$('#cpassok').show();
		}else{
			$('#cpassbad').show();
		}
	}
}
";
	}

	/**
	 * Create a custom page redirction function
	 * @param string $fname
	 * @param string $page
	 */
	public function buildCustomPageFunc($fname, $page){
		$this->output .= "
function {$fname}(){
	window.location = '{$page}';
}
";
	}

    /**
     * Return Validator script
     */
	private function showValidatorCode(){
        global $incl;

        if(strpos($incl, "validator") === false) return null;

        preg_match("/validator\(([a-z0-9, \-_]*)\)/i", $incl, $formlist);
        if($formlist[1] != '' && isset($formlist[1])){
            $forms = preg_split("/[\s,]+/", $formlist[1]);
            foreach($forms as $key=>$value) $forms[$key] = "form#".$value;
            $form_id = join(",", $forms);
        }else{
            $form_id = "form";
        }
        return "\n\n	$(\"{$form_id}\").validate();";
 	}

	/**
	 * Output the finalized JS list block
	 */
	public function showJSListBlock(){
		$content = $this->output;
		$jq_out = $this->jq_output;

		print <<<EOT
<script type="text/javascript" language="javascript">
(function($){
	{$content}
	{$jq_out}
}) (jQuery);
</script>

EOT;
	}

	/**
	 * Output finalized JS edit block
	 */
	public function showJSEditBlock(){
		$content = $this->output;
		$jq_out = $this->jq_output.self::showValidatorCode();

		print <<<EOT
<script type="text/javascript" language="javascript">
(function($){
	{$content}
	{$jq_out}
}) (jQuery);
</script>

EOT;
	}
}

?>