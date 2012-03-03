<?php

//  ------------------------------------------------------------------------------------
//  CREATE NEW PAGE
//  ------------------------------------------------------------------------------------

$incl = "validator imgedit fileuploader filehandler";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."products";
$cat_id = intval(getRequestVar('cat_id'));

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$itemtitle = clean_text(getRequestVar('itemtitle'));
	$code = codify($itemtitle);
	$description = str_replace("&#34;", "\"", getRequestVar('description'));
	$shortdescr = clean_text(((getRequestVar('shortdescr') != '') ? getRequestVar('shortdescr') : $description));
	$product_id = getRequestVar('product_id');
	$type_id = intval(getRequestVar('type_id'));
	$size = clean_text(getRequestVar('size'));
	$color = clean_text(getRequestVar('color'));
	$min_order = floatval(getRequestVar('min_order'));
	$measure = clean_text(getRequestVar('measure'));
	$price = floatval(getRequestVar('price'));
	$sale_price = floatval(getRequestVar('sale_price'));
	if($sale_price > $price) list($price, $sale_price) = swap($price, $sale_price);
	$case_size = intval(getRequestVar('case_size'));
	if($case_size < 1) $case_size = 1;
	$upc = clean_text(getRequestVar('upc'));
	$is_feature = ((getRequestVar('is_feature') != '') ? 1 : 0);
	$image = getRequestVar('image_fld');
	$image_mod = getRequestVar('image_mod');
	$lastID = getLastID($db->table, "id");

	list($saveimg, $savethm) = FU_MoveTempFile($image_mod, $image, IMG_UPLOAD_FOLDER.$db->table, THM_UPLOAD_FOLDER.$db->table, true, false);

	if (!errorMsgExists()) {
		// sql fields and data lists. end both with ', '
		$sqlfields = "code, cat_id, product_id, itemtitle, shortdescr, description, type_id, size, color, min_order, measure, price, sale_price, case_size, upc, is_feature, image, thumb";
		$sqldata   = "'$code', $cat_id, '$product_id', '$itemtitle', '$shortdescr', '$description', $type_id, '$size', '$color', $min_order, '$measure', $price, $sale_price, $case_size, '$upc', $is_feature, '$saveimg', '$savethm'";
    	switch($_page->savebuttonpressed) {
    		case "save":
    			$_page->row_id = insertRec($db->table, $sqlfields, $sqldata);
                if($_page->row_id > 0){
    				if(updateRec($db->table, "rank = ".(getLastID($db->table, "rank", "") + 1), "id = '{$_page->row_id}'")){
                		addErrorStatMsg(SUCCESS_CREATE);
    				}else{
    					addErrorStatMsg(FAILURE_CREATE);
    				}
                }else{
                	addErrorStatMsg(FAILURE_CREATE);
                }
    			break;
    		case "savedraft":
    			$_page->row_id = insertRec($db->table, $sqlfields, $sqldata);
                if($_page->row_id > 0){
    				if(updateRec($db->table, "rank = ".(getLastID($db->table, "rank", "") + 1), "id = '{$_page->row_id}'")){
                		addErrorStatMsg(SUCCESS_CREATE);
    				}else{
    					addErrorStatMsg(FAILURE_CREATE);
    				}
                }else{
                	addErrorStatMsg(FAILURE_CREATE);
                }
    			break;
    		case "saveact":
    			$_page->row_id = insertRec($db->table, $sqlfields.", activated, date_published", $sqldata.", 1, NOW()");
                if($_page->row_id > 0){
    				if(updateRec($db->table, "rank = ".(getLastID($db->table, "rank", "") + 1), "id = '{$_page->row_id}'")){
                		addErrorStatMsg(SUCCESS_CREATE);
    				}else{
    					addErrorStatMsg(FAILURE_CREATE);
    				}
                }else{
                	addErrorStatMsg(FAILURE_CREATE);
                }
    			break;
    	}
	}

	// Twitter post
	if (getErrorStatMsg(SUCCESS_CREATE) && strpos($incl, "twitter") !== false){
		createTwitterSession("status", "update", $shortdescr, WEB_URL, "News Article", WEB_URL.ADMIN_FOLDER."products/list-products.php?cat_id=$cat_id");
	}

	if (getErrorStatMsg(SUCCESS_CREATE)) gotoEditPage();
}

