<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;


class export extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    
    protected $param;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle($param)
    {
    	if(count($param) >= 6){
	    	$task_id = $this->param['task_id'];
	    	$report_id = $this->param['report_id'];
	    	$facility_id = $this->param['facility_id'];
	    	$date_type = $this->param['date_type'];
	    	$from_date = $this->param['from_date'];
	    	$to_date = $this->param['to_date'];
	    	$email = $this->param['email'];
			$exportType ="PDF";
			
			if($date_type=="day"){
				$date = date('Y-m-d');
				$from_date = date('Y/m/d', strtotime($date .' -1 day'))."";
				$to_date = $from_date;
			}
			
			$fs=explode("-",$from_date);
			if(count($fs)>=3){
				$from_date="$fs[2]/$fs[0]/$fs[1]";
			}
			
			$fs=explode("-",$to_date);
			if(count($fs)>=3){
				$to_date="$fs[2]/$fs[0]/$fs[1]";
			}
			
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$host = "http://".ENV('DB_HOST');
				
				switch ($report_id){
					case 1:
						$url = $host."/report/export.php?export=PDF&date_from=$from_date&date_to=$to_date&facility_id=$facility_id";
						break;
					case 2:
						$url = $host."/report/summaryVolumeReport.blade.php?export=PDF&startDate=$from_date&endDate=$to_date&facility_id=$facility_id";
						break;
					case 3:
						$url = $host."/report/WelltestSummaryReport.blade.php?export=PDF&startDate=$from_date&endDate=$to_date&facility_id=$facility_id";
						break;
					case 4:
						$url = $host."/report/MorningReport_report.blade.php?export=PDF&date=$from_date&facility_id=$facility_id";
						break;
				}
				file_get_contents($url);
				
				try
				{
					$mailFrom = env('MAIL_USERNAME');
					$content="Please see attached file.<br>You also can view live report by click this link: $url";//"Please see attached file";
					$data = ['content' => $content];
					$filename = $host."/report/output.pdf";
					$subjectName = "Report sent from Workflow";
					$ret = Mail::send('front.sendmail',$data, function ($message) use ($email, $subjectName, $mailFrom, $filename) {
						$message->from($mailFrom, 'Your Application');
						$message->to($email)->subject($subjectName);
						$message->attach($filename);
					});
					
					if($ret===1){
						\Log::info("Email sent successfully");
					}
					else
						\Log::info($ret);
				}catch (Swift_RfcComplianceException $e)
				{
					\Log::info($e->getMessage());
				}
			}
			if($task_id>0){
				$objRun = new run(null);
				$objRun->finalizeTask($task_id,1,null,null);
			}
			
			exit();
    	}else{
    		$facility_id=$_REQUEST["facility_id"];
    		$from_date=$_REQUEST["date_from"];
    		$to_date=$_REQUEST["date_to"];
    		$exportType = $_REQUEST["export"];
    	}
    	
    	ini_set ( "allow_url_include", true );
    	if (isset ( $exportType )) {
    		$varurl = 'http://localhost:8080/JavaBridge/java/Java.inc';
    		require_once $varurl;
    		$System = java ( "java.lang.System" );
    		// echo $System->getProperties();
    		try {
    			java ( "java.lang.Class" )->forName ( "com.mysql.jdbc.Driver" );
    			$connection = java ( "java.sql.DriverManager" )->getConnection ( "jdbc:mysql://localhost/eb", "root", "" );
    			$root = realpath ( "." );
    			$in = $root . "/NewMain.jrxml";
    			$report = java ( "net.sf.jasperreports.engine.JasperCompileManager" )->compileReport ( $in );
    	
    			$dateFormat = new java ( "java.text.SimpleDateFormat", "yy/MM/dd" );
    			$d = $dateFormat->parse ( $from_date );
    			$d2 = $dateFormat->parse ( $to_date );
    			$params = new java ( "java.util.HashMap" );
    	
    			$params->put ( "facility_id", intval ( $facility_id ) ); // dây là 1 param
    	
    			$params->put ( "begin_date", new java ( "java.sql.Date", $d->getTime () ) );
    			$params->put ( "end_date", new java ( "java.sql.Date", $d2->getTime () ) );
    			$params->put ( "SUBREPORT_DIR", $root . "/sub/" );
    			
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
    }
}
