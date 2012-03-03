<?php
   /*
    * DYNAMIC SEARCH
    * Author: Chris Donalds (chrisd@navigatormm.com)
    * Date: Feb 12, 2010
    * ----------------------------------------------
    * 
    * Input: 
    * 		$searchtext (string):
    * 				the entered search value
    * 		$fieldarray (array):
    * 				"text" => all text-based search fields
    * 				"date" => all date-based search fields
    * 				"time" => all time-based search fields
    * 				"number" => all number-based search fields
    * 				"binary" => all binary-based search fields
    * 				"dollar" => all monetary-based fields
    * 
    * Output:
    * 		on success: sql WHERE value
    * 		on failure: false
    *
    * Code Examples:
    *
		if($search_text != "" && strtolower($search_text) != "search ..."){
    		// call dosearch to create SQL WHERE string
			$search_clause = dosearch($search_text,
									  array("text" => array("d.text_field1"{, "d.text_field2"}...),
											"number" => array("a.number_field1"{, "a.number_field2"}...),
											"date" => array("d.date1"{, "d.date2"}...),
											"time" => array("d.time1"{, "d.time2"}...),
											"dollar" => array("d.dollar_field1"{, "dollar_field2"}...)
										   )
									 );
    		// append returned string to current WHERE clause
			if($search_clause !== false)
				$where_clause .= " AND ".$search_clause;
		}

    	// prepare queries (number of rows and records)
		// getRecArrayJoin(array(tables,
								 fields,
								 joincriteria,
								 jointype,
								 $where_clause,
								 sortby,
								 limits,
								 groupby/having);

		$num  = getRecArrayJoinNumRows(array("table1 as d"{, "table2 as c"}...),
							  array("d.fields1"{, "c.fields2"}...),
							  array(""{, "d.field_id1 = c.field_id2"}...),
							  array(""{, "LEFT JOIN|RIGHT JOIN|INNER JOIN|JOIN"}...),
							  $where_clause);
		$recs = getRecArrayJoin(array("table1 as d"{, "table2 as c"}...),
							  array("d.fields1"{, "c.fields2"}...),
							  array(""{, "d.field_id1 = c.field_id2"}...),
							  array(""{, "LEFT JOIN|RIGHT JOIN|INNER JOIN|JOIN"}...),
							  $where_clause, $sort_by." ".$sort_dir, "$offset, $limit", "");

    */
	
function dosearch($searchtext, $fieldarray){
	if ($searchtext == "") return null;
	if (!is_array($fieldarray)) die("Search: Missing fieldarray!");
	
	// first break searchtext into words/phrases
	$searchtext = strtolower(stripslashes($searchtext));
	$searchparts = preg_split("/[\s,]*[(\"|\')]([^(\"|\')]+)[(\"|\')][\s,]*|[\s,]+/", $searchtext, 0, PREG_SPLIT_DELIM_CAPTURE);
	
	// then loop through words
	// if word is 'and', or 'or' change concat to this value
	// loop though fields appending search string with field and words
	if(count($searchparts) > 0){
		$searchclause = "";
		$concat = "";
		$treat_as_neg = false;
		foreach($searchparts as $word){
			$word = trim($word);
			if($word != '' && $word != null){
				$word = str_replace("'", "&#39;", $word);
				if($word == 'and'){ 
					$concat = " AND ";
				}elseif($word == 'or'){
					$concat = " OR ";
				}elseif($word == 'not'){
					$treat_as_neg = true;
				}elseif($word != "%'" && $word !="''=''" && $word != "'"){
					$searchclause .= $concat."(";
					$searchchunk = "";
					foreach($fieldarray as $type => $fieldset){
						// the fieldset contains the type and fields (array)
						$innerconcat = (($treat_as_neg) ? " AND " : " OR ");
						switch($type){
							case "text":
								foreach($fieldset as $field) {
									if($searchchunk != "") $searchchunk .= $innerconcat;
									$searchchunk .= "LOWER(".$field.") ".(($treat_as_neg) ? "NOT " : "")."LIKE '%".addslashes($word)."%'";
								}
								break;
							case "number":
								$number = floatval($word);
								if($number != 0){
									foreach($fieldset as $field) {
										if($searchchunk != "") $searchchunk .= $innerconcat;
										$searchchunk .= $field." ".(($treat_as_neg) ? "!" : "")."= ".$number;
									}
								}
								break;
							case "date":
								$sdate = strtotime($word);
								if($sdate !== false){
									$date = date("Y-m-d", $sdate);
									if($date != "1969-12-31"){
										foreach($fieldset as $field) {
											if($searchchunk != "") $searchchunk .= $innerconcat;
											$searchchunk .= $field." ".(($treat_as_neg) ? "NOT " : "")."LIKE '%".$date."%'";
										}
									}
								}
								break;
							case "time":
								$stime = strtotime($word);
								if($stime !== false){
									$time = date("H:i", $stime);
									if($time != "0000-00-00"){
										foreach($fieldset as $field) {
											if($searchchunk != "") $searchchunk .= $innerconcat;
											$searchchunk .= $field." ".(($treat_as_neg) ? "NOT " : "")."LIKE '%".$time."%'";
										}
									}
								}
								break;
							case "dollar":
								if(substr($word, 0, 1) == "$"){
									$money = floatval(substr($word, 1));
									foreach($fieldset as $field) {
										if($searchchunk != "") $searchchunk .= $innerconcat;
										$searchchunk .= $field." ".(($treat_as_neg) ? "!" : "")."= ".$money;
									}
								}
								break;
							case "binary":
								$bin2dec = bindec($word);
								$dec2bin = decbin($bin2dec);
								if($dec2bin == $word){
									foreach($fieldset as $field) {
										if($searchchunk != "") $searchchunk .= $innerconcat;
										$searchchunk .= $field." ".(($treat_as_neg) ? "NOT " : "")."LIKE '%".$word."%'";
									}
								}
								break;
						}
					}
					$searchclause .= $searchchunk.")";
					$concat = " AND ";
					$treat_as_neg = false;
				}
			}
		}
		return "(".$searchclause.") ";
	}else{
		return false;
	}
}
?>