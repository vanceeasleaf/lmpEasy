<?php
$srcHome="/home/xggong/home1/zhouy/tcscripts/src";
$projHome=dirname(__FILE__);
$projName=basename($projHome);
if($stage==1){
	$units="metal";
	$species="CN-rotate";
	$method="greenkubo";
	$nodes=1;
$procs=4;$queue="q1.1";
$runTime=200000;
	for($i=1;$i<=1;$i+=15){
	submit("\$dumpv=1;\$dumpRate=5;\$runTime=$runTime;\$thick=1.44;\$langevin=0;\$hdeta=8*\$deta;\$timestep=.5e-3;\$usinglat=1;\$latx=22;\$laty=2;\$latz=1;");
	}
}
shell_exec("cp $projHome/sub.php $srcHome;");
require_once("$srcHome/submit.php");
?>
