<div class = "product_filter">
{{ Helper::filter(array('default'=>array('value'=>'','name'=>'All'),'model'=>'App\Models\CodeReadingFrequency','filteName'=>'Record Frequency'))}}

{{ Helper::filter(
	array('default'=>array('value'=>'','name'=>'All'),
		'model'=>'App\Models\CodeFlowPhase',
		'filteName'=>'Phase Type'
))
}}

</div>