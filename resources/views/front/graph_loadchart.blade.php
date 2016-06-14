<?php
include_once('../lib/db.php');
include_once('../lib/utils.php');
$title=$_REQUEST[title];
$minvalue=$_REQUEST["minvalue"];
$maxvalue=$_REQUEST["maxvalue"];
$isrange=(is_numeric($minvalue) && $maxvalue>$minvalue);
?>
<html>
<head>
  	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
  	<title>Load chart</title>
	<script src="/common/js/highcharts.js"></script>
	<script src="/common/js/highcharts_exporting.js?1"></script>
	<script src="/common/js/highcharts-offline-exporting.js?1"></script>

<script type='text/javascript'>
//<![CDATA[ 
$(function () {
    $('#container').highcharts({
        chart: {
            zoomType: 'xy'
        },
		credits: false,
        title: {
            text: <?php echo ($title?"'$title'":"null"); ?>
        },
        subtitle: {
            text: null
        },
        xAxis: {
            type: 'datetime',
            dateTimeLabelFormats: { // don't display the dummy year
                month: '%e. %b',
                year: '%b'
            },
            title: {
                text: 'Occur date'
            }
        },
        tooltip: {
            headerFormat: '<b>{series.name}</b><br>',
            pointFormat: '{point.x:%e. %b}: {point.y:.2f} kL'
        },

        plotOptions: {
            spline: {
                marker: {
                    enabled: true
                }
            }
        },
        exporting: {
            sourceWidth: $('#container').width(),
            sourceHeight: $('#container').height(),
            scale: 1,
            chartOptions: {
                subtitle: null
            }
        },
        series: [
<?php
$input=$_REQUEST[input];
$date_begin=$_REQUEST[date_begin];
$date_end=$_REQUEST[date_end];

$ss=explode(",",$input);
$k=0;
$maxV=0;
$minV=PHP_INT_MAX;
foreach($ss as $s)
{
	$xs=explode(":",$s);
	$chart_name=$xs[5];
	$chart_type=$xs[4];
	$types=explode("~",$xs[3]);
	$vfield=$types[0];

	//echo "-- $chart_name/$chart_type:$s --";

	$datefield="OCCUR_DATE";
	$is_eutest=false;
	if(substr($xs[2], 0, strlen("EU_TEST")) === "EU_TEST")
	{
		$is_eutest=true;
		$datefield="EFFECTIVE_DATE";
	}
	if($xs[0]=="TANK")
		$obj_type_id_field="TANK_ID";
	else if($xs[0]=="STORAGE")
		$obj_type_id_field="STORAGE_ID";
	else if($xs[0]=="FLOW")
		$obj_type_id_field="FLOW_ID";
	else if($xs[0]=="ENERGY_UNIT")
	{
		$obj_type_id_field="EU_ID";
		$chart_type=$xs[5];
		$chart_name=$xs[6];
		$vfield=$xs[3];
		$types=explode("~",$xs[4]);
		$phase_type=$types[0];
	}
	
	$pos=strpos($xs[3],"@");
	if($pos>0)
	{
		$xs[3]=substr($xs[3],$pos+1);
	}

	echo ($k>0?",":"")."			{
            type: '$chart_type',
            name: '$chart_name',
            data: [
";
	$sSQL="select $vfield V, DATE_FORMAT($datefield,'%Y,%m-1,%d') D from $xs[2] where $obj_type_id_field=$xs[1] ".(($xs[0]=="ENERGY_UNIT" && !$is_eutest)?"and FLOW_PHASE=$phase_type ":"")." and $datefield between '".toDateString($date_begin)."' and '".toDateString($date_end)."' order by $datefield limit 300";
	
	//echo "---".$sSQL;
	$result=mysql_query($sSQL) or die("fail: ".$sSQL."-> error:".mysql_error());
	$i=0;
	while($row=mysql_fetch_array($result))
	{
		if($row[V]=="") $row[V]=0;
		if($row[V]>$maxV)$maxV=$row[V];
		if($row[V]<$minV)$minV=$row[V];
		if($i>0) echo ",\r\n";
		echo "                [Date.UTC($row[D]), $row[V]   ]";
		$i++;
	}
	echo "            ]}\r\n";
	$k++;
}

$min1=($minV<0?$minV:0);
$div=5;
if($isrange){
	$min1=$minvalue;
	$max1=$maxvalue;
}
else{
	$x=ceil($maxV);
	$xs=strval($x);
	$xl=strlen($xs)-1;
	$n=(int)$xs[0];
	$t=pow(10,$xl);
	$x=ceil(2*$maxV/$t)/2;
	$max1=$x*$t;
	if($max1/$div*($div-1)>$maxV){
		$max1 = $max1/$div*($div-1);
		$div -= 1;
	}
}
$tickInterval1=($max1-($min1>0?$min1:0))/$div;
?>
        ],
        yAxis: [{ // Primary yAxis
			min: <?php echo $min1; ?>,
			max: <?php echo $max1; ?>,
            labels: {
                format: '{value}',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            title: {
                text: 'Liquid Volume (kL)',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            opposite: false

        },{ // Secondary yAxis
<?php 
$tickInterval2=0;
$min2=0;
$x=convertUOM($tickInterval1,'kL','m3');
if(is_numeric($x)){
	$tickInterval2=$x;
	if($isrange)
		$min2=convertUOM($min1,'kL','m3');
}
if($tickInterval2>0){
	$max2=($min2<0?0:$min2)+$tickInterval2*$div;
	echo"
			min: $min2,
			max: $max2,
";
}
?>
            labels: {
                format: '{value}',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            title: {
                text: 'Gas Volume (scm)',
                style: {
                    color: 'green'
                }
            },
            opposite: true

        }
		]
    });
});


//]]>  

</script>
</head>
<body>
	<div id="container" style="min-width: 400px; height: 380px; margin: 0 auto"></div>
</body>


</html>
