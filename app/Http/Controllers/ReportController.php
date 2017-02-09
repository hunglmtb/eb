<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\RptGroup;
use App\Models\RptReport;
use App\Models\RptParam;
use Illuminate\Http\Request;

class ReportController extends Controller {
	
	public function __construct() {
		$this->isReservedName = config ( 'database.default' ) === 'oracle';
		$this->middleware ( 'auth' );
	}
	
	public function loadReports(Request $request) {
		$data = $request->all ();
		$reports = RptReport::where('GROUP','=', $data['group_id'])
			->select("ID", "NAME", "FILE")
			->orderBy('ORDER')
			->get();
		return response ()->json ($reports);		
	}
	
	public function loadParams(Request $request) {
		$data = $request->all ();
		$reports = RptParam::where('REPORT_ID','=', $data['report_id'])
			->select("ID", "CODE", "NAME", "VALUE_TYPE", "REF_TABLE")
			->orderBy('ORDER')
			->get();
    	for($i=0;$i<count($reports);$i++){
    		if($reports[$i]->REF_TABLE)
    		{
				$tableName = strtolower ($reports[$i]->REF_TABLE);
				$mdlName = \Helper::camelize($tableName,'_');
				if (!$mdlName)  continue;
				$mdl = "App\Models\\$mdlName";
				$reports[$i]->REF_LIST = $mdl::select("ID", "NAME")
					->orderBy('NAME')
					->get();
			}
		}
		return response ()->json ($reports);		
	}
	
	public function _index() {
		$rpt_group = RptGroup::get ( [
				'ID',
				'NAME'
		] );
		
		$reports = [];
    	foreach($rpt_group as $row ){
    		if($row->ID)
    		{
    			$reports = RptReport::where('GROUP','=', $row->ID)
					->select("ID", "NAME", "FILE")
					->get();
				break;
			}
		}
		$params = [];
		/*
    	foreach($reports as $row ){
    		if($row->ID)
    		{
    			$params = RptParam::where('REPORT_ID','=', $row->ID)
					->select("ID", "CODE", "NAME", "VALUE_TYPE", "REF_TABLE")
					->orderBy('ORDER')
					->get();
				break;
			}
		}
		*/
		return view ( 'front.reports', ['rpt_group'=>$rpt_group, 'reports' => $reports, 'params' => $params] );
	}
}