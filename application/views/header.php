<?php $appName = "flySearhPageApp"; ?>
<?php define("PAGE_NAME","fly_search" )?> 
<!DOCTYPE html>
<html lang="en" xmlns:ng="http://angularjs.org" id="ng-app" ng-app="<?php echo $appName; ?>">
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>

       <!--[if lt IE 8]>
        <script>
          document.createElement('ng-include');
          document.createElement('ng-pluralize');
          document.createElement('ng-view');
 
          // Optionally these for CSS
          document.createElement('ng:include');
          document.createElement('ng:pluralize');
          document.createElement('ng:view');
        </script>
       <![endif]-->
       
        <!--[if lt IE 9]>
          <link href="//netdna.bootstrapcdn.com/respond-proxy.html" id="respond-proxy" rel="respond-proxy" />
          <link href="<?php echo base_url();?>online_reservation_public/app/lib/respond.proxy.gif" id="respond-redirect" rel="respond-redirect" /> 
          <script src="<?php echo base_url();?>online_reservation_public/app/lib/html5shiv.js" type="text/javascript"></script>
          <script src="<?php echo base_url();?>online_reservation_public/app/lib/respond.js" type="text/javascript"></script>
        
          <script src="<?php echo base_url();?>online_reservation_public/app/lib/respond.proxy.js" type="text/javascript"></script>
         
        <![endif]-->
            <title>My AngularJS App</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootswatch/3.1.1/cerulean/bootstrap.min.css"/>
 
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet"/>
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/3.4.5/select2.css" rel="stylesheet"/>
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/3.4.5/select2-bootstrap.css" rel="stylesheet"/>
    <link href="<?php echo base_url();?>online_reservation_public/app/css/common/common.css" rel="stylesheet"/>
    <link href="<?php echo base_url();?>online_reservation_public/app/css/common/logo.css" rel="stylesheet"/>
    <link href="<?php echo base_url();?>online_reservation_public/app/css/<?php echo PAGE_NAME;?>/<?php echo PAGE_NAME;?>.css" rel="stylesheet"/>
</head>
<body>
<script>
    var APP_NAME = "<?php echo $appName; ?>";
</script>  

<div class="container top-container" ng-controller="topHeaderController">
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6" style=""> <div class="ucuzuc-sprite ucuzuc_eu company-logo-container"></div> </div>
        <div class="col-xs-12 col-sm-6 col-md-6 " >
            <div class="row">
                <div class="col-xs-3 col-sm-3 col-md-3">
                    <div class="login-icon"> 
                        <span  class="ucuzuc-sprite header-yeni-uyelik"></span ><span class='login-icon-text' style="display: inline-block;">Yeni Üyelik</span> 
                    </div>
                </div>
                <div class="col-xs-3  col-sm-3 col-md-3">
                   <div class="login-icon">
                       <span  class="ucuzuc-sprite header-uye-girisi"></span><span class='login-icon-text' style="display: inline-block;">Üye girişi</span>
                   </div>
                 </div>
                <div class="col-xs-3  col-sm-4 col-md-4">
                     <div class='login-icon'>
                        <span class='icon-phone ucuzuc-sprite header-tel'></span><span class='icon-phone-text'>0900 - 11 11</span>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-4'></div>
                <div class='col-md-8'>
                   
                </div>
            </div>
        </div>
    </div>
    
</div>

<nav class="navbar navbar-default " role="navigation" ng-controller="navBarController">
    <div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#"></a>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
         <ul class="nav navbar-nav">
        <li><a href="#">Anasayfa</a></li>
        <li><a href="#" ng-click="focusReservation();">Rezervasyon</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Kurumsal <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="#">Action</a></li>
            <li><a href="#">Another action</a></li>
            <li><a href="#">Something else here</a></li>
            <li class="divider"></li>
            <li><a href="#">Separated link</a></li>
            <li class="divider"></li>
            <li><a href="#">One more separated link</a></li>
          </ul>
        </li>
         <li><a href="#">Bize Ulaşın</a></li>
      </ul>
     
        
    </div>
    
    </div> 

</nav>