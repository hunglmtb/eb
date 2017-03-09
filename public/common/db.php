<?php
error_reporting(0);

$db_server_name="localhost";
$db_user="nhneu_tung";
$db_pass="tung#3";
$db_schema="energy_builder";

$db_survey_conn = @mysql_connect($db_server_name, $db_user, $db_pass) or die ("Could not connect");
mysql_select_db($db_schema,$db_survey_conn);

?>