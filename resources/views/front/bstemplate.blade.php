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
<meta name="_token" content="{!! csrf_token() !!}" />
</head>

<body>
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