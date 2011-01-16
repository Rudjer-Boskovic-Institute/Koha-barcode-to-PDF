<?php

require_once('inc/tcpdf/config/lang/hrv.php');
require_once('inc/tcpdf/tcpdf.php');
require_once('inc/config.inc.php');
require_once('inc/db/Database.class.php');

$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

$db->connect();

if (isset($_POST['label_template']) && $_POST['label_template']!='') { $label = filter_var($_POST['label_template'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW); } else { $inputerr[] = "label not set."; }
if (isset($_POST['filter']) && $_POST['filter']!='') { $filter = filter_var($_POST['filter'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW); } else { $inputerr[] = "filter not set."; }
if (isset($_POST['filter_value']) && $_POST['filter_value']!='') { $filter_value = filter_var($_POST['filter_value'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW); } else { $inputerr[] = "filter value not set."; }
if (isset($_POST['position']) && is_numeric($_POST['position']) && $_POST['position'] >= 1) { $labelnum = $_POST['position'] - 1; } else { $labelnum = 0; }
$status = $_POST['status'];

if ($filter =='barcode') {

	$barcode_num = '/^[0-9]{10}$/';
	$barcode_comma_array = '/,/';
	$barcode_array = '/^[0-9]{10}-[0-9]{10}$/';
	if (preg_match($barcode_num, $filter_value)) {
		$sql_barcode_filter = ' AND items.barcode="'.$db->escape($filter_value).'"';
	} elseif (preg_match($barcode_array, $filter_value)) {
		list ($bcode_min, $bcode_max) = split ('-',$filter_value);
		$sql_barcode_filter = ' AND items.barcode>="'.$bcode_min.'" AND items.barcode<="'.$bcode_max.'"';
	} elseif (preg_match($barcode_comma_array, $filter_value)) {
		$barcodes = explode (',',$filter_value);
		if (count($barcodes) > 0) {
			$barcode_list = '';
			foreach ($barcodes as $val) {
				$barcode_list .= trim($val) . ",";
			}
			$barcode_list = rtrim ($barcode_list, ",");
		}
		$sql_barcode_filter = " AND items.barcode IN ($barcode_list)";
		
	} else {
		$sql_barcode_filter = ' AND items.barcode="0"';
	}

	$sql = 'SELECT items.barcode as bcode, items.itemcallnumber as sig FROM items LEFT JOIN biblioitems on (items.biblioitemnumber=biblioitems.biblioitemnumber) LEFT JOIN biblio on (biblioitems.biblionumber=biblio.biblionumber)'
		. ' WHERE items.itype="BOOK"'
		. ' AND items.holdingbranch="IRB"'
		. $sql_barcode_filter;


} elseif ($filter == 'borrower' ) {
	$sql = 'SELECT issues, biblio.title, author, surname, firstname, items.itemcallnumber as sig, items.barcode as bcode, issues.issuedate, issues.lastreneweddate'
        	. ' FROM issues'
        	. ' LEFT JOIN borrowers ON borrowers.borrowernumber=issues.borrowernumber'
        	. ' LEFT JOIN items ON issues.itemnumber=items.itemnumber'
        	. ' LEFT JOIN biblio ON items.biblionumber=biblio.biblionumber'
        	. ' WHERE borrowers.cardnumber="'.$db->escape($filter_value).'"'
        	. ' ORDER BY biblio.title';
} elseif ($filter == 'callnumber') {
	if (strlen($filter_value) <4) { 
		$inputerr[] = "callnumber input must have at least 4 characters.";
	}
	$sql = 'SELECT items.barcode as bcode, items.itemcallnumber as sig FROM items LEFT JOIN biblioitems on (items.biblioitemnumber=biblioitems.biblioitemnumber) LEFT JOIN biblio on (biblioitems.biblionumber=biblio.biblionumber)'
		. ' WHERE items.itype="BOOK"'
		. ' AND items.holdingbranch="IRB"'
		. ' AND items.itemcallnumber like "'.$db->escape($filter_value).'%"';
}


// STATUS FILTER

	if ($status == 'onloan') {
		$sql .= ' AND onloan is NOT NULL';
	} 

	if ($status == 'onshelf' && $filter !='borrower') {
		$sql .= ' AND (onloan is NULL OR onloan = "0000-00-00")';
	} 


// feed it the sql directly. store all returned rows in an array
$rows = $db->fetch_all_array($sql);

$lines = explode (';', $data);
foreach ($lines as $line) {
	$record = explode (',', $line);
	$barcode_data[$record[0]] = $record[1];
}

// Labels per page
$l = $labels[$label]['NX'] * $labels[$label]['NY'];

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true); 

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('RBI Library');
$pdf->SetTitle('Barcode Labels');
$pdf->SetSubject('Barcode');
$pdf->SetKeywords('barcode, label, library');


// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(0, 0, 0);

//set auto page breaks
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetAutoPageBreak(TRUE, 0);

//set image scale factor
//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 

//set some language-dependent strings
$pdf->setLanguageArray($l); 

// ---------------------------------------------------------

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// add a page
$pdf->AddPage();

if (!isset($inputerr)) {

	$style = array(
    		'position' => 'C',
    		'border' => false,
    		'padding' => 0,
    		'fgcolor' => array(0,0,0),
    		'bgcolor' => false, //array(255,255,255),
    		'text' => true,
    		'font' => 'freesans',
    		'fontsize' => 9,
    		'stretchtext' => 0
	);

	$position = $labelnum - ($l * floor($labelnum/$l));

	foreach($rows as $record) {

		$pdf_x_pos = $labels[$label]['marginLeft'] + (($labels[$label]['width'] + $labels[$label]['SpaceX']) * ($position % $labels[$label]['NX']));
		$pdf_y_pos = $labels[$label]['marginTop'] + (($labels[$label]['height'] + $labels[$label]['SpaceY']) * floor($position / $labels[$label]['NX']));

		$pdf->SetXY($pdf_x_pos, $pdf_y_pos);

		$pdf->SetFont('freesans', '', 8);
		$pdf->Cell($labels[$label]['width'], 0, 'Knjižnica Instituta "Ruđer Bošković"', 0, 2, 'C', 0, '', 0, true);
		$pdf->SetFont('freesansb', '', 14);
		$pdf->Cell($labels[$label]['width'], 0, $record[sig], 0, 2, 'C', 0, '', 0, true);
		$pdf->write1DBarcode($record[bcode], 'I25', '', '', $labels[$label]['width'], 12, 0.4, $style, 'N');

		$labelnum ++;
		$position ++;

		if ($labelnum % $l == 0) {
			$position = 0; 
			$pdf->AddPage();
		}
	}
} else {

	foreach ($inputerr as $i) {
			$pdf->Cell(0, 0, $i, 0, 2, 'C', 0, '', 0, true);
                }

}

			$pdf->Cell(0, 0, "", 0, 2, 'C', 0, '', 0, true);

$db->close();

//Close and output PDF document
$pdf->Output('barcode.pdf', 'I');


?>