if($cat_id > 0) $pcat_id = intval(getRecItem($db->table."_cat", "cat_id", "id=$cat_id"));
$itemtitle = getRequestVar('itemtitle');
$product_id = getRequestVar('product_id');
$shortdescr = getRequestVar('shortdescr');
$description = getRequestVar('description');
$type_id = intval(getRequestVar('type_id'));
$size = getRequestVar('size');
$color = getRequestVar('color');
$min_order = floatval(getRequestVar('min_order'));
$measure = getRequestVar('measure');
$price = floatval(getRequestVar('price'));
$sale_price = floatval(getRequestVar('sale_price'));
$case_size = intval(getRequestVar('case_size'));
$is_feature = intval(getRequestVar('is_feature'));
$image = getRequestVar('image');
$thumb = getRequestVar('thumb');

// build arrays
$subcatlist = flattenDBArray(getRecJoin(DB_TABLE_PREFIX."products_cat c", DB_TABLE_PREFIX."products_cat c2", "c2.id, concat(c.name, ' - ', c2.name) as namepair", "c2.cat_id = c.id", "INNER JOIN", "", "namepair", ""), "id", "namepair");
$subcatlist = array("0"=>"-- Select a Category --") + $subcatlist;
$typelist = flattenDBArray(getRec(DB_TABLE_PREFIX."products_types", "*", "", "name", ""), "id", "name");
$typelist = array("0"=>"-- Select a Product Type --") + $typelist;

// build javascript block
$js = new JSBlock();
$js->subject = "product";
$js->section = "products";
$js->addCheckReqEntry('itemtitle', 'Please enter the product name.');
$js->addCheckReqEntry('cat_id', 'Please select a category.');
$js->addCheckReqEntry('product_id', 'Please enter the product ID');
$js->addCheckReqCKEditorEntry('description', 'Please enter a description.');
$js->addCheckReqNumEntry('price', 0.01, 9999999.99, 'Please enter the price.');
$js->buildCheckFormFunc();
$js->buildJQueryCode('#price, #case_size', 'change', 'var price = $("#price").val(); var case_size = $("#case_size").val(); if(!isNaN(price) && !isNaN(case_size)) { var cprice = parseFloat(price) * parseFloat(case_size); $("#case_price").val(cprice.toFixed(2)); }');
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Products List" => "products/list-products.php?cat_id={$cat_id}"));
showEditorButtons(DEF_EDITBUT_ACT);
echo "</div>\n";

startEditorBlock("add_content");
showPageTitle("Create a New Product");
showReqdText();

startPageForm("edit_form", "", "POST", true);
showTextField("Product Name*", "itemtitle", $itemtitle);
showTextField("Product ID*", "product_id", $product_id);
showMenu("Category*", "cat_id", $subcatlist, $cat_id, 1, false, "", "", "<br/><a href=\"".WEB_URL.ADMIN_FOLDER."products/list-products_cat.php\" target=\"_blank\">Add/Edit Categories and Sub-Categories</a>");
showTextareaField("Short Description<br/>(Seen on list page. Limited to 200 characters. Description will be used if Short Description is not provided.)", "shortdescr", $shortdescr, 75, 5, 200);
showHTMLEditorField("Description", "description", $description);
showMenu("Product Type*", "type_id", $typelist, $type_id, 1, false, "", "", "<br/><a href=\"".WEB_URL.ADMIN_FOLDER."products/list-products_types.php\" target=\"_blank\">Add/Edit Types</a>");
showTextField("Size", "size", $size, 20);
showTextField("Color", "color", $color);
showTextField("Minimum Order*", "min_order", $min_order, 20);
showTextField("Measure", "measure", $measure);
showTextField("Case Size", "case_size", $case_size, 10, 0, " [defaults to 1]");
showTextField("Reg. Price*", "price", $price, 20, 0, " [only numbers and period]");
showTextField("Sale Price", "sale_price", $sale_price, 20, 0, " [only numbers and period]");
showTextField("Case Price", "case_price", $case_price, 20, 0, " [only numbers and period]");
showImageField("Image", array("image", "lastimg", "lastthm", "delimg"), array($image, $thumb), array(false, false), $db->table);
showCheckbox("Is Product Featured?", "is_feature", $is_feature, "1", "Yes, product is featured");
endPageForm();
showImgEditBox("Image", "image");
showFileUploaderScript(IMGEDITOR_TEMPFOLDER, 0, 100);
attachFileUploader(array('image'), array($image), array(IMAGE_TYPES));
showFooter();
?>
