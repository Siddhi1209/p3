<?php

error_reporting(0);
                //header('Location: https://10.0.6.125:8080/under_construction/');
                //header('Location: https://10.176.29.189:9090/pace/Online_portal/pages/smart_external.php');
                session_start();
                //if($_SESSION['portal'] !="Smartmonitor"){
                //      header('location:../login.php');
                //}
        require("../config_apr_2.php");
                //print_r($_SESSION);
                $mldate = trim(file_get_contents("/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/MFLAGS_D"));
                $sys_stat = trim(file_get_contents("/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/sys_stat1.txt_bkp"));
                $portal_ip = trim(file_get_contents("/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/portal_nslookup.txt"));
                $getDDcrr = $_GET['date'];
                //ip address block
                $ipadd=explode(",",file_get_contents("card_folder/ipadd.txt"));
                //$ipofpc=$_SERVER['REMOTE_ADDR'];

                /*if(!in_array($ipofpc,$ipadd)){
                        echo "<script>alert('NOT PSO PC');window.location.href='https://10.0.6.125:8080/'</script>";
                }*/
                if(empty($getDDcrr)){
                        $crdate  = $mldate;
                }
                else{
                        $crdate  = $getDDcrr;
                }

                $dateyear=trim(substr($crdate,0,4));
                $dateyearmonth=trim(substr($crdate,0,6));
                $currpath = "/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/".$dateyear."/".$dateyearmonth."/".$crdate."/";
                $prevdate=date("Ymd",strtotime("-1 day",strtotime($crdate)));
                if($mldate == $crdate){
                        $currpath = "/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/";
                }
                else{
                        $currpath = "/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/".$dateyear."/".$dateyearmonth."/".$crdate."/";
                }

                $prevdate=date("Ymd",strtotime("-1 day",strtotime($crdate)));

                $tpsdata = explode("at",file_get_contents($currpath."peak_tps.".$crdate));
                $hours=substr(trim($tpsdata[1]),0,2);
                $minute=substr(trim($tpsdata[1]),2,2);
                $sec=substr(trim($tpsdata[1]),4,2);
                $peaktime=trim($hours).":".trim($minute).":".trim($sec);
                $MasterDecider=explode(".",file_get_contents("/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/DR_CHECK"));
                switch($MasterDecider[1]){
                        case "0":
                                $DBdecider='PR';
                                $trickleuploadserver="BHAGA";
                                break;
                        case "176":
                                $DBdecider='DR(No Auto Switch)';
                                $trickleuploadserver="PALAR";
                                break;
                        case "189":
                                $DBdecider='NR(No Auto Switch)';
                                $trickleuploadserver="BRAMHA";
                                break;
                        default:
                                $DBdecider='PR';
                                break;
                }

                $imageDB = trim(shell_exec("cat /opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/Image.txt"));
                if($imageDB=='0'){
                        $icon="<i class='fa fa-thumbs-up'></i>";
                        $classcolor="background-color:green";
                        $classglow="";
                }
                else if($imageDB=='1'){
                        $icon="<i class='fa fa-thumbs-down'></i>";
                        $classcolor="background-color:red";
                        $classglow="glow";
                }

                $showerfeed_status = trim(shell_exec("cat /opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/shfeed_requested_job_status.txt|grep NR|head -1|awk '{print $1}'"));
                $showerfeed_status_time = strtotime(shell_exec("ls -lrt /opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/shfeed_requested_job_status.txt|awk '{print $6,$7,$8}'"));
                $showerfeed_status_queue = trim(shell_exec("cat /opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/SFDQ_sql_error.txt_$crdate|wc -l'"));
                $smsne_status = trim(shell_exec("cat /opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/smsne.txt|wc -l"));
                $currtime=strtotime(date("M d H:i"));
                $difftimesec=$currtime-$showerfeed_status_time;
                if($showerfeed_status=='NR' || $showerfeed_status_queue > 0){
                        $icon_showerfeed="<i class='fa fa-thumbs-down'></i>";
                        $classcolor_showerfeed="background-color:red";
                        $classglow_showerfeed="glow";
                }
                else if($difftimesec < 1200 && $showerfeed_status_queue==0){

                        $icon_showerfeed="<i class='fa fa-thumbs-up'></i>";
                        $classcolor_showerfeed="background-color:green";
                        $classglow_showerfeed="";
                }
                else{
                        $icon_showerfeed="<i class='fa fa-warning'></i>";
                        $classcolor_showerfeed="background-color:#ff6700";
                }

                ///--------max connection for mr start---------/////
                $mrmaxfile=file("/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/mrmax_conn.txt");
                foreach($mrmaxfile as $index => $mrdata){
                        $mrdataexplode=explode(":",$mrdata);
                        //print_r($mrdataexplode);
                        if($mrdataexplode[1]>0){
                                $maxarray[]="greater";
                        }
                        else{
                                $maxarray[]="normal";
                        }
                }
                if(in_array("greater",$maxarray)){
                        $icon_mrconnection="<i class='fa fa-thumbs-down'></i>";
                        $classcolor_mrconnection="background-color:orange";
                        $classglow_mrconnection="glow";
                        $classtitle_mrconnection="MR Max Connection limit greater than Zero";
                }
                else{
                        $icon_mrconnection="<i class='fa fa-thumbs-up'></i>";
                        $classcolor_mrconnection="background-color:green";
                        $classglow_mrconnection="";
                        $classtitle_mrconnection="MR Max Connection limit is Proper";
                }

                $nslookup=explode('.',trim(file_get_contents("/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/nslookup.txt")));
                //print_r($nslookup);
                switch($nslookup[1]){
                        case "0":
                                $enquiry='PR';
                                break;
                        case "176":
                                $enquiry='DR';
                                break;
                        case "189":
                                $enquiry='NR';
                                break;
                        default:
                                $enquiry='NR';
                                break;
                }
                $loggapdata1=trim(shell_exec("cat /opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/loggap.txt|grep 'Total Gaps till now'|awk -F ':' '{print $2}'"));
                $loggapdatadr=shell_exec("cat /opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/loggap_dr.txt|grep 'Total Gaps till now'|awk -F ':' '{print $2}'");
                $online_time=shell_exec("cat ".$currpath."CBS_ONLINE.txt.".$crdate."|awk -F'=' '{print $2}'");
                $online_timesubstr=substr($online_time,0,2);
                $online_timesubstr2=substr($online_time,2,2);
                $neftinvalid=trim(shell_exec("cat ".$currpath."neftinvalid.txt.".$crdate."|awk -F':' '{print $2}'"));
                $neftnightinvalid=trim(shell_exec("cat ".$currpath."neftnightinvalid.txt.".$crdate."|awk -F':' '{print $2}'"));
                if($neftinvalid!='0' || $neftnightinvalid!='0'){
                        $iconneftinvalid="<i class='fa fa-thumbs-down'></i>";
                        $classcolorneftinvalid="background-color:red";
                        $classglowneftinvalid="glow";
                }
                else{
                        $iconneftinvalid="<i class='fa fa-thumbs-up'></i>";
                        $classcolorneftinvalid="background-color:#db8b0b";
                        $classglowneftinvalid="";
                }
                $repostfailcount=shell_exec("cat ".$currpath."NNEF_REPOST_FAIL_".$crdate.".txt|wc -l");
                if($repostfailcount==0){
                        $repostfailcolor="background-color:green;color:white";
                        $classglowrepostfail="";
                }
                else{
                        $repostfailcolor="background-color:red;color:white";
                        $classglowrepostfail="glow";
                }
                if($smsne_status > 0){
                        $classglow_smsne="glow";
                }
                $querysql=trim(shell_exec("cat /opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/sql_connectivity.txt"));
                if($querysql==""){$querysql="<i class='fa fa-thumbs-up'></i>";}
?>
<!DOCTYPE html>
<html>
<head>
        <meta charset="UTF-8" http-equiv="refresh" content="30">
        <?php header("Cache-Control:no-cache,must-revalidate");?>
    <title>✔ S.M.A.R.T. - 🚩$🌍 ✔  <?php echo $_SESSION['username'];?></title>
        <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
        <meta http-equiv="refresh" content="600">
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css?x=<?php echo rand(5555,9999); ?>" rel="stylesheet" type="text/css" />
    <link href="../font-awesome/css/font-awesome.min.css?x=<?php echo rand(5555,9999); ?>" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.min.css?x=<?php echo rand(5555,9999); ?>" rel="stylesheet" type="text/css" />
    <link href="../css/skins/_all-skins.min.css?x=<?php echo rand(5555,9999); ?>" rel="stylesheet" type="text/css" />
    <link href="../css/compactPage.css?x=<?php echo rand(5555,9999); ?>" rel="stylesheet" type="text/css" />
        <link href="../css/animate.css" rel="stylesheet" type="text/css"/>
        <link href="../css/normalize.min.css" rel="stylesheet" >
        <link href="../css/progress.css" rel="stylesheet" >
        <link href="../css/darklight.css" rel="stylesheet" type="text/css" />
        <link href="enjoyhint/enjoyhint.css" rel="stylesheet" type="text/css">
        <link href="../css/swal/app.css" rel="stylesheet" type="text/css" />
        <style>
                .shakealarm {
                  animation: shake 0.5s;
                  animation-iteration-count: infinite;
                  background-color:red;
                  color:white;
                }
                .shakeinfo {
                  animation: shake 0.5s;
                  animation-iteration-count: infinite;
                }

                @keyframes shake {
                  0% { transform: translate(1px, 1px) rotate(0deg); }
                  10% { transform: translate(-1px, -2px) rotate(-1deg); }
                  20% { transform: translate(-3px, 0px) rotate(1deg); }
                  30% { transform: translate(3px, 2px) rotate(0deg); }
                  40% { transform: translate(1px, -1px) rotate(1deg); }
                  50% { transform: translate(-1px, 2px) rotate(-1deg); }
                  60% { transform: translate(-3px, 1px) rotate(0deg); }
                  70% { transform: translate(3px, 1px) rotate(-1deg); }
                  80% { transform: translate(-1px, -1px) rotate(1deg); }
                  90% { transform: translate(1px, 2px) rotate(0deg); }
                  100% { transform: translate(1px, -2px) rotate(-1deg); }
                }
                ::selection {
                          color: white;
                          background: #00c0ef;
                        }
        </style>
        <script src="../js/swaljs/sweetalert.min.js" type="text/javascript"></script>
        <div id="dateModal" class="modal fade" role="dialog">
                <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                                  <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                         <h4 class="panel-heading modal-title ">Enter Date </h4>
                                  </div>
                                  <div class="modal-body text-center">
                                        <input type="date" class="form-control" id="ll" onchange="pageRedirection()" required>  <br>
                                  </div>
                        </div>
                </div>
        </div>
        <style>
        @media(max-width:990px)
        {
                #myheader.main-header{
                        display: none !important;
                }
        }
        .noCursor{
                cursor:default !important;
        }
        .bg-tcs{
                backgrounnd-color:#486092;
        }
        .stylemargin{
                margin-top:10px;
                color:white;
                border-radius:6px;
                margin-left:-7px;
                font-family: arial;
                padding: 5px 6px;
                font-size: 14px;
        }
        </style>
