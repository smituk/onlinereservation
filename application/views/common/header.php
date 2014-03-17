<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"[]>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xml:lang="en">
    <head>    
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />    
        <title>Ankafly</title>    
        <link rel="stylesheet" href="<?php echo base_url("static/css/style.css"); ?>" type="text/css" />
        <link rel="stylesheet" href="<?php echo base_url("static/css/slide_style.css"); ?>" type="text/css" />         
        <link rel="stylesheet" href="<?php echo base_url("static/js/jquery-ui/development-bundle/themes/base/jquery.ui.all.css"); ?>" />         
        <link rel="stylesheet" href="<?php echo base_url("static/css/jquery_demos.css"); ?>" />    
        <!--============================--> 
        <script src="<?php echo base_url("static/js/jquery-ui/development-bundle/jquery-1.9.0.js"); ?>"></script> 	
        <script src="<?php echo base_url("static/js/jquery-ui/development-bundle/ui/jquery.ui.core.js"); ?>"></script>     
        <script src="<?php echo base_url("static/js/jquery-ui/development-bundle/ui/jquery-ui.js"); ?>"></script> 	
        <script src="<?php echo base_url("static/js/jquery-ui/development-bundle/ui/jquery.ui.widget.js"); ?>"></script> 	
        <script src="<?php echo base_url("static/js/jquery-ui/development-bundle/ui/jquery.ui.tabs.js"); ?>"></script> 	
        <script src="<?php echo base_url("static/js/jquery-ui/development-bundle/ui/jquery.ui.datepicker.js"); ?>"></script>     
        <script src="<?php echo base_url("static/js/jquery.atooltip.js"); ?>"></script>     
        <script src="<?php echo base_url("static/js/jquery.atooltip.min.js"); ?>"></script>
        <!--============================--> 
        <script type="text/javascript">var _siteRoot='index.html',_root='index.html';</script>
        <script type="text/javascript" src="<?php echo base_url("static/js/slide/slide_jquery.js"); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url("static/js/slide/slide_scripts.js"); ?>"></script>


        <script>
            $(function() {
                $( "#from" ).datepicker({
                    defaultDate: "+2w",
                    changeMonth: true,
                    showWeek: true,
                    numberOfMonths: 2,
                    onClose: function( selectedDate ) {
                        $( "#to" ).datepicker( "option", "minDate", selectedDate  );
                    }
                });
                $( "#to" ).datepicker({
                    defaultDate: "+2w",
                    changeMonth: true,
                    showWeek: true,
                    numberOfMonths: 2,
                    onClose: function( selectedDate ) {
                        $( "#from" ).datepicker( "option", "maxDate", selectedDate );
                    }
                });
            });
        </script>
        <script>
            $(function() {
                $( "#tabs" ).tabs({
                    collapsible: true
                });
            });
        </script>

        <!--[if IE 6]><link rel="stylesheet" href="<?php echo base_url("static/style.ie6.css"); ?>" type="text/css" media="screen" /><![endif]-->    
        <!--[if IE 7]><link rel="stylesheet" href="<?php echo base_url("static/css/style.ie7.css"); ?>" type="text/css" media="screen" /><![endif]-->
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    </head>
    <body>