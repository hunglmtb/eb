<?php
$file = $_REQUEST ["file"];
$exportType = $_REQUEST ["export"];
error_reporting ( E_ALL & ~ E_NOTICE );
ini_set ( "allow_url_include", true );
if (isset ( $exportType )) {
	$varurl = 'http://localhost:8080/JavaBridge/java/Java.inc';
	require_once $varurl;
	$System = java ( "java.lang.System" );
	try {
		java ( "java.lang.Class" )->forName ( "com.mysql.jdbc.Driver" );
		$connection = java ( "java.sql.DriverManager" )->getConnection ( "jdbc:mysql://localhost/energy_builder", "eb", "4v@Zy#m." );
		$root = realpath ( "." );
		$file = str_replace(".jrxml","", $file);
		$in = $root . "\\{$file}.jrxml";

		$report = java ( "net.sf.jasperreports.engine.JasperCompileManager" )->compileReport ( $in );
		
		$dateFormat = new java ( "java.text.SimpleDateFormat", "yy-MM-dd" );
		$params = new java ( "java.util.HashMap" );
		
		foreach($_REQUEST as $arg => $value){
			$param = substr($arg,0,strpos($arg, '__T_'));
			//echo "$param = $value<br>";
			if (strpos($arg, '__T_1') !== false) {
				$params->put ($param, intval($value));
			}
			else if (strpos($arg, '__T_2') !== false) {
				$params->put ($param, $value);
			}
			else if (strpos($arg, '__T_3') !== false && $value) {
				$datevalue = $dateFormat->parse ( $value );
				$params->put ($param, new java("java.sql.Date", $datevalue->getTime()));
			}
		}
		$params->put ( "ROOT_DIR", $root );
		
		$print = java ( "net.sf.jasperreports.engine.JasperFillManager" )->fillReport ( $report, $params, $connection );
		$print->setProperty("net.sf.jasperreports.export.xls.ignore.graphics", "true");
		
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
			
			// $xlsExporter->setParameter($JRXlsExporterParameter->IS_WHITE_PAGE_BACKGROUND, true);
			$xlsExporter->exportReport ();
		}
		header ( "Content-type: " . $contentType );
		readfile ( $out );
		unlink($out);
	} catch ( Exception $ex ) {
		echo "Can not generate report. Please contact technical support.";
		//echo "<b>Error...:</b>" . $ex->getCause ();
	}
}
?>