</head>
<!-- Left side column. contains the logo and sidebar -->
<?php
$file = $_SERVER['PHP_SELF']; $name = basename($file);
if($_GET['date'] == '' && $sys_stat == 'N' && $DBdecider=='PR'){
        echo '<script type="text/javascript">
                   swal ({
                                title:"Attention !! Night Region Activated !!",
                                button:false,
                                classname:"red-bg",
                                text: "Window will automatically Redirect To Night Region!!!",
                                icon:"info",
                                closeOnClickOutside:false,
                        });
                        window.setTimeout(function(){
                           window.location.href="eod.php";
                        },2000)
        </script>';
}
if($portal_ip=='10.176.29.189'){
        echo '<script type="text/javascript">
                   swal ({
                                title:"Portal Is On DR server",
                                button:false,
                                classname:"red-bg",
                                text: "Window will automatically Redirect To DR Portal Server!!!",
                                icon:"info",
                                closeOnClickOutside:false,
                        });
                        window.setTimeout(function(){
                           window.location.href="https://10.176.29.189:9090/pace/Online_portal/pages/smart.php";
                        },4000)
        </script>';
}
?>
<body class="skin-blue sidebar-collapse" oncontextmenu="return false;" id="canvas" >
        <div class="wrapper">
                <header class="main-header" id="myheader" style="min-width: 173vw;">
                        <a href="smart.php" title="Smart Monitoring and Reporting tool By TATA Consultancy Services Developed By CBS-PSO Team." class="logo bg-black-active welcomepso"><img src="../img/smart_logo_2016_small.jpg" alt="TCS logo" style="background-color:#fff;margin-left:-30px;margin-top:-43px;width:245px;height:135px;"></a>
                        <nav class="navbar navbar-static-top bg-black-active" role="navigation" style="height:40px;!important">
                                <a href="#" class="sidebar-toggle bg-aqua-active" data-toggle="offcanvas" role="button">
                                        <span class="sr-only">Toggle navigation</span>
                                </a>
                                <div class="navbar-custom-menu" style="float:none !important">
                                        <ul class="nav navbar-nav">
                                                <div class="switchWrap">
                                                        <button class="switchBtn">
                                                                <svg viewBox="0 0 145 145">
                                                                        <defs>
                                                                                <style>.cls-1,.cls-3{fill:#fff;}.cls-1{stroke:#fff;}.cls-1,.cls-2,.cls-3{stroke-miterlimit:10;}.cls-2,.cls-3{stroke:#000;}</style>
                                                                        </defs>
                                                                        <path id="right-circle" class="cls-1" d="M302,240a32,32,0,0,1,0-64,64,64,0,0,1,0,128,32,32,0,0,0,0-64Z" transform="translate(-237.5 -175.5)" />
                                                                        <circle id="right-dot" class="cls-2" cx="64.29" cy="31.86" r="11.5" />
                                                                        <path id="left-circle" class="cls-2" d="M302,176a64,64,0,0,0,0,128,32,32,0,0,0,0-64,32,32,0,0,1,0-64Z" transform="translate(-237.5 -175.5)" />
                                                                        <circle id="left-dot" class="cls-3" cx="64.29" cy="95.86" r="11.5" />
                                                                </svg>
                                                        </button>
                                                </div>
                                        </ul>
                                        <ul class="nav navbar-nav">
                                                <li><span onclick="tourstart()" class="btn btn-success stylemargin">APP Tour</span></li>
                                        </ul>
                                        <!--<ul class="nav navbar-nav">
                                                <a class=" btn btn-info financeoneglow stylemargin f1file_systems" target="_blank" href="finance_one.php">F1</a>
                                        </ul>
                                        <ul class="nav navbar-nav monitordisplay">
                                                <a class="btn monitorfilestatus stylemargin" target="_blank"></a>
                                        </ul>-->
                                        <ul class="nav navbar-nav">
                                                <span class="btn btn-success noCursor stylemargin">We Are In <?php echo $DBdecider; ?></span>
                                        </ul>
                                        <ul class="nav navbar-nav ">
                                                <span class="btn btn-primary noCursor stylemargin">Enquiry In <?php echo $enquiry; ?></span>
                                        </ul>
                                        <?php
                                        if($DBdecider=='PR'){ ?>
                                        <ul class="nav navbar-nav">
                                                <a class=" btn btn-warning stylemargin" href="eod.php?date=<?php echo $prevdate; ?>">Night Region</a>
                                        </ul>
                                        <?php } else{ ?>
                                        <ul class="nav navbar-nav">
                                                <a class=" btn btn-warning stylemargin" href="eod.php?date=<?php echo $prevdate; ?>">Night Region</a>
                                        </ul>
                                        <?php } ?>
                                        <ul class="nav navbar-nav">
                                                <span data-toggle="modal"  class="btn btn-info stylemargin backdated" data-target="#dateModal"><b>-Change <span style="color:white">Date</span>- </b></span>
                                        </ul>
                                        <ul class="nav navbar-nav">
                                                <span class="noCursor btn bg-navy btn-default stylemargin"><?php echo 'MFLAG : '.$crdate; ?></span>
                                        </ul>
                                        <ul class="nav navbar-nav">
                                                <p class="btn btn-success square-btn-adjust noCursor stylemargin" id="de" style="font-weight:bold; "><i class="fa fa-clock-o" style="font-weight:bold; font-size:10px;"></i></p>
                                        </ul>
                                        <ul class="nav navbar-nav">
                                                <span data-toggle="modal" class="btn btn-info stylemargin" data-target="#tools"><i class="fa fa-cog"></i> Tools</span>
                                        </ul>
                                        <ul class="nav navbar-nav" >
                                                <span class="btn btn-success onlinetime noCursor stylemargin"><?php echo "Online at  ".$online_timesubstr.":".$online_timesubstr2;?></span>
                                        </ul>
                                        <ul class="nav navbar-nav" >
                                                <span class="btn btn-warning noCursor stylemargin" title="Cache Clear and Tab Refresh" id="timer"></span>
                                        </ul>
                                        <ul class="nav navbar-nav" style="margin-left:23px;">
                                                <li><img style="width:350px;height:43px;border:2px solid white;margin-top:1%;padding:2px;" src="../img/health.png"></li>
                                        </ul>
                                        <ul class="nav navbar-nav" >
                                                <a href='../logout.php'><span class="btn btn-info stylemargin" title="Logout">Logout</span></a>
                                        </ul>
                                </div>
                        </nav>
                </header>
                <aside class="main-sidebar" style="position:fixed;">
                        <section class="sidebar">
                                <ul class="sidebar-menu">
                                        <li class="header">MAIN NAVIGATION</li>
                                        <li><a href="../dashboard.php?date=<?php echo $crdate; ?>"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                                        <li><a href="#"><i class="fa fa-edit"></i> <span>Application Monitoring</span><i class="fa fa-angle-left pull-right"></i></a>
                                        <ul class="treeview-menu">
                                                <li><a href="applnmonitor.php"><i class="fa fa-circle-o"></i><span>Gateways & Queues</span></a></li>
                                                <li><a href="SystemUtilization.php"><i class="fa fa-sun-o"></i><span> System Utilization</span></a></li>
                                        </ul>
                                        <li><a href="Reposting.php"><i class="fa fa-sun-o"></i><span> Reposting</span></a>
                                        <li><a href="#"><i class="fa fa-edit"></i> <span>Channels</span><i class="fa fa-angle-left pull-right"></i></a>
                                        <ul class="treeview-menu">
                                                <li> <a href="rtgs.php"><i class="fa fa-circle-o"></i> <span>RTGS Monitoring</span></a></li>
                                                <li> <a href="atminb.php"><i class="fa fa-circle-o"></i> <span>ATM & INB</span></a></li>
                                        </ul>
                                        <li><a href="TrickleFeedNew.php"><i class="fa fa-sun-o"></i><span> Trickle Feed</span></a></li>
                                        <li><a href="finance_one.php"><i class="fa fa-sun-o"></i><span> Finance One</span></a></li>
                                        <li><a href="Miscellenous.php"><i class="fa fa-sun-o"></i><span> Miscellenous </span></a></li>
                                        <li><a href="api.php"><i class="fa fa-sun-o"></i><span> Flash Report for IT incident</span></a>
                                        <li><a href="/pace/Online_portal/tree.php"><i class="fa fa-sun-o"></i><span>RTGS Tree</span></a></li>
                                        <li><a href="#"><i class="fa fa-edit"></i> <span>Monthly Port Details</span><i class="fa fa-angle-left pull-right"></i></a>
                                        <ul class="treeview-menu">
                                                <li><a target="_blank" href="mrgraphjsonMonth.php"><i class="fa fa-circle-o"></i> <span>MR 6101 (UPI)</span></a></li>
                                                <li><a target="_blank" href="mrgraphjsonMonth6036.php"><i class="fa fa-circle-o"></i><span>MR 6036 (UPI)</span></a>
                                                <li><a target="_blank" href="mrgraphjsonMonth6103.php"><i class="fa fa-circle-o"></i><span>MR 6103 (LOTUS)</span></a>
                                                <li><a target="_blank" href="b24graphjsonMonth6001.php"><i class="fa fa-circle-o"></i><span>B24 6001 (LOTUS)</span></a>
                                                <li><a target="_blank" href="inbgraphjsonMonth7014.php"><i class="fa fa-circle-o"></i><span>INB 7014 (LOTUS)</span></a>
                                                <li><a target="_blank" href="inbgraphjsonMonth6026.php"><i class="fa fa-circle-o"></i><span>INB 6026 </span></a>
                                                <li><a target="_blank" href="inbgraphjsonMonth6027.php"><i class="fa fa-circle-o"></i><span>INB 6027 </span></a>
                                                <li><a target="_blank" href="inbgraphjsonMonth6028.php"><i class="fa fa-circle-o"></i><span>INB 6028</span></a></li>
                                                <li><a target="_blank" href="inbgraphjsonMonth6029.php"><i class="fa fa-circle-o"></i><span>INB 6029</span></a></li>
                                                <li><a target="_blank" href="inbgraphjsonMonth6030.php"><i class="fa fa-circle-o"></i><span>INB 6030</span></a></li>
                                        </ul>
                                        <li><a href="eod.php"><i class="fa fa-sun-o"></i><span> Eod Monitoring</span></a></li>
                                        <li><a href="../pages/feedbackform.php"><i class="fa fa-sun-o"></i><span> FeedBack Form</span></a></li>
                                        <li><a href="../EventDash.php"><i class="fa fa-sun-o"></i><span> Event Monitoring</span></a></li>
                                        <li><a href="../DR_READINESS/DailyStatusup.php"><i class="fa fa-sun-o"></i><span>Daily Report</span></a></li>
                                        <li><a href="../pages/dr_mock.php"><i class="fa fa-sun-o"></i><span>Daily DR Mock</span></a></li>
                                </ul>
                        </section>
                </aside>
        <!-- Content Wrapper. Contains page content -->
                <div class="content-wrapper">
                        <section class="content-header">
                        <ul class="btn-group mainnav" style="margin-left:-40px;">
                                <li class="hassubs branches_telm"><a title="Branches Logged In" style="background-color:#db8b0b;border-radius: 6px;" target="_blank" href="branchlist.php"><i class="fa fa-university"></i><span class="brancheslogged"> <b><?php echo msl::app_stat1();?></b> </span></a></li>
                                <li class="hassubs teller_telm"><a onclick="window.open('teller_graph.php','','height=950, width=1000,scrollbars=yes,resizable=yes' );" title="No of Teller Logged" style="background-color:#555299;border-radius: 6px;"><i class="fa fa-users"></i><span class="tellerlogged"> <b><?php echo msl::app_stat_teller();?> </b></span></a></li>
                                <!--<li class="hassubs"><a href="#" title="ImageDB"  class="noCursor imageDB <?php echo $classglow;?>" style="border-radius: 6px;<?php echo $classcolor;?>"><?php echo "ImageDB ".$icon;?></a></li>-->


                                <!--<li class="hassubs"><a onclick="window.open('tpmtps.php?date=<?php echo $crdate;?>','','height=900, width=1200,scrollbars=yes,resizable=yes' );" style="background-color:#00a7d0;border-radius: 6px;" class="tpscurr tps_btn">--><!--TPS : <?php echo trim(shell_exec("cat ".$currpath."tps_all_8apps.".$crdate."|awk '{print $1}'")); ?>--><!--|Max : <?php echo $tpsdata[0]."at ".$peaktime; ?>--></a>
                                        <!--<ul class="dropdown">-->
                                                <!--<li class="subs"><a href="#"  data-toggle="modal" data-target="#myModal3" class="tpsbreakup" style="background-color:#555299;border-radius: 6px;">TPS Breakup</a></li>-->
                                                <!--<li class="subs" ><a href="#"  data-toggle="modal" data-target="#myModal2"  style="background-color:#555299;border-radius: 6px;">Compare Tps </a></li>-->
                                                <!--<li class="subs" ><a href="#"  data-toggle="modal" data-target="#myModalpeaktps"  style="background-color:#555299;border-radius: 6px;">Peak Tps </a></li>-->
                                        <!--</ul>-->
                                <!--</li>-->

                                <!--<li class="hassubs"><a href="#"  data-toggle="modal" data-target="#myModalpeaktps"  style="background-color:#555299;border-radius: 6px;">Peak TPS WEEK</a></li>-->

                                <li><a onclick="window.open('progressCBS.html','','height=300, width=1800,scrollbars=yes,resizable=yes' );" class="bg-green" style="border-radius: 6px;"><font color="white"><b> CBS FLOW </b></font></a></li>

                                <li class="hassubs"><a onclick="window.open('txn_desc.php','','height=600, width=1200,scrollbars=yes,resizable=yes' );" class="bg-green" style="border-radius: 6px;"><font color="white">TxN Desc</font></a></li>
                                <?php
                                        /*$nslookup=trim(file_get_contents("/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/nslookup.txt"));
                                        if(($nslookup == '10.189.20.101' && $DBdecider!='NR(No Auto Switch)' )|| $nslookup == '10.176.20.101'){  ?>
                                                <li class="hassubs"><a href="#" data-toggle="modal" data-target="#myModalNr" style="background-color:#555299;border-radius: 6px;" class="nrtps"><i class="fa fa-line-chart"></i> ALT TPS : <?php echo file_get_contents ($currpath."tps_all_8apps_nr.".$crdate); ?></a></li>
                                <?php } */?>
                                <li class="hassubs all_files"><a onclick="window.open('allfiles.php','','height=900, width=1500,scrollbars=yes,resizable=yes' );" title="All files location and data" style="background-color:#00a7d0;border-radius: 6px;cursor:pointer" >All Files </a></li>
                                <!--<li class="hassubs progress_day"><a onclick="window.open('progress_day.php?date=<?php echo $crdate ; ?>','','height=600, width=1500,scrollbars=yes,resizable=yes' );" style="background-color:#008d4c;border-radius: 6px;cursor:pointer" title="All Progress through the Day">Progress Throughout</a></li>-->
                                <li class="hassubs progress_day"><a onclick="window.open('branch_teller_interval.php?date=<?php echo $crdate ; ?>','','height=600, width=1500,scrollbars=yes,resizable=yes' );" style="background-color:#008d4c;border-radius: 6px;cursor:pointer" title="Branch Teller Interval">Branch Teller Interval</a></li>
                                <li class="hassubs milestone_det"><a class="" style="background-color:#6ab04c;color:white;border-radius: 6px;cursor:pointer" onclick="window.open('/pace/Online_portal/pages/milestone.php','','height=950, width=1920,scrollbars=yes,resizable=yes' );">Milestone Details</a></li>
                                <!--<li class="hassubs lotusupi_det" ><a href="#" style="background-color:blue;border-radius: 6px;">Graphs</a>
                                        <ul class="dropdown">
                                                <li class="subs hassubs">
                                                        <a href="#">UPI</a>
                                                        <ul class="dropdown">
                                                                <li class="subs"><a href="mr_iframe.php?port=_6101&hrs=900&date=<?php //echo $crdate ; ?>" target="_blank"><b>Port 6101(F)</b></a></li>
                                                                <li class="subs"><a href="mr_iframe.php?port=_7009&hrs=900&date=<?php //echo $crdate ; ?>" target="_blank"><b>Port 7009(F)</b></a></li>
                                                                <li class="subs"><a href="mr_iframe.php?port=_7010&hrs=900&date=<?php //echo $crdate ; ?>" target="_blank"><b>Port 7010(F)</b></a></li>
                                                                <li class="subs"><a href="mr_iframe.php?port=_6036&hrs=900&date=<?php //echo $crdate ; ?>" target="_blank"><b>Port 6036</b></a></li>
                                                                <li class="subs"><a href="mr_iframe.php?port=_6076&hrs=900&date=<?php //echo $crdate ; ?>" target="_blank"><b>Port 6076(NF)</b></a></li>
                                                                <li class="subs"><a href="mr_iframe.php?port=_6077&hrs=900&date=<?php //echo $crdate ; ?>" target="_blank"><b>Port 6077(NF)</b></a></li>
                                                        </ul>
                                                </li>
                                                <li class="subs"><a target="_blank" href="/pace/Online_portal/pages/graph_7006_b24.php" >PassBook 7006</a></li>
                                                <li class="subs hassubs">
                                                        <a href="#">LOTUS</a>
                                                        <ul class="dropdown">
                                                                <li class="subs"><a href="lotus_iframe.php?port=b24_6001&hrs=900&date=<?php //echo $crdate ; ?>" target="_blank" ><b>Port 6001(B24)</b></a></li>
                                                                <li class="subs"><a href="lotus_iframe.php?port=mr_6103&hrs=900&date=<?php //echo $crdate ; ?>" target="_blank" ><b>Port 6103(MR)</b></a></li>
                                                                <li class="subs"><a href="lotus_iframe.php?port=inb_7014&hrs=900&date=<?php //echo $crdate ; ?>" target="_blank" ><b>Port 7014(INB)</b></a></li>
                                                        </ul>
                                                </li>
                                        </ul>
                                </li>-->
                                <li class="hassubs">
                                        <a target="_blank" onclick="window.open('/pace/Online_portal/pages/mr_connection.php','','height=500,width=900,scrollbars=yes,resizable=yes');" title="<?php echo $classtitle_mrconnection;?>" class="mrconnection <?php echo $classglow_mrconnection;?>" style="border-radius: 6px;<?php echo $classcolor_mrconnection;?>">
                                        <?php echo "UPI(MR) ".$icon_mrconnection;?>
                                        </a>
                                </li>
                                <li class="hassubs legends"><a class="" style="background-color:#db8b0b;border-radius: 6px;cursor:pointer" onclick="window.open('legends.php','','height=700, width=1700,scrollbars=yes,resizable=yes' );">Legends</a></li>
                                <!--<li class="hassubs sms_noti"><a style="border-radius: 6px;background-color:#381460;cursor:pointer;" onclick="window.open('notificationengine.php','','height=950, width=1000,scrollbars=yes,resizable=yes' );" class=" smsne_class <?php echo $classglow_smsne;?>">SMSNE</a></li>-->
                                <!--<li class="hassubs log_gap" ><a class="loggapnr" style="border-radius: 6px;background-color:#00a7d0;cursor:pointer;" onclick="window.open('loggap.php','','height=1000, width=1200,scrollbars=yes,resizable=yes' );">Log Gap(NR/DR):<?php echo $loggapdata1."/".$loggapdatadr;?></a></li>
                                <li class="hassubs showefeed_det"><a onclick="window.open('SFDQ_queue.php?date=<?php echo $_GET['date']?>','','height=800,width=1600,scrollbars=yes,resizable=yes');window.open('shfeed_requested_job.php','','height=900,width=800,scrollbars=yes,resizable=yes')" title="Showerfeed Status" class="noCursor class_showerfeed <?php echo $classglow_showerfeed;?>" style="border-radius: 6px;<?php echo $classcolor_showerfeed;?>"><?php echo "Shfeed ".$icon_showerfeed;?></a></li>-->


                                <li class="hassubs neftinvalid_utr"><a target="_blank" onclick="window.open('gatewaymore.php?type=NEFT&&date=<?php echo $_GET['date']?>','','height=800,width=1400,scrollbars=yes,resizable=yes');" title="NEFT Invalid Count" class="neftinvalid <?php echo $classglowneftinvalid;?>" style="<?php echo $classcolorneftinvalid; ?>;border-radius: 6px;cursor:pointer"><?php echo "NEFT Invalid (D/N): ".$neftinvalid."/".$neftnightinvalid; ?></a></li>

                                <!-- Reposting Count Updated Script -->

                        #       <li class="hassubs neftrepostfail_utr"><a target="_blank" href="reposting_mon.php" title="Reposting Count" class="repostfail <?php echo $classglowrepostfail;?>" style="<?php echo $repostfailcolor; ?>;border-radius: 6px;"><?php echo "Reposting Status"; ?></a></li>


                                <li class="hassubs neftrepostfail_utr"><a target="_blank" href="neftrepostfail.php" title="NEFT Repost Fail Count" class="repostfail <?php echo $classglowrepostfail;?>" style="<?php echo $repostfailcolor; ?>;border-radius: 6px;"><?php echo "Repost Fail:".$repostfailcount; ?></a></li>

                                <!--<li class="hassubs"><a target="_blank" title="Extend Queryi [DB] password Now In All instances !!" class="btn btn-primary sqlconnection">SQL 9apps Queryi user - <?php echo $querysql;?></a></li>--->
                                <!--<li class="hassubs"><a title="Gateway Port Error !!" style="border-radius: 6px;cursor:pointer" onclick="window.open('gateway_matrix.php?date=<?php echo $crdate;?>','_blank');" class="btn btn-success channelmatrix ">Gateway HIVE</a></li>-->
                                <!--<li class="hassubs"><a class="btn btn-default bg-purple" target="_blank"  href="../../eventportal/IG_yonoupi.php">UPI/Yono Response</a></li>-->
                                <!--<li class="hassubs bg-navy"><a style="border-radius: 6px;cursor:pointer;"  class="kcusage" onclick="window.open('kcusage.php','','height=950, width=1600,scrollbars=yes,resizable=yes' );" >KCusage</a></li>-->
                                <!--<li class="hassubs bg-purple"><a style="border-radius: 6px;background-color:#381460;cursor:pointer;"  onclick="window.open('eoyeod.php','','height=950, width=1600,scrollbars=yes,resizable=yes' );" >EOD Signal Details</a></li>-->
                                <!--<li class="hassubs"><a target="_blank" href="/pace/Online_portal/tran_resp.php?dqp=i" class="btn btn-info ">Response- DqpTYPE</a></li>-->

                                <!--<li class="hassubs " style="cursor:none !important;"><button type="button" class="btn-primary"><b>DEDUP Status : </b><?php echo msl::dedup();?></button></li>-->
                                <!--<li class="hassubs bg-navy"><a style="border-radius: 6px;cursor:pointer;"  onclick="window.open('rollover.php','','height=950, width=1600,scrollbars=yes,resizable=yes' );" >Rollover and Payout</a></li>-->
                                <li class="hassubs  "><a style="background-color:#6ab04c;color:white;border-radius: 6px;cursor:pointer" class="rtgsincominggateway" onclick="window.open('rtgsmultiple_credit.php','','height=950, width=1600,scrollbars=yes,resizable=yes' );" >RTGS Incoming gateway</a></li>
                                <li class="hassubs  "><a style="background-color:#6ab04c;color:white;border-radius: 6px;cursor:pointer" class="rtgsincomingack" onclick="window.open('rtgsng_ack_in.php','','height=950, width=1600,scrollbars=yes,resizable=yes' );" >RTGS Incoming ACK C54</a></li>
                                <!--<li class="hassubs  "><a style="background-color:#6ab04c;color:white;border-radius: 6px;cursor:pointer" class="sms_cbs_ne" onclick="window.open('sms_cbs_ne.php','','height=950, width=1600,scrollbars=yes,resizable=yes' );" >SMS PUSHED DELAY</a></li>-->

                                <!--<li class="hassubs bg-navy"><a style="border-radius: 6px;cursor:pointer;"  class="MREN_query" onclick="window.open('MREN_query.php','','height=500, width=1600,scrollbars=yes,resizable=yes' );" >MREN query
                                </a></li>-->
                                <!--<li class="hassubs neftrepostfail_utr"><a target="_blank" href="EIS_Data.php" title="EIS Txn Count" >EIS Txn Count</a></li>-->
                                <!--<li id="channel-txn-log-btn" class="hassubs" style="background-color:green;color:white;cursor:pointer"><a target="_blank" href="channel_txn_log.php" title="Channel Txn Log" >Channel Txn Log</a></li>-->
                        </ul>
                        <!--TPS-->
                        <div id="id01" class="modal">
                                <form class="modal-content animate" action="https://10.0.40.8:9090/pace/Online_portal/pages/admin_action.php" method="post">
                                        <div class="container">
                                                <label for="uname"><b>Username</b></label>
                                                <input type="text" placeholder="Enter Username" name="uname" required>
                                                <label for="psw"><b>Password</b></label>
                                                <input type="password" placeholder="Enter Password" name="psw" required>
                                                <button type="submit" name="login" value="login">Login</button>
                                        </div>
                                </form>
                        </div>
                        <div id="myModal2" class="modal fade" role="dialog">
                                <div class="modal-dialog modal-lg">
                                <!-- Modal content-->
                                        <div class="modal-content">
                                                <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        <h4 class="panel-heading modal-title ">Select <span style="color:red">Date</span> To Compare <span style="color:green">TPS<span></h4>
                                                </div>
                                                <div class="modal-body text-center">
                                                        <form method="post" action="graphtpm_apr.php" target="_blank">
                                                        <label style="margin-right:2%;font-size:13px;">Previous Date</label><input type="date" style="font-size:20px;" name="startdate" required>
                                                        <label style="margin-right:2%;font-size:13px;">Recent/Current Date</label><input type="date" style="font-size:20px;" name="enddate" required>
                                                </div>
                                                <div class="modal-footer">
                                                        <input type="submit" name="submit" class="btn btn-success" value="Submit"></form>
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                </div>
                                        </div>
                                </div>
                        </div>
                        <div id="tools" class="modal fade" role="dialog">
                                <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                                <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        <h4 class="panel-heading modal-title "> Bytes <i class="bg-green fa fa-exchange"></i> KB <i class="bg-green fa fa-exchange"></i> MB <i class="bg-green fa fa-exchange"></i> GB <span class="label bg-navy">Converter</span></h4>
                                                </div>
                                                <div class="modal-body text-center">
                                                        <table class="text-center">
                                                                <thead>
                                                                        <tr>
                                                                                <th>Bytes</th>
                                                                                <th>KB</th>
                                                                                <th>MB</th>
                                                                                <th>GB</th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody>
                                                                        <tr>
                                                                                <td><input class="form-control" placeholder="Enter/Get Bytes" type="number" min="0" onkeyup="ByteConverter()"   required id="Bytes"></td>
                                                                                <td><input class="form-control" placeholder="Enter/Get KB" type="number" min="0" onkeyup="KBConverter()"        required id="KB"></td>
                                                                                <td><input class="form-control" placeholder="Enter/Get MB" type="number" min="0" onkeyup="MBConverter()"        required id="MB"></td>
                                                                                <td><input class="form-control" placeholder="Enter/Get GB" type="number" min="0" onkeyup="GBConverter()"        required id="GB"></td>
                                                                        </tr>
                                                                </tbody>
                                                        </table><br/>
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                </div>
                                        </div>
                                </div>
                        </div>

                        <div id="tpm_modal" class="modal fade" role="dialog" >
                                <div class="modal-dialog modal-md">
                                        <div class="modal-content">
                                                <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        <h4 class="panel-heading modal-title ">TPM Incomplete Batches Timestamp</h4>
                                                </div>
                                                <div class="modal-body text-center">
                                                        <table class="text-center" style="width:570px;">
                                                                <thead>
                                                                        <tr>
                                                                                <th style="width:150px;">File Count</th>
                                                                                <th>From</th>
                                                                                <th>To</th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                                        $tpm_data=explode("\n",file_get_contents("/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/tpm_sequence_file_count.txt.".$crdate));
                                                                        foreach($tpm_data as $key=>$value){
                                                                                echo "<tr>";
                                                                                $tpm_final_data = explode("|",$tpm_data[$key]);
                                                                                if($tpm_final_data[0]!= '12'){
                                                                                        echo "<td style='background-color:red;color:white'><b>".trim($tpm_final_data[0])."</b></td>";
                                                                                        echo "<td style='background-color:red;color:white'><b>".trim($tpm_final_data[1])."</b></td>";
                                                                                        echo "<td style='background-color:red;color:white'><b>".trim($tpm_final_data[2])."</b></td>";
                                                                                }
                                                                                else{
                                                                                        echo "<td style='background-color:green;color:white'><b>".trim($tpm_final_data[0])."</b></td>";
                                                                                        echo "<td style='background-color:green;color:white'><b>".trim($tpm_final_data[1])."</b></td>";
                                                                                        echo "<td style='background-color:green;color:white'><b>".trim($tpm_final_data[2])."</b></td>";
                                                                                }
                                                                                echo "</tr>";

                                                                        }
                                                                ?>
                                                                </tbody>
                                                        </table><br/>
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                </div>
                                        </div>
                                </div>
                        </div>

                        <div id="myModal3" class="modal fade" role="dialog">
                                <div class="modal-dialog modal-lg">
                                        <!-- Modal content-->
                                        <div class="modal-content">
                                                <div class="modal-body text-center">
                                                        <div class="row">
                                                                <div class="col-md-3"><span class="btn btn-danger">Branch Server :- <?php echo msl::tps_break(0);?></span></div>
                                                                <div class="col-md-3"><span class="btn btn-danger">Internet Banking:- <?php echo msl::tps_break(1);?></span></div>
                                                                <div class="col-md-3"><span class="btn btn-danger">ATM:- <?php echo msl::tps_break(2);?></span></div>
                                                                <div class="col-md-3"><span class="btn btn-danger">MR:- <?php echo (trim(shell_exec("cat ".$currpath."tpmtps_all_8apps.".$crdate."|awk '{print $1}'"))." - ".msl::tps_break(2)." - ".msl::tps_break(1)." - ".msl::tps_break(0));?></span></div>
                                                        </div><br/>
                                                        <div class="row">
                                                                <div class="col-md-4"><span class="btn btn-danger">NR/DR Branch Server :- <?php echo msl::nr_tps_break(0);?></span></div>
                                                                <div class="col-md-4"><a  class="btn btn-info" onclick="window.open('breakup.php?date=<?php echo date("Ymd",strtotime($crdate));?>','','height=900, width=1000,scrollbars=yes,resizable=yes' );" class="">TPS BREAKUP TABULAR</a></div>
                                                                <div class="col-md-4"><a style="background-color:#9818d6" class="btn btn-info" onclick="window.open('breakupgraph.php?date=<?php echo date("Y-m-d",strtotime($crdate));?>','','height=900, width=1200,scrollbars=yes,resizable=yes' );" class="tpscurr">TPS BREAKUP GRAPH</a></div>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>

                        <div id="myModalpeaktps" class="modal fade" role="dialog">
                                <div class="modal-dialog modal-lg text-bold">
                                        <!-- Modal content-->
                                        <div class="modal-content">
                                        <div>
                                                <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        <h4 class="panel-heading modal-title "><span class="btn btn-primary bg-navy">PEAK TPS OF THIS WEEK [95 Percentile]</span> (Current Day Peak Can be Found After 3.30PM)</h4>
                                                </div>
                                                <div class="modal-body text-center">
                                                        <div class="panel-body table-responsive">
                                                                <table class="table table-bordered table-striped text-center">
                                                                        <thead>
                                                                                <th>Date</th>
                                                                                <th>Day</th>
                                                                                <th>Peak TPS</th>
                                                                        </thead>
                                                                        <tbody>
                                                                        <?php
                                                                                if(file_exists("/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/peak_file_week.txt")){
                                                                                        $datapeakweek=file("/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/peak_file_week.txt");
                                                                                        //print_r($data);
                                                                                        foreach($datapeakweek as $key => $valuepeak){
                                                                                                $datexplode=explode(":",trim($valuepeak));
                                                                                                $tpsdata = explode("at",$datexplode[2]);
                                                                                                $hours=substr(trim($tpsdata[1]),0,2);
                                                                                                $minute=substr(trim($tpsdata[1]),2,2);
                                                                                                $sec=substr(trim($tpsdata[1]),4,2);
                                                                                                $peaktime=trim($hours).":".trim($minute).":".trim($sec);
                                                                                                //print_r($datexplode);
                                                                                                echo "<tr>";
                                                                                                $insidelink="'peaktps.php?date=".trim($datexplode[0])."','','height=1000,width=900,scrollbars=yes,resizable=yes'";
                                                                                                $link="window.open($insidelink);";
                                                                                                //echo $link;
                                                                                                        if($crdate==trim($datexplode[0])){
                                                                                                                echo "<td><a style='font-size:16px;' onclick=$link>".trim($datexplode[0])." (<span style='color:red'>Today</span>)(<span style='color:green'>Click For Breakup</span>)</a></td>";
                                                                                                        }
                                                                                                        else{
                                                                                                                echo "<td><a onclick=$link>".trim($datexplode[0])."(<span style='color:green'>Click For Breakup</span>)</a></td>";
                                                                                                        }
                                                                                                        echo "<td><span style='color:blue'>".trim($datexplode[1])."</span></td>";
                                                                                                        echo "<td><span style='color:red'>".$tpsdata[0]."</span> at <span style='color:green'>".$peaktime."</span></td>";
                                                                                                echo "</tr>";
                                                                                        }
                                                                                }
                                                                        ?>
                                                                        </tbody>
                                                                </table>
                                                        </div>
                                                </div>
                                                <div class="modal-footer text-center">
                                                        <p><i class="fa fa-info-circle" style='color:red'></i>  <b>Peak TPS</b> : The consistant Peak of Transaction Reached in the whole day calculated by Nearest Rank Method(<a href='https://en.wikipedia.org/wiki/Percentile#The_nearest-rank_method' target="_blank" style='color:blue'>Click to View theorem in Wikipedia</a>) after <span style='color:red'>removing 5% Overshoot</span> and <span style='color:green'>Taking 95 Percentile of Highest 60 values per 15mins slot between 0930-1530 [More Accurate Measure of a System]</span> data.</p>
                                                        <p><b>Max TPS</b> : The <span style='color:red'>Max Overshoot TPS</span> Reached in a Day.</p>
                                                </div>
                                        </div>
                                </div>
                        </div>

                        <div id="myModalqueue" class="modal fade" role="dialog">
                                <div class="modal-dialog modal-lg">
                                <!-- Modal content-->
                                        <div class="modal-content">
                                                <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        <h4 class="panel-heading modal-title ">Select <span style="color:red">Date</span> To Compare <span style="color:green">Queue<span></h4>
                                                </div>
                                                <div class="modal-body text-center">
                                                        <form method="GET" action="queue_graph2.php" target="_blank">
                                                                <label style="margin-right:2%;">Select the queue to compare</label>
                                                                        <select name="queuename" required>
                                                                                <option value="Error">--Select--</option>
                                                                                <option value="1-CASQ   ">CASQ  </option>
                                                                                <option value="2-CIFQ   ">CIFQ  </option>
                                                                                <option value="3-CIFQ  A">CIFQ A</option>
                                                                                <option value="4-CIFQ  I">CIFQ I</option>
                                                                                <option value="5-CIFQ  M">CIFQ M</option>
                                                                                <option value="6-CTAQ   ">CTAQ  </option>
                                                                                <option value="7-DEPQ   ">DEPQ  </option>
                                                                                <option value="8-DEPQ  A">DEPQ  </option>
                                                                                <option value="9-DEPQ  I">DEPQ A</option>
                                                                                <option value="10-DEPQ M">DEPQ I</option>
                                                                                <option value="11-DP1Q  ">DP1Q  </option>
                                                                                <option value="12-DP1Q A">DP1Q A</option>
                                                                                <option value="13-DP1Q I">DP1Q I</option>
                                                                                <option value="14-DP1Q M">DP1Q M</option>
                                                                                <option value="15-DP2Q  ">DP2Q  </option>
                                                                                <option value="16-DP2Q A">DP2Q A</option>
                                                                                <option value="17-DP2Q I">DP2Q I</option>
                                                                                <option value="18-DP2Q M">DP2Q M</option>
                                                                                <option value="19-DP3Q  ">DP3Q  </option>
                                                                                <option value="20-DP4Q  ">DP4Q  </option>
                                                                                <option value="21-DP5Q  ">DP5Q  </option>
                                                                                <option value="22-DP6Q  ">DP6Q  </option>
                                                                                <option value="23-DP7Q  ">DP7Q  </option>
                                                                                <option value="24-DP8Q  ">DP8Q  </option>
                                                                                <option value="25-DP9Q  ">DP9Q  </option>
                                                                                <option value="26-GL1Q  ">GL1Q  </option>
                                                                                <option value="27-EQ1Q  ">EQ1Q  </option>
                                                                                <option value="28-ELMQ  ">ELMQ  </option>
                                                                                <option value="29-GENQ  ">GENQ  </option>
                                                                                <option value="30-GENQ A">GENQ A</option>
                                                                                <option value="31-GENQ I">GENQ I</option>
                                                                                <option value="32-GENQ M">GENQ M</option>
                                                                                <option value="33-LONQ  ">LONQ  </option>
                                                                                <option value="34-OLRQ  ">OLRQ  </option>
                                                                                <option value="35-ONLQ  ">ONLQ  </option>
                                                                                <option value="36-PF1Q  ">PF1Q  </option>
                                                                                <option value="37-PFMQ  ">PFMQ  </option>
                                                                                <option value="38-RESPQ ">RESPQ </option>
                                                                                <option value="39-SBRQ  ">SBRQ  </option>
                                                                                <option value="40-SDVQ  ">SDVQ  </option>
                                                                                <option value="41-SPYQ  ">SPYQ  </option>
                                                                                <option value="42-VPIQ  ">VPIQ  </option>
                                                                                <option value="43-VV1Q  ">VV1Q  </option>
                                                                                <option value="44-VV2Q  ">VV2Q  </option>
                                                                                <option value="45-VV3Q  ">VV3Q  </option>
                                                                                <option value="46-ARCQ  ">ARCQ  </option>
                                                                                <option value="47-CC1Q  ">CC1Q  </option>
                                                                                <option value="48-KCCQ  ">KCCQ  </option>
                                                                                <option value="49-FA1Q  ">FA1Q  </option>
                                                                                <option value="50-SFDQ  ">SFDQ  </option>
                                                                        </select>
                                                        <input type="hidden" name="date" value='<?php echo $crdate;?>' style="margin-right:5%;" required>
                                                </div>
                                          <div class="modal-footer">
                                                <input type="submit" name="submitqueue" class="btn btn-success" value="Submit"></form>
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                          </div>
                                        </div>
                                </div>
                        </div>

                                <div id="myModal4" class="modal fade" role="dialog">
                                  <div class="modal-dialog modal-lg">
                                        <!-- Modal content-->
                                        <div class="modal-content">
                                          <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                 <h4 class="panel-heading modal-title ">Select <span style="color:red">Date</span> To Compare <span style="color:green">System Utilization</span></h4>
                                          </div>
                                          <div class="modal-body text-center">
                                                <form method="post" action="sys_graph.php" target="_blank">
                                                        <label style="margin-right:2%;">Recent/Current Date<span style="color:red;">*</span></label>
                                                        <input type="date" name="startdate" style="margin-right:5%;" required>
                                                        <label style="margin-right:2%;">Previous Date<span style="color:red;">*</span></label><input type="date" name="enddate" required><br><br>
                                                        <label style="margin-right:2%;">Select Server<span style="color:red;">*</span></label>
                                                        <select name="server"  required>
                                                                <option value="Error">--Select--</option>
                                                                <option value="topstat_m">Master</option>
                                                                <option value="topstat_s1">Slave1</option>
                                                                <option value="topstat_s2">Slave2</option>
                                                                <option value="topstat_s3">Slave3</option>
                                                                <option value="topstat_s4">Slave4</option>
                                                                <option value="topstat_s5">Slave5</option>
                                                                <option value="topstat_s6">Slave6</option>
                                                                <option value="topstat_s7">Slave7</option>
                                                                <option value="topstat_s8">Slave8</option>
                                                        </select>
                                                         <label style="margin-left:4%;">Processes<span style="color:red;">*</span></label>
                                                        <select name="processes"  required>
                                                                <option value="System Utilization">--System Utilization--</option>
                                                                <option value="Processes">Processes</option>
                                                        </select>
                                          </div>
                                          <div class="modal-footer">
                                                <input type="submit" name="submit" class="btn btn-success" value="Submit"></form>
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                          </div>
                                        </div>

                                  </div>
                                </div>
                                <div id="myModal5" class="modal fade" role="dialog">
                                  <div class="modal-dialog modal-lg">
                                        <!-- Modal content-->
                                        <div class="modal-content">
                                          <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                 <h4 class="panel-heading modal-title ">Select <span style="color:red;">Date</span> To Compare <span style="color:green;">Space Utilization</span></h4>
                                          </div>
                                          <div class="modal-body text-center">
                                                <form method="post" action="space_graph.php" target="_blank">
                                                        <label style="margin-right:2%;">Recent/Current Date<span style="color:red;">*</span></label>
                                                        <input type="date" name="startdate" style="margin-right:5%;" required>
                                                        <label style="margin-right:2%;">Previous Date<span style="color:red;">*</span></label><input type="date" name="enddate" required><br><br>
                                                        <label style="margin-right:2%;">Select Server<span style="color:red;">*</span></label>
                                                        <select name="server"  required>
                                                                <option value="Error">--Select--</option>
                                                                <option value="m">Master</option>
                                                                <option value="s1">Slave1</option>
                                                                <option value="s2">Slave2</option>
                                                                <option value="s3">Slave3</option>
                                                                <option value="s4">Slave4</option>
                                                                <option value="s5">Slave5</option>
                                                                <option value="s6">Slave6</option>
                                                                <option value="s7">Slave7</option>
                                                                <option value="s8">Slave8</option>
                                                        </select>
                                                        <label style="margin-left:4%;">Processes<span style="color:red;">*</span></label>
                                                        <select name="processes"  required>
                                                                        <option value="Error">--Select--</option>
                                                                        <option value="2">Data(D)%</option>
                                                                        <option value="3">Spool(D)%</option>
                                                                        <option value="4">Sysout(D)%</option>
                                                                        <option value="5">/Tmp%</option>
                                                                        <option value="6">Home%</option>
                                                                        <option value="7">Sys(C)%</option>
                                                                        <option value="8">Sys(N)%</option>
                                                                        <option value="9">Data(N)%</option>
                                                                        <option value="10">Spool(C)%</option>
                                                                        <option value="11">Ftparea%</option>
                                                                        <option value="12">/Opt%</option>
                                                                        <option value="13">/var%</option>
                                                        </select>
                                          </div>
                                          <div class="modal-footer">
                                                <input type="submit" name="submit" class="btn btn-success" value="Submit"></form>
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                          </div>
                                        </div>
                                  </div>
                                </div>
                        <div id="myModal6" class="modal fade" role="dialog">
                          <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                  <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                         <h4 class="panel-heading modal-title "> <h4><span style="float:right;font-size:18px;"><i class="fa fa-fw fa-clock-o"></i><?php echo timedisplay::timeUpdated("/opt/hpws/apache/cgi-bin/trials/bip/data/TESTING/rtgsProcTimeConsolidated",".");?></span>RTGS Inward Processing Time</h4>
                                                <div class="modal-body text-center">
                                                        <div class="inner">
                                                                <div class="table-responsive">
                                                                        <table class="table table-bordered table-striped text-center">
                                                                                <thead>
                                                                                        <th style="padding:0px;">Time(m)</th>
                                                                                        <th>RTGS</th>
                                                                                </thead>
                                                                                <tbody>
                                                                                        <?php
                                                                                                $rtgs->rtgss_time(0);
                                                                                        ?>
                                                                                </tbody>
                                                                        </table>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                          </div>
                        </div>
                        </section>
                        <!--<section class="content">
                                <div class="row">
                                        <div class="col-xs-12">
                                                <div class="box">
                                                        <div class="box-body">
                                                                <div class="col-md-6">
                                                                        <div class="box box-primary box-solid" id="boxjobs">
                                                                                <div class="box-header bg-tcs-active text-center">
                                                                                  <h3 class="box-title">Jobs</h3>
                                                                                  <a target="_blank" href="gatewayconso.php" style="float:left" class="btn btn-xs btn-success">See Gateway Consolidated</a>
                                                                                  <a target="_blank" href="intermediate_server_fs.php" style="float:left;margin-left:5px;" class="btn btn-xs btn-warning">See Intermediate server file status</a>
                                                                                <span class="jobstimepop" style="float:right;font-size:18px;"><img src="loading-bubbles.svg"><?php echo $yummy->timeUpdated("gateway.txt_8app",".");?></span>
                                                                                        <div class="box-tools pull-right">
                                                                                         <!--<button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>-->
                                                                                        <!--</div>
                                                                                </div>
                                                                                <div class="box-body table-responsive">
                                                                                        <table class="table table-bordered table-striped text-center">
                                                                                          <thead>
                                                                                                   <tr>
                                                                                                                <th style="width:110px;">JOB Name</th>
                                                                                                                <?php echo file_get_contents("server_card.php");?>
                                                                                                   </tr>
                                                                                          </thead>
                                                                                          <tbody class="jobsappend">
                                                                                                        <?php //$aplnMonior->gateway(); ?>
                                                                                          </tbody>
                                                                                        </table>
                                                                                        <div>
                                                                                                <span style="float:right;background-color:white;color:black;cursor:default;" class="btn btn-sm btn-primary">
                                                                                                        <i style="color:black" class='fa fa-fw fa-minus'></i> NA
                                                                                                        <i style="color:green" class='fa fa-check'></i> Running
                                                                                                        <i style="color:#f39c12" class='fa fa-fw fa-warning'></i> Not Updating
                                                                                                        <i style="color:red" class='fa fa-fw fa-ban'></i> NO TXN
                                                                                                        <i style="color:red" class='fa fa-fw fa-exclamation'></i> TIMESTAMP FREEZED
                                                                                                        <i style="color:red" class='fa fa-bolt'></i> MAX TCP
                                                                                                        <i style="color:red" class='fa fa-bell'></i> Unable To Process
                                                                                                        <i style="color:red" class='fa fa-close'></i> Not Running
                                                                                                </span>
                                                                                        </div>
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="col-md-6">
                                                                        <div class="box box-primary box-solid">
                                                                                <div class="box-header bg-tcs-active text-center">
                                                                                        <h3 class="box-title">Queue Buildup</h3>
                                                                                        <span class="queuetimepop" style="float:right;font-size:18px;"><img src="loading-bubbles.svg"><?php echo $yummy->timeUpdated("status_queue_8app.txt",".");?></span>
                                                                                        <div class="box-tools pull-right">
                                                                                        <!-- <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>-->
                                                                                        <!--</div>
                                                                                </div>
                                                                                <div class="panel-body table-responsive hehe">
                                                                                        <table class="table table-bordered table-striped text-center">
                                                                                                <thead>
                                                                                                        <tr>
                                                                                                                <th>Queue Name</th>
                                                                                                                <th><a onclick="window.open('queue_buildup_details.php?date=<?php //echo $crdate;?>&&flag=pace_buildup_i.txt','','height=1000, width=1900,scrollbars=yes,resizable=yes' );">Master  </a></th>
                                                                                                                <th><a onclick="window.open('queue_buildup_details.php?date=<?php //echo $crdate;?>&&flag=pace_buildup_I.txt','','height=1000, width=1900,scrollbars=yes,resizable=yes' );">Slave1 </a></th>
                                                                                                                <th><a onclick="window.open('queue_buildup_details.php?date=<?php //echo $crdate;?>&&flag=pace_buildup_a.txt','','height=1000, width=1900,scrollbars=yes,resizable=yes' );">Slave2 </a></th>
                                                                                                                <th><a onclick="window.open('queue_buildup_details.php?date=<?php //echo $crdate;?>&&flag=pace_buildup_b.txt','','height=1000, width=1900,scrollbars=yes,resizable=yes' );">Slave3 </a></th>
                                                                                                                <th><a onclick="window.open('queue_buildup_details.php?date=<?php //echo $crdate;?>&&flag=pace_buildup_c.txt','','height=1000, width=1900,scrollbars=yes,resizable=yes' );">Slave4 </a></th>
                                                                                                                <th><a onclick="window.open('queue_buildup_details.php?date=<?php //echo $crdate;?>&&flag=pace_buildup_d.txt','','height=1000, width=1900,scrollbars=yes,resizable=yes' );">Slave5 </a></th>
                                                                                                                <th><a onclick="window.open('queue_buildup_details.php?date=<?php //echo $crdate;?>&&flag=pace_buildup_e.txt','','height=1000, width=1900,scrollbars=yes,resizable=yes' );">Slave6 </a></th>
                                                                                                                <th><a onclick="window.open('queue_buildup_details.php?date=<?php //echo $crdate;?>&&flag=pace_buildup_f.txt','','height=1000, width=1900,scrollbars=yes,resizable=yes' );">Slave7 </a></th>
                                                                                                                <th><a onclick="window.open('queue_buildup_details.php?date=<?php //echo $crdate;?>&&flag=pace_buildup_g.txt','','height=1000, width=1900,scrollbars=yes,resizable=yes' );">Slave8</a></th>
                                                                                                        </tr>
                                                                                                </thead>
                                                                                                <!--<tbody style="font-size:15px;"><b><?php //$aplnMonior->status_queue(); ?></b></tbody>       -->
                                                                                                <!--<tbody class="queueappend">
                                                                                                        <?php //$aplnMonior->status_queue(); ?>
                                                                                                </tbody>
                                                                                        </table>
                                                                                </div>
                                                                        </div>
                                                                </div>
                                                                <div id="alldiv">
                                                                        <?php //include("monitorExternalFile.php");?>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </section>-->

                        <div id="alldiv">
                                <?php include("monitorExternalFile.php"); ?>
                        </div>
                </div>
                        <footer class="main-footer bg-navy-active">
                                <center><strong>Copyright &copy; 2016-<?php echo date("Y"); ?> Tata Consultancy Services LTD.</strong>&nbsp; All rights reserved.</center>
                        </footer>
                </div>
        </div>
</body>
        <script src="../js/jQuery-2.1.3.min.js?x=<?php echo rand(5555,9999); ?>"></script>
        <script src="../js/bootstrap.min.js?x=<?php echo rand(5555,9999); ?>" type="text/javascript"></script>
        <script src="../js/app.min.js?x=<?php echo rand(5555,9999); ?>" type="text/javascript"></script>
        <script src="../js/converter.js?x=<?php echo rand(5555,9999); ?>" type="text/javascript"></script>
        <?php if($ipofpc != '10.0.6.125'){?>
        <script src="../js/hideCode.js?x=<?php echo rand(5555,9999); ?>" type="text/javascript"></script>
        <?php }?>
        <script src="../js/darklight.js?x=<?php echo rand(5555,9999); ?>" type="text/javascript"></script>
        <script src="../js/duplicate.js" type="text/javascript"></script>
        <script src="enjoyhint/enjoyhint.min.js"></script>
        <script src="enjoyhint/index.js"></script>
        <script type="text/javascript">

        const canvas = document.getElementById("canvas");
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;
var ctx = canvas.getContext("2d");
function Firework(x,y,height,yVol,R,G,B){
  this.x = x;
  this.y = y;
  this.yVol = yVol;
  this.height = height;
  this.R = R;
  this.G = G;
  this.B = B;
  this.radius = 2;
  this.boom = false;
  var boomHeight = Math.floor(Math.random() * 200) + 50;
  this.draw = function(){

   ctx.fillStyle = "rgba(" + R + "," + G + "," + B + ")";
    ctx.strokeStyle = "rgba(" + R + "," + G + "," + B + ")";
    ctx.beginPath();
 //   ctx.arc(this.x,boomHeight,this.radius,Math.PI * 2,0,false);
    ctx.stroke();
    ctx.beginPath();
    ctx.arc(this.x,this.y,3,Math.PI * 2,0,false);
    ctx.fill();
  }
  this.update = function(){
    this.y -= this.yVol;
    if(this.radius < 20){
      this.radius += 0.35;
    }
    if(this.y < boomHeight){
      this.boom = true;

      for(var i = 0; i < 120; i++){
        particleArray.push(new Particle(
          this.x,
          this.y,
          // (Math.random() * 2) + 0.5//
          (Math.random() * 2) + 1,
          this.R,
          this.G,
          this.B,
          1,
        ))

      }
    }
    this.draw();
  }
  this.update()
}

window.addEventListener("click", (e)=>{
    var x = e.clientX;
  var y = canvas.height;
  var R = Math.floor(Math.random() * 255)
  var G = Math.floor(Math.random() * 255)
  var B = Math.floor(Math.random() * 255)
  var height = (Math.floor(Math.random() * 20)) + 10;
  fireworkArray.push(new Firework(x,y,height,5,R,G,B))
})

function Particle(x,y,radius,R,G,B,A){
  this.x = x;
  this.y = y;
  this.radius = radius;
  this.R = R;
  this.G = G;
  this.B = B;
  this.A = A;
  this.timer = 0;
  this.fade = false;

  // Change random spread
  this.xVol = (Math.random() * 10) - 4
  this.yVol = (Math.random() * 10) - 4


  //console.log(this.xVol,this.yVol)
  this.draw = function(){
 //   ctx.globalCompositeOperation = "lighter"
    ctx.fillStyle = "rgba(" + R + "," + G + "," + B + "," + this.A + ")";
    ctx.save();
    ctx.beginPath();
   // ctx.fillStyle = "white"
    ctx.globalCompositeOperation = "screen"
    ctx.arc(this.x,this.y,this.radius,Math.PI * 2,0,false);
    ctx.fill();

    ctx.restore();
  }
  this.update = function(){
    this.x += this.xVol;
    this.y += this.yVol;

    // Comment out to stop gravity.
    if(this.timer < 200){
        this.yVol += 0.12;
    }
    this.A -= 0.02;
    if(this.A < 0){
      this.fade = true;
    }
    this.draw();
  }
  this.update();
}

var fireworkArray = [];
var particleArray = [];
for(var i = 0; i < 6; i++){
  var x = Math.random() * canvas.width;
  var y = canvas.height;
  var R = Math.floor(Math.random() * 255)
  var G = Math.floor(Math.random() * 255)
  var B = Math.floor(Math.random() * 255)
  var height = (Math.floor(Math.random() * 20)) + 10;
  fireworkArray.push(new Firework(x,y,height,5,R,G,B))
}


function animate(){
  requestAnimationFrame(animate);
 // ctx.clearRect(0,0,canvas.width,canvas.height)
  ctx.fillStyle = "rgba(0,0,0,0.1)"
  ctx.fillRect(0,0,canvas.width,canvas.height);
  for(var i = 0; i < fireworkArray.length; i++){
    fireworkArray[i].update();
  }
  for(var j = 0; j < particleArray.length; j++){
    particleArray[j].update();
  }
  if(fireworkArray.length < 4){
      var x = Math.random() * canvas.width;
      var y = canvas.height;
      var height = Math.floor(Math.random() * 20);
      var yVol = 5;
      var R = Math.floor(Math.random() * 255);
      var G = Math.floor(Math.random() * 255);
      var B = Math.floor(Math.random() * 255);
      fireworkArray.push(new Firework(x,y,height,yVol,R,G,B));
  }


  fireworkArray = fireworkArray.filter(obj => !obj.boom);
  particleArray = particleArray.filter(obj => !obj.fade);
}
animate();

window.addEventListener("resize", (e) => {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
})



        $(document).ready(function () {
                        //$(".mainnav").hide();
                        //$(".mainnav").slideUp().slideDown().animate({opacity:0.5},"slow").animate({opacity:1},"slow");
                        $(".mainnav").fadeOut(2000).fadeIn(500).fadeOut(2000).fadeIn(500);
            if (window.IsDuplicate()) {
                swal ({
                                        title:"Oops !! Duplicate Tab !!",
                                        button:false,
                                        classname:"red-bg",
                                        text: "Window will automatically Redirect in 5 sec !!!\nSolution:\nKindly Close all windows including Incognito Tab\nIf Issue still persist please restart the PC",
                                        icon:"error",
                                        closeOnClickOutside:false,
                                });
                                window.setTimeout(function (){
                                   window.location.href='https://10.0.6.125:8080/under_construction/';
                                },5000)
            }
        });

                var myVar = setInterval('myTimer()',1000);
                function myTimer(){
                        var d = new Date();
                        document.getElementById("de").innerHTML = d.toLocaleTimeString();
                }
                function myFunction(){
                        document.getElementById('readmore').style.display='block';
                        document.getElementById('readButton').style.display='none';
                        document.getElementById('lessButton').style.display='block';
                }
                function myFunction1(){
                        document.getElementById('readmore').style.display='none';
                        document.getElementById('lessButton').style.display='none';
                        document.getElementById('readButton').style.display='block';
                }
                function myFunction2(){
                        document.getElementById('readmore1').style.display='block';
                        document.getElementById('readButton1').style.display='none';
                        document.getElementById('lessButton1').style.display='block';
                }
                function myFunction3(){
                        document.getElementById('readmore1').style.display='none';
                        document.getElementById('lessButton1').style.display='none';
                        document.getElementById('readButton1').style.display='block';
                }
                function pageRedirection(){
                        var data = document.getElementById('ll').value;
                        var date = data.replace(/-/g,"");
                        window.location.assign(window.location.origin+'/pace/Online_portal/pages/smart.php'+'?date='+date );
                }
                function show_all_data(){
                        var datadate = "<?php echo $crdate; ?>";
                        var data1 = 'Monitormain.php?date='+datadate;
                        $.ajax({
                                url:'Monitormain.php?date='+datadate,
                                success:function(d){
                                        $("#alldiv").html("");
                                        $("#alldiv").append(d);
                                        setCardsValues();
                                        //console.log("Loading MonitorFile");
                                }
                        })
                }
                setInterval('show_all_data()',20000);
                document.getElementById('timer').innerHTML =
                10 + ":" + 00;
                startTimer();

                function startTimer(){
                        var presentTime = document.getElementById('timer').innerHTML;
                        var timeArray = presentTime.split(/[:]+/);
                        var m = timeArray[0];
                        var s = checkSecond((timeArray[1] - 1),m);
                        if(s==59){m=m-1;}
                        if(m==0 && s==0){location.reload();}
                        document.getElementById('timer').innerHTML =
                        m + ":" + s;
                        setTimeout(startTimer, 1000);
                }
                function checkSecond(sec,m){
                        if (sec < 10 && sec >= 0) {sec = "0" + sec}; // add zero in front of numbers < 10
                        if (sec < 0 && m > 0) {sec = "59"};
                        return sec;
                }
                var i=1;
                function setCardsValues(){
                        //console.log(i++);
                        var newx = <?php echo CardDecider::getFinalDetails(); ?>;
                        console.log(newx);
                        var ars = ['master','slave1','slave2','slave3','slave4','slave5','slave6','slave7','slave8','slave9','slave10','slave11','sutlej','ganga','sharda','girija','jhelum','jamuna','bhargavi','mandovi','terna','purna','savitri','chenab'];
                        //var ars = ['master','slave1','slave2','slave3','slave4','slave5','slave6','slave7','slave8','M','S1','S2','S3','S4','S5','S6','S7','S8'];
                        $("#alldiv").find('table').each(function(i,v){
                                $(v).find("thead>tr:first").each(function(k,m){
                                        $(m).find("th").each( function(t,r){
                                                if ($.inArray($.trim($(r).text().toLowerCase()),ars) > -1 && $(v).find("thead>tr:first").find("th").length > 8) {
                                                        $(r).html($(r).html().replace($.trim($(r).text()),newx[t-1][0]));
                                                        $(r).attr("title",newx[t-1][2]+"/"+newx[t-1][4]);
                                                }
                                        });
                                })
                        })
                }
                //setCardsValues();
                /* jobrefresh--
                function setCardsValues(){
                        //console.log(i++);
                        var newx = <?php echo CardDecider::getFinalDetails(); ?>;
                        //console.log(newx);
                        var ars = ['master','slave1','slave2','slave3','slave4','slave5','slave6','slave7','slave8','sutlej','ganga','sharda','girija','jhelum','jamuna','bhargavi','mandovi','terna'];
                        //console.log('sdchjsdkhksdvh');
                        $(".table-responsive").find('table').each(function(i,v){
                                $(v).find("thead>tr:first").each(function(k,m){
                                        $(m).find("th").each( function(t,r){
                                                if ($.inArray($.trim($(r).text().toLowerCase()),ars) > -1 && $(v).find("thead>tr:first").find("th").length > 8) {
                                                        $(r).html($(r).html().replace($.trim($(r).text()),newx[t-1][0]));
                                                        $(r).attr("title",newx[t-1][2]+"/"+newx[t-1][4]);
                                                        //console.log($(r).attr("title",newx[t-1][2]+"/"+newx[t-1][4]));
                                                }
                                        });
                                })
                        })
                }*/

                $(document).ready(function(){
                        checkRegion();
                        setCardsValues();
                        /*var odd  = $('.totaltrickle>tbody>tr>td:odd').val();
                        var even = $('.totaltrickle>tbody>tr>td:even').val();
                        console.log(odd,even);*/

                        // ip address block
                                /*      $.ajax({
                                        type:"POST",
                                        url:"smart.php",
                                        data:{
                                                'ip':"<?php echo $_SERVER['REMOTE_ADDR'];?>"
                                        },
                                        success:function(data){
                                                var jsonData=JSON.parse(data);
                                                console.log(jsonData);
                                        }
                                });*/
                })

                function checkRegion(){
                        //console.clear();
                        var url=new URL(window.location.href);
                        var urldate=url.searchParams.get('date');
                        //console.log(urldate);
                        $.ajax({
                                type:"POST",
                                url:"checkregion.php",
                                data:"key=regionFile&urldate="+urldate,
                                success:function(region){
                                        //console.log(region);
                                        var tpsinfo=JSON.parse(region);
                                        //console.log(tpsinfo);
                                        var varspace=parseInt(tpsinfo[16]);
                                        if(varspace === 100){
                                                //console.log('Var Mountpoint Space In portal server Full');
                                                 swal ({
                                                        title:"Var Mountpoint Space In portal server Full !!",
                                                        button:false,
                                                        classname:"red-bg",
                                                        text: "Please Contact CDC To Clear The Mountpoint!!!",
                                                        icon:"error",
                                                        closeOnClickOutside:false,
                                                });
                                        }
                                        if(isNaN(parseInt(urldate)) && tpsinfo[0] == "N"){
                                                $(".tpscurr").html("TPS : "+tpsinfo[1]);
                                                $(".peaktps").html("Max TPS : "+tpsinfo[3]);
                                                if( tpsinfo[19] < 1921 ){
                                                        $(".channelmatrix").addClass("glow");
                                                }
                                                else{
                                                        $(".channelmatrix").removeClass("glow");
                                                }
                                                <?php
                                                if($DBdecider=='PR'){?>
                                                        //console.log('Night Region Activated');
                                                         swal ({
                                                                title:"Attention !! Night Region Activated !!",
                                                                button:false,
                                                                classname:"red-bg",
                                                                text: "Window will automatically Redirect To Night Region!!!",
                                                                icon:"info",
                                                                closeOnClickOutside:false,
                                                        });
                                                        window.setTimeout(function(){
                                                           window.location.href="eod.php";
                                                        },5000)
                                                <?php }else{?>
                                                        //console.log('Region Mode -> '+tpsinfo[0]+' and URL Date Not Present -> '+isNaN(parseInt(urldate))+ ' and Last Port Error '+tpsinfo[19]/60+' Mins ago');
                                                <?php }?>
                                        }
                                        else{
                                                $(".tpscurr").html("TPS : "+tpsinfo[1]+"|Max : "+tpsinfo[3]);
                                                $(".nrtps").html("<i class='fa fa-line-chart'></i>ALT TPS : "+tpsinfo[2]);
                                                //$(".peaktps").html("Peak TPS : "+tpsinfo[3]);
                                                $(".brancheslogged").text(tpsinfo[4]);
                                                $(".tellerlogged").text(tpsinfo[20]);
                                                //$(".loggapnr").html("LOG Gap(NR/DR):"+tpsinfo[6]+"/"+tpsinfo[7]);
                                                if((parseInt(tpsinfo[6]) > 10 )||(parseInt(tpsinfo[7]) > 10)){
                                                        $(".loggapnr").addClass("glow");
                                                        $(".loggapnr").html("LOG Gap(NR/DR):"+tpsinfo[6]+"/"+tpsinfo[7]);
                                                }
                                                else{
                                                        $(".loggapnr").removeClass("glow");
                                                        $(".loggapnr").html("LOG Gap(NR/DR):"+tpsinfo[6]+"/"+tpsinfo[7]);
                                                }
                                                if(tpsinfo[5]=='1'){
                                                        $(".imageDB").addClass("glow")
                                                        $(".imageDB").html("ImageDB <i class='fa fa-thumbs-down'></i></span>").css("background-color","red");
                                                }
                                                else if(tpsinfo[5]=='0'){
                                                        $(".imageDB").removeClass("glow")
                                                        $(".imageDB").html("ImageDB  <i class='fa fa-thumbs-up'></i>").css("background-color","green");
                                                }
                                                if((parseInt(tpsinfo[8])> 0)||(parseInt(tpsinfo[9])> 0)){
                                                        $(".neftinvalid").addClass("glow");
                                                        $(".neftinvalid").html("NEFT Invalid (D/N): "+tpsinfo[8]+"/"+tpsinfo[9]).css("background-color","red");
                                                }
                                                else{
                                                        $(".neftinvalid").removeClass("glow");
                                                        $(".neftinvalid").html("NEFT Invalid (D/N): "+tpsinfo[8]+"/"+tpsinfo[9]).css("background-color","#db8b0b");
                                                }
                                                if(tpsinfo[10]=='NR'){
                                                        $(".class_showerfeed").addClass("glow");
                                                        $(".class_showerfeed").html("Shfeed Status: "+tpsinfo[10]+" <i class='fa fa-thumbs-down'></i></span>").css("background-color","red");
                                                }
                                                else if(tpsinfo[13] > 0){
                                                        $(".class_showerfeed").addClass("glow");
                                                        $(".class_showerfeed").html("Shfeed "+"SQL Error : "+tpsinfo[13]+" <i class='fa fa-thumbs-down'></i></span>").css("background-color","red");
                                                }
                                                else if(tpsinfo[11] < 1800){
                                                        $(".class_showerfeed").removeClass("glow");
                                                        $(".class_showerfeed").html("Shfeed  <i class='fa fa-thumbs-up'></i>").css("background-color","green");
                                                }
                                                else{
                                                        $(".class_showerfeed").removeClass("glow");
                                                        $(".class_showerfeed").html("Shfeed  <i class='fa fa-warning'></i>").css("background-color","#ff6700");
                                                        $(".class_showerfeed").attr("title","File Not updating for "+tpsinfo[11]+" secs");
                                                }
                                                if(tpsinfo[12] > 0){
                                                                $(".repostfail").addClass("glow");
                                                                $(".repostfail").html("Repost Fail :"+tpsinfo[12]).css("background-color","red");
                                                }
                                                else if(tpsinfo[12] == 0){
                                                        $(".repostfail").removeClass("glow");
                                                        $(".repostfail").html("Repost Fail :"+tpsinfo[12]).css("background-color","green");
                                                }
                                                if(tpsinfo[17]==true){
                                                        $(".mrconnection").html("UPI(MR) <i class='fa fa-thumbs-down'></i></span>").css("background-color","orange");
                                                        $(".mrconnection").addClass("glow");
                                                        //$('.mrconnection').attr("href","mr_connection.php");
                                                        $('.mrconnection').attr("title","MR Max Connection limit greater than Zero");
                                                }
                                                else if(tpsinfo[17] == false){
                                                        $(".mrconnection").removeClass("glow");
                                                        $(".mrconnection").html("UPI(MR)  <i class='fa fa-thumbs-up'></i>").css("background-color","green");
                                                        //$('.mrconnection').attr("href","mr_connection.php");
                                                        $('.mrconnection').attr("title","MR Max Connection limit is Proper");
                                                }
                                                if( tpsinfo[19] < 1200 ){
                                                        $(".channelmatrix").addClass("glow");
                                                }
                                                else{
                                                        $(".channelmatrix").removeClass("glow");
                                                }
                                                //console.log(tpsinfo[12]);
                                                //console.log('Region Mode -> '+tpsinfo[0]+' and URL Date Not Present -> '+isNaN(parseInt(urldate))+ ' and Last Port Error '+tpsinfo[19]/60+' Mins ago');
                                        }
                                        if(tpsinfo[18]!='OK'){
                                                $(".sqlconnection").html("SQL 9apps Queryi user : "+tpsinfo[18]).css("background-color","white").addClass("glow");
                                        }
                                        else{
                                                $(".sqlconnection").html("SQL 9apps Queryi user : <i class='fa fa-thumbs-up'></i>");
                                        }
                                        if( tpsinfo[21]=='kcusageglow' ){
                                                $(".kcusage").addClass("glow");
                                                $('.kcusage').attr("title","Kcusage parameters are crossing the Threshold...!!Please Check!!");
                                        }
                                        else{
                                                $(".kcusage").removeClass("glow");
                                        }
                                        console.log(tpsinfo[12])
                                        if( parseInt(tpsinfo[23]) > 0 ){
                                                $(".rtgsincominggateway").addClass("glow");
                                                $('.rtgsincominggateway').attr("title","RTGS Log Incoming Error...!!Please Check!!");

                                        }
                                        else{
                                                $(".rtgsincominggateway").removeClass("glow");
                                                //$('.rtgsincominggateway').css("background-color","red");
                                        }

                                        if( tpsinfo[22] == 'glowsms' ){
                                                $(".smsne_class").addClass("glow");
                                                $('.smsne_class').attr("title","ERROR!!! Please check");

                                        }
                                        else{
                                                $(".smsne_class").removeClass("glow");
                                        }
                                        if( tpsinfo[24] > 0  ){
                                                $(".sms_cbs_ne").addClass("glow");
                                        }
                                        else{
                                                $(".sms_cbs_ne").removeClass("glow");
                                        }
                                        if( tpsinfo[25] != 0  ){
                                                $(".MREN_query").addClass("glow");
                                        }
                                        else{
                                                $(".MREN_query").removeClass("glow");
                                        }
                                        if( tpsinfo[26] != 0 ){
                                                $(".rtgsincomingack").addClass("glow");
                                                $('.rtgsincomingack').attr("title","RTGS Log Incoming ACK Error...!!Please Check!!");
                                                //$('.rtgsincomingack').css("background-color","red");
                                        }
                                        else{
                                                $(".rtgsincomingack").removeClass("glow");
                                                $('.rtgsincomingack').css("background-color","red");
                                        }
                                        //console.log(tpsinfo[12])
                                }
                        })
                }

                setInterval('checkRegion()',10);

                function checkfinance(){
                        //console.clear();
                        //console.log(urldate);
                        $.ajax({
                                type:"POST",
                                url:"financestatus.php",
                                data:"key=checkfinance",
                                success:function(d){
                                        var jsonreturned=JSON.parse(d);
                                        //console.log(jsonreturned);
                                        if(parseInt(jsonreturned[1])==10 || (jsonreturned[0]==true)){
                                                //console.log("inside if");
                                                $('.financeoneglow').addClass("glow");
                                        }
                                        else{
                                                //console.log("inside else ");
                                                $('.financeoneglow').removeClass("glow");
                                        }
                                }
                        })
                }
                function checkmonitor(){
                        $.ajax({
                                type:"POST",
                                url:"monitor1file_status.php",
                                data:"key=monitorfile_status",
                                success:function(d){
                                        var jsonarrayfiles=JSON.parse(d);
                                        //console.log("enetr"+jsonarrayfiles);
                                        if(jsonarrayfiles['NU'] == 0 && jsonarrayfiles['MISSING']==0){
                                                $('.monitorfilestatus').removeClass("glow");
                                                $('.monitorfilestatus').html("<i class='fa fa-check'></i> All files Proper");
                                                $('.monitorfilestatus').attr("title","All Monitoring Portal FILES are Proper");
                                                $('.monitorfilestatus').addClass("btn-success");
                                        }
                                        else if (jsonarrayfiles['MISSING'] > 0){
                                                //$('.monitordisplay').css("display","block");
                                                $('.monitorfilestatus').addClass("glow");
                                                $('.monitorfilestatus').html("<i class='fa fa-exclamation-triangle'></i> "+(jsonarrayfiles['MISSING'])+" Files Missing");
                                                $('.monitorfilestatus').attr("title","MISSING FILES IN Monitoring Portal");
                                                $('.monitorfilestatus').attr("href","filenot.php");
                                                $('.monitorfilestatus').addClass("btn-danger");
                                        }
                                        else if(jsonarrayfiles['NU'] > 0){
                                                $('.monitorfilestatus').removeClass("glow");
                                                $('.monitorfilestatus').html("<i class='fa fa-exclamation-triangle'></i> "+(jsonarrayfiles['NU'])+" Files Not Updating");
                                                $('.monitorfilestatus').addClass("btn-warning");
                                                $('.monitorfilestatus').attr("title","Not Updating FILES IN Monitoring Portal");
                                                $('.monitorfilestatus').attr("href","filenot.php");
                                        }
                                }
                        })
                }
                setInterval('checkfinance()',10000);
                checkmonitor();

                const checkTxnLogAlert = () => {
                        $.get( "/pace/Online_portal/pages/channel_txn_log_alert.php", function( data ) {
                                //channel-txn-log-btn
                                if(data == 2){
                                        $("#channel-txn-log-btn").css("background-color","red");
                                        $("#channel-txn-log-btn").addClass("glow");
                                }else if(data == 1){
                                        $("#channel-txn-log-btn").css("background-color","orange");
                                        $("#channel-txn-log-btn").removeClass("glow");
                                }else if(data == 0){
                                        $("#channel-txn-log-btn").css("background-color","green");
                                        $("#channel-txn-log-btn").removeClass("glow");
                                }
                        });
                }
                setInterval('checkTxnLogAlert()',10000);

                function jobsrefresh(key,dataclassname,dateclassname,dataclassnamequeue,dateclassnamequeue){
                        //console.clear();
                        var url=new URL(window.location.href);
                        var urldate=url.searchParams.get('date');
                        //console.log("jobsrefresh running");
                        $.ajax({
                                type:"POST",
                                url:"jobs.php",
                                data:"key="+key+"&urldate="+urldate,
                                success:function(region){
                                        var jobsinfo=JSON.parse(region);
                                        //console.log(jobsinfo);
                                        $("."+dataclassname).html(jobsinfo[0]);
                                        $("."+dateclassname).html('<img src="loading-bubbles.svg">'+jobsinfo[1]);
                                        $("."+dataclassnamequeue).html(jobsinfo[2]);
                                        $("."+dateclassnamequeue).html('<img src="loading-bubbles.svg">'+jobsinfo[3]);
                                        //console.log("donejob refresh");
                                }
                        })
                }
                //setInterval("jobsrefresh('jobsqueue','jobsappend','jobstimepop','queueappend','queuetimepop')",5000);
                //setInterval("jobsrefresh('queue','queueappend','queuetimepop')",5000);

                window.onscroll = function(){
                        myfunction() ;
                }
                var header = document.getElementById("myheader");
                //console.log(header);
                var sticky = header.offsetTop;
                function myfunction(){
                        if(window.pageYOffset > sticky ){
                                header.classList.add("sticky");
                        }
                        else{
                                header.classList.remove("sticky");
                        }
                }
                //table autoscroll
                /*var $el = $(".table-autoscroll");
                function anim(){
                        var st = $el.scrollTop();
                        var sb = $el.prop("scrollHeight")-$el.innerHeight();
                        $el.animate({scrollTop: st<sb/2 ? sb : 0}, 10000, anim);
                }
                function stop(){
                        $el.stop();
                }
                anim();
                $el.hover(stop, anim);*/

                $.getScript("../js/notification.js",function(){});
                function notifyMe(){
                        //console.clear();
                        $("body").each(function (){
                                $.ajax({
                                        type:"POST",
                                        url:"getnoti.php",
                                        data:{
                                                'ip':"<?php echo $_SERVER['REMOTE_ADDR'];?>/smart/"
                                        },
                                        success:function(data){
                                                //console.log("notifyme");
                                                var jsonData=JSON.parse(data);
                                                //console.log(jsonData);
                                                $(jsonData).each(function(i,v){
                                                        Push.create("Smart Monitoring Alerts", {
                                                                body: v+" !!!",
                                                                icon: '../img/bolt.JPG',
                                                                timeout: 10000,
                                                                onClick: function () {
                                                                        window.focus();
                                                                        this.close();
                                                                }
                                                        })
                                                })
                                        }
                                });
                        });
                }
                window.setInterval("notifyMe()",120000);
                $("#clear-button").click(function(){
                   Push.clear();
                });
        </script>
        <?php ( !in_array(date("dm"),array("3103","0104"))) ?  include("modal_graphs1.php") : "" ;?>
</html>
<?php
        /*}
        else {
                $err="Un-Authenticated Access Try Again!!";
                header("location:../index.php?status=err=$err");
        }*/
