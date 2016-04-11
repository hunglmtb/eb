<table border="0" cellpadding="0" cellspacing="0" width="100%"
	id="table3">
	<tr>
		<td style="border-bottom: 1px solid #000000;background-color: #666666;" height="100">
			<div
				style="position: absolute; height: 32px; z-index: 1; left: 350px; top: 43px"
				id="submenu">
			@include('partials.submenu', $subMenus)
			</div>
			@include('partials.menu')
			<div
				style="position: absolute; width: 100px; height: 84px; z-index: 0; left: 20px; top: 15px"
				id="layer3">
				<img border="0" src="/img/eb2.png?1" height='70'>
			</div>
			<p>&nbsp;
		</td>
	</tr>
	<tr>
		<td height="10"
			style="background-image: url('../img/g.png'); background-repeat: no-repeat; background-position: center top"></td>
	</tr>
</table>
@include('partials.user')
