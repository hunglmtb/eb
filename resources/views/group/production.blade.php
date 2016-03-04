
<div class = "product_filter">
	@foreach( $filterGroup as $filter )
			{{ Helper::buildFilter($filter['options'],$filter['name']) }}
	@endforeach
</div>