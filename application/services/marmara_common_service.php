<?php
  class MarmaraCommonService {
      
      public static  function getConfigValue($key) {
        $query = "SELECT * FROM marmara_genelayar WHERE gayar_anahtar='$key'";
        $queryExecutor = new QueryExecutor();
        $resultRow = $queryExecutor->query($query, TRUE, 3600*24);
        foreach($resultRow as $row){
            return $row->gayar_deger;
        }
        return FALSE;
      }
     
      
      
  }
 

?>
