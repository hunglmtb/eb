<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
<!--<![endif]-->

<head>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>ENERGY BUILDER</title>
<meta name="description" content="">
<meta name="_token" content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
<!-- <link rel="stylesheet" href="common/css/jquery-ui.css" /> -->
<link rel="stylesheet" href="/common/css/style.css" />
<link rel="stylesheet" href="/css/css3menu0/style.css?4" />
<link rel="stylesheet" href="/common/css/jquery-ui.css" />
	<script src="/dc/cdn/jquery-1.10.2.min.js"></script>
	<script src="/common/js/jquery-ui.js"></script>
	<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
@yield('script')
</head>

<body style="margin:0; overeu-x:hidden">
	<header role="banner">
		@include('partials.header')
		@yield('header')
	</header>
	<main role="main" class="container"> 
		@yield('main') 
	 </main>

	<footer role="contentinfo">
		@include('partials.footer')
		@yield('footer')
	</footer>
</body>
</html>