


<!--<script type ="text/javascript" src="<?php echo base_url("onlinefly/public_html/js/libs/jquery-1.9.0//jquery.min.js")?>"></script>
-->
  <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
  <script src="http://code.jquery.com/ui/1.10.1/jquery-ui.js"></script>
  <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery.selectboxit/3.2.0/jquery.selectBoxIt.min.js"></script>
  <script src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.1/underscore-min.js"></script>
  <script src="http://cdnjs.cloudflare.com/ajax/libs/backbone.js/0.9.9/backbone-min.js"></script>
  <?php
//  gerekli scriptleri burdan ekliyoz.
if(isset($js)){
   
    foreach ($js as $js_file_name){
      echo "<script type='text/javascript'
          src='".base_url("onlinefly/public_html/js/".$js_file_name)."'></script>"; 
     }
}
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
</body>
</html>