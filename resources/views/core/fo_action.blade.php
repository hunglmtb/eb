@extends('core.fo')
@section('adaptData')
@parent
<script src="/common/js/eb_table_action.js"></script>
@stop

@section('floatWindow')
	@yield('editBox')
	@include('core.float_window')
@stop