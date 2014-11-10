<?php
$initsub=1;
require_once("sub.php");
require_once("$srcHome/config.php");
require_once("$srcHome/funcs.php");

if($argc==1){
	clean();
	require_once("$srcHome/toolSub.php");
	$stage=1;
	require("sub.php");
}
else if($argv[1]=="q"){
	$result=fopen("$projHome/result.txt","w");
        $obj=getObjs("$projHome/qloops.txt");
        $paras=getParas($obj);
         echo "id\tpercent\tstatus\tqueue\tprocs";
         for($j=0;$j<count($paras);$j++){
         	 printf("\t%s",$paras[$j]);
         	 fprintf($result,"%s\t",$paras[$j]);
         }
         printf("\tkappa");
         fprintf($result,"kappa");
                  printf("\ttotalE");
         fprintf($result,"\ttotalE");
                           printf("\tNatom");
         fprintf($result,"\tNatom");
                                    printf("\tE/N");
         fprintf($result,"\tE/N");
                                             printf("\tdisorder");
         fprintf($result,"\tdisorder");
                           printf("\n");
         fprintf($result,"\n");
        for($i=0;$i<count($obj);$i++){
        	$ob=$obj[$i];
        	        	$id=$ob["id"];
        	$pid=$ob["pid"];
        	$runTime=$ob["runTime"];
        	$lastline=shell_exec("cd $projHome/$id;tail -1 log.out 2>log");
        	$qstat=shell_exec("qstat $pid 2>&1|tail -1 ");
        	if(strpos($qstat,"Unknown Job Id")){
        		        	$time="complete";
        		   if(strpos($lastline,"builds")){
        	$status="C";
        	$percent="100%";
	           	}else{
	           		$status="E";
	           		$percent="1%";
	           	}
        	$queue="q0";
        	$nodes="0";
        	$procs="0";
        	}else{
        	list($null,$null,$null,$time,$status,$queue)=sscanf($qstat,"%s%s%s%s%s%s");
        	$info=shell_exec("qstat -f $pid 2>&1|grep nodes");

        list($null,$null,$info)=sscanf($info,"%s%s%s");
        $nnn=split(":ppn=",$info);
        $nodes=$nnn[0];
        $procs=$nnn[1];
        	        	list($step)=sscanf($lastline,"%d");
        	$percent=sprintf("%.1f%%",$step/$runTime*100);
        	}

        	echo $id."\t".$percent."\t".$status."\t".$queue."\t".$nodes."x".$procs;
        	for($j=0;$j<count($paras);$j++){
        		$key=$paras[$j];
         	 printf("\t%s",$ob[$key]===""?"def":$ob[$key]);
         	 if($percent+0>.5){
         	    	 fprintf($result,"%s\t",$ob[$key]==""?"def":$ob[$key]);
         	 }
         	          }
         	          if($percent+0>0){
         	          	  $dir="$projHome/$id";
         	          	  $dir=preg_replace("/\//","\\\/",$dir);
         	          	  $sed="sed 's/projHome=.\+/projHome=\"".$dir."\";/g ' qloop.php>qloop.php1";
         	          	  if(file_exists("post.php"))$postfile= "../post.php";
         	          	  else $postfile="";
         	          	  passthru("cd $projHome/$id;$sed;cat qloop.php1 $postfile>qloop.php2;cp qloop.php2 $srcHome/qloop.php;cp species.php $srcHome;$php $srcHome/profile.php;");
         	          $kappaline=shell_exec("cd $projHome/$id;tail -1 result.txt;");
         	          $kappa=trim(substr($kappaline,strpos($kappaline,'=')+1));
         	          echo "\t".$kappa;
         	          fprintf($result,"$kappa");
         	          $totalEline=shell_exec("cd $projHome/$id/minimize;tail -22 log.out| head -1;");
         	          list($null,$totalE)=sscanf($totalEline,"%d%f");
         	          	  echo "\t".$totalE;
         	          fprintf($result,"\t$totalE");
         	                   	          $Natomline=shell_exec("cd $projHome/$id/minimize;head -5 log.out|tail -1 ;");
         	          list($Natom)=sscanf($Natomline,"%d");
         	          if($Natom){
         	          	  echo "\t".$Natom;
         	          fprintf($result,"\t$Natom");
         	          $epn=$totalE/$Natom;
         	          
         	          echo "\t".$epn;
         	          fprintf($result,"\t$epn");
         	          }
         	           $disorderLine=shell_exec("cd $projHome/$id/minimize;mkdir disorder 2>err;cd disorder;cp $srcHome/in.disorder .;$APP_PATH<in.disorder 2>err 1>log;tail -1 disorder.txt  2>err;");
         	          list($null,$disorder)=sscanf($disorderLine,"%d%f");
         	          	  echo "\t".$disorder;
         	          fprintf($result,"\t$disorder");
         	          }
         	       fprintf($result,"\n");
         	 printf("\n");
       }
}else if($argv[1]=="clean"){clean();

}else if($argv[1]=="stop"){
	printf("Comfirm to stop all the simulation in this project?[y/n]");
$stdin = fopen('php://stdin', 'r');
list($s)=fscanf($stdin,"%s"); 
if($s!="y")exit("exit with no change.");
stop();

}
function stop(){
	global $projName;
		global $projHome;
		
		//����kill��ͬ�����̳���

$tarname="zy_$projName"."_";
if(strlen($tarname)<10){
	shell_exec("qstat|grep $tarname>tmp");
	$file=fopen("$projHome/tmp","r");
	while(list($pid)=fscanf($file,"%s")){
		$pid=intval($pid);
		echo "qdel:$pid\n";
		shell_exec("qdel $pid 2>log");
	}
}else{
		shell_exec("qstat|grep xggong >tmp");
	$file=fopen("$projHome/tmp","r");
	while(list($pid)=fscanf($file,"%s")){
		$pid=intval($pid);
		$jobnameString=shell_exec("qstat -f $pid |grep Job_Name");
		list($null,$null,$jobname)=sscanf($jobnameString,"%s%s%s");
		if(strstr($jobname,$tarname)){
			echo "qdel:$pid\n";
			shell_exec("qdel $pid 2>log");
		}
	}
}

		/*
//�����ļ��к�����kill��ԭ�����̵ĳ���
		$qloop=fopen("qloops.txt","r");
$n=0;			
while($json_string=fgets($qloop)){
        $obj[$n++]=json_decode($json_string,true);
        }
        for($i=0;$i<count($obj);$i++){
        	$ob=$obj[$i];
        	$pid=$ob["pid"];
	shell_exec("qdel $pid 2>log");
        }
        */
}
function getObjs($fileName){
		$qloop=fopen($fileName,"r");
		$n=0;
		while($json_string=fgets($qloop)){
        	$obj[$n++]=json_decode($json_string,true);
        }
        return 	$obj;
}
 function getParas($obj){
	$paras=array();
         for($i=0;$i<count($obj);$i++){
         	 $pa=$obj[$i];
         	 while($key=key($pa)){
         	 	 if($key=="id"||$key=="pid"||$key=="time"||$key=="cmd"||$key=="project"||$key=="nodes"||$key=="procs"||$key=="species"||$key=="units"||$key=="method"){next($pa);continue;}
         	 	 $flag=0;
         	 	 for($j=0;$j<count($paras);$j++){
         	 	 	 if($key==$paras[$j]){$flag=1;break;}
         	 	 }
         	 	 if($flag==0)$paras[count($paras)]=$key;
				next($pa);
		}
         }
         return $paras;
}
function clean(){
	global $projName;
	global $projHome;
	printf("Comfirm to clean all the files in this project?[y/n]");
	$stdin = fopen('php://stdin', 'r');
	list($s)=fscanf($stdin,"%s"); 
	if($s!="y")exit();
	stop();
		
	shell_exec("cd $projHome;ls >$projHome/tmp");
	$file=fopen("$projHome/tmp","r");
	while(list($ls)=fscanf($file,"%s")){
		if($ls=="sub.php"||$ls=="tmp"||$ls=="post.php")continue;
		echo "deleting:$ls\n";
		shell_exec("cd $projHome;rm -r $ls");
	}
	shell_exec("cd $projHome;rm tmp");
/*

        rm("log");
        //rm("qloops.txt","result.txt");
        */
}

?>
