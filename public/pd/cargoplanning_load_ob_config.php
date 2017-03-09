<?php
include_once('../common/db.php');

$sSQL="select ID from plot_view_config where CHART_TYPE='cargoplanning' limit 1";
$result=mysql_query($sSQL) or die("-1");
$row=mysql_fetch_assoc($result);
echo $row["ID"];

?>
