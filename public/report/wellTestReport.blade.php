<?php
$facility_id = $_REQUEST ["facility_id"];
$from_date = $_REQUEST ["startDate"];
$exportType = $_REQUEST ["export"];

error_reporting ( E_ALL & ~ E_NOTICE );
ini_set ( "allow_url_include", true );

if (isset ( $exportType )) {
	$varurl = 'http://localhost:8080/JavaBridge/java/Java.inc';
	require_once $varurl;
	//$System = java ( "java.lang.System" );
	// echo $System->getProperties();
	try {
		java ( "java.lang.Class" )->forName ( "com.mysql.jdbc.Driver" );
		$connection = java ( "java.sql.DriverManager" )->getConnection ( "jdbc:mysql://localhost/eb", "root", "" );
		$root = realpath ( "." );
		$in = $root . "\well_test" . ($exportType == "Excel" ? "_excel" : "") . ".jrxml";
		$report = java ( "net.sf.jasperreports.engine.JasperCompileManager" )->compileReport ( $in );
		
		$dateFormat = new java ( "java.text.SimpleDateFormat", "yy/MM/dd" );
		$d = $dateFormat->parse ( $from_date );
		$params = new java ( "java.util.HashMap" );
		
		$params->put ( "facility_id", intval ( $facility_id ) ); // dây là 1 param
		
		$params->put ( "begin_date", new java ( "java.sql.Date", $d->getTime () ) );
		$params->put ( "report_time", $_REQUEST ["report_time"] );
		$params->put ( "ROOT_DIR", $root );
		$print = java ( "net.sf.jasperreports.engine.JasperFillManager" )->fillReport ( $report, $params, $connection );
		
		$contentType = "text/Html";
		$out = 'out.html';
		if ($exportType == "PDF") {
			java_set_file_encoding ( "ISO-8859-1" );
			$contentType = "application/pdf";
			// export Pdf
			$out = $root . "/output.pdf";
			java ( "net.sf.jasperreports.engine.JasperExportManager" )->exportReportToPdfFile ( $print, $out );
		} elseif ($exportType == "HTML") {
			// export Pdf
			$out = $root . "/output.Html";
			$contentType = "text/Html";
			java ( "net.sf.jasperreports.engine.JasperExportManager" )->exportReportToHtmlFile ( $print, $out );
		} elseif ($exportType == "Excel") {
			$out = $root . "/output.xls";
			$contentType = "application/vnd.ms-excel";
			$xlsExporter = new java ( "net.sf.jasperreports.engine.export.JRXlsExporter" );
			$JRXlsExporterParameter = java ( "net.sf.jasperreports.engine.export.JRXlsExporterParameter" );
			$xlsExporter->setParameter ( $JRXlsExporterParameter->JASPER_PRINT, $print );
			$xlsExporter->setParameter ( $JRXlsExporterParameter->OUTPUT_FILE, new java ( "java.io.File", $out ) );
			$xlsExporter->setParameter ( $JRXlsExporterParameter->IS_DETECT_CELL_TYPE, true );
			$xlsExporter->setParameter ( $JRXlsExporterParameter->IS_IGNORE_GRAPHICS, true );
			
			// $xlsExporter->setParameter($JRXlsExporterParameter->IS_WHITE_PAGE_BACKGROUND, true);
			$xlsExporter->exportReport ();
		}
		header ( "Content-type: " . $contentType );
		readfile ( $out );
		// unlink($out);
	} catch ( Exception $ex ) {
		echo "<b>Error...:</b>" . $ex->getCause ();
	}
	echo "done";
}
?>

