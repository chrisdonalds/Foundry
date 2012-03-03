<?php

//  ------------------------------------------------------------------------------------
//  EDIT
//  ------------------------------------------------------------------------------------

$incl = "validator imgedit fileuploader filehandler";
include ("../header.php");
$db->table = DB_TABLE_PREFIX."products";

startContentArea();

// process POST DATA

if(formDataIsReadyForSaving()){
	$cat_id = intval(getRequestVar('cat_id'));
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
	if($image != "" && $image_mod != ""){
		list($saveimg, $savethm) = FU_MoveTempFile($image_mod, $image, IMG_UPLOAD_FOLDER.$db->table, THM_UPLOAD_FOLDER.$db->table, true, false);
	}elseif($image_mod != 'deleted'){
		$saveimg = getRequestVar('lastimg');
		$savethm = getRequestVar('lastthm');
	}

	if (!errorMsgExists()) {
		// SQL fields to be updated.
		// end list with ', '
		$sqlbase = "code = '$code', cat_id = $cat_id, product_id = '$product_id', itemtitle = '$itemtitle', shortdescr = '$shortdescr', description = '$description', type_id = $type_id, size = '$size', color = '$color', min_order = $min_order, measure = '$measure', price = $price, sale_price = $sale_price, case_size = $case_size, upc = '$upc', is_feature = $is_feature, image = '$saveimg', thumb = '$savethm', ";
    	switch($_page->savebuttonpressed) {
    		case "save":
                if(updateRec($db->table, $sqlbase."date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    		case "savedraft":
                if(updateRec($db->table, $sqlbase."draft = 1, date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    		case "saveact":
                if(updateRec($db->table, $sqlbase."draft = 0, activated = 1, date_published = NOW(), date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    		case "savearc":
                if(updateRec($db->table, $sqlbase."archived = 1, date_updated = NOW()", "id = '{$_page->row_id}'")){
                	addErrorStatMsg(SUCCESS_EDIT);
                }else{
                	addErrorStatMsg(FAILURE_EDIT);
                }
    			break;
    	}
	}
}

// build query

$recset = getRec($db->table, "*", "id = '{$_page->row_id}'", "", "");
if(count($recset) == 1) {
    extractVariables($recset[0]);
}else{
    gotoPage("list-products_cat.php");
	exit;
}

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
//$js->buildJQueryAjaxCode('pcat_id', 'selectmenu', 'cat_id', 'a Category', 'data_products_cat', 'cat_id', 'name');
$js->buildJQueryCode('#price, #case_size', 'change', 'var price = $("#price").val(); var case_size = $("#case_size").val(); if(!isNaN(price) && !isNaN(case_size)) { var cprice = parseFloat(price) * parseFloat(case_size); $("#case_price").val(cprice.toFixed(2)); }');
$js->showJSEditBlock();

startButtonBlock();
showPrevPageButtons(array("Products List" => "products/list-products.php?cat_id={$cat_id}"));
showEditorButtons(DEF_EDITBUT_UPDATE+DEF_EDITBUT_STATS);
echo "</div>\n";

startEditorBlock("edit_content");
showPageTitle("Edit Product");
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
showStats();
showImgEditBox("Image", "image");
showFileUploaderScript(IMGEDITOR_TEMPFOLDER, 0, 100);
attachFileUploader(array('image'), array($image), array(IMAGE_TYPES));
showFooter();
?>
