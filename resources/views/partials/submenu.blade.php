<ul id="css3menu0" class="topmenu">
	@foreach( $subMenus['pairs'] as $menu )
		@if($menu['link'] == $subMenus['currentSubMenu'])
			<li class="topmenu current_menu"><a href="#"
				style="height: 21px; line-height: 21px;">{{ $menu['title'] }}</a></li>
		@else
			<li class="topmenu"><a href="{{ $menu['link'] }} "
				style="height: 21px; line-height: 21px;">{{ $menu['title'] }}</a></li>
		@endif
	@endforeach
</ul>
