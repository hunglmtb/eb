	@if((auth()->user() != null))
					<div>day 
					{{ auth()->user()->username }}
					</div>

				@else
					<p> huhu no user </p>
				@endif