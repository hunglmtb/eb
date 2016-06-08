<?php
if($argv){
	require_once('../../taskman/includes/gfconfig.php');
	require_once('../../taskman/includes/gfinnit.php');
	require_once('../../taskman/libs/cls.mysql.php');
	require_once('../../taskman/function/workflow_process.php');
	if(count($argv)>=6){
		// $taskid $report_id $facility_id $type $from_date $to_date $email
		$task_id=$argv[1];
		$report_id=$argv[2];
		$facility_id=$argv[3];
		$date_type=$argv[4];
		$from_date=$argv[5];
		$to_date=$argv[6];
		$email=$argv[7];
		$exportType ="PDF";
		if($date_type=="day"){
			$date = date('Y-m-d');
			$from_date = date('Y/m/d', strtotime($date .' -1 day'))."";
			$to_date = $from_date;
		}
		$fs=explode("-",$from_date);
		if(count($fs)>=3)
			$from_date="$fs[2]/$fs[0]/$fs[1]";
		$fs=explode("-",$to_date);
		if(count($fs)>=3)
			$to_date="$fs[2]/$fs[0]/$fs[1]";
	}
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		if($report_id==1){
			$report_file="NewMain.jrxml";
			$url="http://central-energybuilder.com/eb/report/export.php?export=PDF&date_from=$from_date&date_to=$to_date&facility_id=$facility_id";
			file_get_contents($url);
		}
		else if($report_id==2){
			$report_file="Well_summary.jrxml";
			$url="http://central-energybuilder.com/eb/report/summaryVolumeReport.php?export=PDF&startDate=$from_date&endDate=$to_date&facility_id=$facility_id";
			file_get_contents($url);
		}
		else if($report_id==3){
			$report_file="Well_test_summary.jrxml";
			$url="http://central-energybuilder.com/eb/report/WelltestSummaryReport.php?export=PDF&startDate=$from_date&endDate=$to_date&facility_id=$facility_id";
			file_get_contents($url);
		}
		else if($report_id==4){
			$report_file="Morning_report.jrxml";
			$url="http://central-energybuilder.com/eb/report/MorningReport_report.php?export=PDF&date=$from_date&facility_id=$facility_id";
			file_get_contents($url);
		}
		
		require_once '../../lib/phpmailer/class.phpmailer.php';
		require_once '../../lib/sendemail.php';
		$subject = "Report sent from Workflow";
		$filename="../../report/output.pdf";
		$content="Please see attached file.<br>You also can view live report by click this link: $url";//"Please see attached file";
		$ret=sendEmail($email,$subject,$content,$filename);
		if($ret===1){
			echo "Email sent successfully";
		}
		else
			echo $ret;
	}
	if($task_id>0){
		finalizeTask($task_id,1,null,null);
	}
	exit();
}
else{
	$facility_id=$_REQUEST["facility_id"];
	$from_date=$_REQUEST["date_from"];
	$to_date=$_REQUEST["date_to"];
	$exportType = $_REQUEST["export"];
}
ini_set("allow_url_include", true);
if(isset($exportType)){
	$varurl='http://localhost:8080/JavaBridge/java/Java.inc';
	require_once $varurl;
	$System = java("java.lang.System");
	//echo $System->getProperties();
	try{
		java("java.lang.Class")->forName("com.mysql.jdbc.Driver");
		$connection = java("java.sql.DriverManager")->getConnection("jdbc:mysql://localhost/eb", "root", "");
		$root = realpath(".");
		$in = $root."/NewMain.jrxml";
		$report = java("net.sf.jasperreports.engine.JasperCompileManager")->compileReport($in);
		
		$dateFormat = new java("java.text.SimpleDateFormat", "yy/MM/dd");
		$d = $dateFormat->parse($from_date);
		$d2=$dateFormat->parse($to_date);
		$params = new java("java.util.HashMap");
		
		$params->put("facility_id", intval($facility_id)); // dây là 1 param

		$params->put("begin_date",new java("java.sql.Date", $d->getTime()));
		$params->put("end_date",new java("java.sql.Date",$d2->getTime()));
		$params->put("SUBREPORT_DIR",$root."/sub/");
		$print = java("net.sf.jasperreports.engine.JasperFillManager")->fillReport($report, $params, $connection);

		$contentType="text/Html";
		$out = 'out.html';
		if($exportType == "PDF")
		{
			java_set_file_encoding("ISO-8859-1");
			$contentType="application/pdf";
			// export Pdf
			$out = $root."/output.pdf";
			java("net.sf.jasperreports.engine.JasperExportManager")->exportReportToPdfFile($print, $out);
		}
		elseif($exportType == "HTML")
		{	
			// export Pdf
			$out = $root."/output.Html";
			$contentType="text/Html";
			java("net.sf.jasperreports.engine.JasperExportManager")->exportReportToHtmlFile($print, $out);
		}
		elseif($exportType == "Excel")
		{
			$out = $root."/output.xls";
			$contentType="application/vnd.ms-excel";
			$xlsExporter = new java("net.sf.jasperreports.engine.export.JRXlsExporter");
			$JRXlsExporterParameter = java("net.sf.jasperreports.engine.export.JRXlsExporterParameter");
			$xlsExporter->setParameter($JRXlsExporterParameter->JASPER_PRINT, $print);
			$xlsExporter->setParameter($JRXlsExporterParameter->OUTPUT_FILE, new java("java.io.File", $out));
			//$xlsExporter->setParameter($JRXlsExporterParameter->IS_WHITE_PAGE_BACKGROUND, true);
			$xlsExporter->exportReport();
		}
		header("Content-type: ".$contentType);
		readfile($out);
		//unlink($out);
		
	} catch(Exception $ex){
		echo "<b>Error...:</b>".$ex->getCause();
	}
	echo "done";
}
?>