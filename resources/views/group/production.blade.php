
@foreach( $filterGroups as $key => $filters )
		@if($key=='productionFilterGroup')
		<div class = "product_filter">
			@foreach( $filters as $filter )
				{{ Helper::buildFilter($filter) }}
			@endforeach
		</div>
		@elseif($key=='dateFilterGroup')
		<div class = "date_filter">
			@foreach( $filters as $filter )
				{{ Helper::selectDate($filter)}}
			@endforeach
		</div>
		@elseif($key=='frequenceFilterGroup')
		<div class = "product_filter">
			@foreach( $filters as $filter )
				{{ Helper::filter($filter) }}
			@endforeach
		</div>
		@endif
@endforeach