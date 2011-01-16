<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<title>ZebraKiller</title>
<meta name="AUTHOR" content="alen@irb.hr" />
<meta name="GENERATOR" content="vim" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
BODY {
	font-family: verdana, tahoma, arial; 
	font-size: 1em;
	background: #ffffff;
}

#container {
	position: relative; 
	top: 10px; 
	width: 600px; 
	margin: 0 auto;
} 

#header {
	height: 30px; 
	float: left;	
	font-size: 1.5em;
}

#sep {
	border-bottom: 1px #cccccc solid;
	clear:both;	
}

#close {
	height: 30px; 
	float: right;	
	font-size: 0.8em;
	color: #cccccc;
	margin-top: 10px;
	cursor: pointer;
}

#main {
	width: 100%;
}

#footer {
	width: 100%;
	border-top: 1px #cccccc solid;
	text-align: center;
	font-size: 0.8em;
	color: #cccccc;
}

.red {
	color: #B32D00;
}

.gray {
	color: #cccccc;
}

input {
	font-size: large;
}

</style>

<script type="text/javascript"> 
 function changeClass( idref, classname ) {   
  var el = document.getElementById(idref); 
  var attributeNode = el.getAttributeNode("class"); 
  if( attributeNode ) { attributeNode.value = classname; } 
  else { el.setAttribute("class", classname); } 
 }  
 window.onload = function(){ 
  var serviceTab = document.getElementById('close'); 
  serviceTab.onmouseover = function(){changeClass('x','red');}; 
  serviceTab.onmouseout = function(){changeClass('x','gray');}; 
 } 
 </script> 

</head>
<body>
<div id="container">
	<div id="header">

		ZebraKiller // barcode printer for KOHA
	</div>
	<div id="close" onclick="window.location='index.php'">
		<span id="x" class="gray">x</span> Reset 
	</div>
	<div id="sep"></div>
	<div id="main">
	
	
	<form name="label" method="post" action="tc_label.php">
	 <h3>Page and label layout (currently predefined):</h3>

	 <p>Choose template:<br />
		<select name="label_template">
			<option value="Avery_L4737">Avery L4737</option>
			<option value="Avery_L4737_test">Avery L4737 test</option>
		</select>
	
	 <p>Choose filter:<br />
		<select name="filter">
			<option value="borrower">Patron username</option>
			<option value="barcode">Barcode number (single or array)</option>
			<option value="callnumber">Callnumber starts with (4 chars minimum)</option>
		</select>
	
	<p>Filter value:	<br /> 
		<input type="text" name="filter_value" size="50">
	</p>

	 <p>Status:<br />
		<select name="status">
			<option value="all">On loan and on shelves</option>
			<option value="onloan">On loan only</option>
			<option value="onshelf">On shelf only</option>
		</select>
	
	<p>Label number start position:	<br /> 
		<input type="text" name="position" value="1" size="1">
	</p>

	 <p><input type="submit" value="Generate PDF" name="generate">
	</form>


	</div>
	<div id="footer">
	&copy; 2010. Ruđer Bošković Institue Library
	</div>
	
</div>
</body>
</html>
