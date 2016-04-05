<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
<!--<![endif]-->

<head>

<meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<title>ENERGY BUILDER</title>
<meta name="description" content=""/>
<meta name="_token" content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
<link rel="stylesheet" href="/css/css3menu0/style.css?4" />
<link rel="stylesheet" href="/common/css/jquery-ui.css" />
<link rel="stylesheet" href="/common/css/jquery.dataTables.css"/>
<script src="/common/js/jquery-2.1.3.js"></script>
<script type="text/javascript" src="/common/js/jquery.dataTables.js"></script> 
<script type="text/javascript" src="/common/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="/common/js/utils.js"></script>
<script src="/common/js/jquery-ui.js"></script>
@yield('script')
<link rel="stylesheet" href="/common/css/style.css" />
</head>

<body style="margin:0; overflow-x:hidden">
	<header role="banner">
		@include('partials.header')
		@yield('header')
	</header>
	<main role="main" class="container"> 
		@yield('main') 
	 </main>

	<footer role="contentinfo">
		@yield('footer')
		<div style="text-align: center; padding: 10px; color: #666">
			<font face="Arial" size="1">Copyright &copy; 2016 eDataViz LLC</font>
		</div>
	</footer>
</body>
</html>