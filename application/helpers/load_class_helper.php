<?php
   
function loadClass($classDirectory = null){
      if($classDirectory != null){
          include_once $classDirectory;
      }
}

?>

