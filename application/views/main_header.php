<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
    <title></title>
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url("onlinefly/public_html/css/ui-lightness/jquery-ui-1.9.2.custom.css") ?>" />

    <!-- Bootstrap -->
    <link href="<?php echo base_url("onlinefly/public_html/css/bootstrap.min.css") ?>"  rel="stylesheet">
        <link href="<?php echo base_url("onlinefly/public_html/css/bootstrap-responsive.min.css") ?>"  rel="stylesheet">
            <link href="<?php echo base_url("onlinefly/public_html/css/font-awesome-3.0.0/font-awesome.min.css") ?>"  rel="stylesheet">
                <link type="text/css" rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/jquery.selectboxit/3.2.0/jquery.selectBoxIt.css" />
                <link href="<?php echo base_url("onlinefly/public_html/css/select2/select2.css") ?>"  rel="stylesheet">
                <link href="<?php echo base_url("onlinefly/public_html/css/modal/component.css") ?> " rel="stylesheet">  
                    <!--[if lt IE 9]>
                      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
                    <![endif]-->

                    <?php
                    if (isset($css)) {
                       
                        foreach ($css as $cs_file_name) {
                            echo "<link href='" . base_url("onlinefly/public_html/css/" . $cs_file_name) . "' rel = 'stylesheet' >";
                        }
                    }
                    ?>

                    <style>
                       
                    </style>
                    </head>
                    <body>



                        <?php
                        /*
                         * To change this template, choose Tools | Templates
                         * and open the template in the editor.
                         */
                        ?>
