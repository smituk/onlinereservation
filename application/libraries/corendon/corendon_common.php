<?php

  include_once 'corendon_account.php';
  class CorendonCommon {
      const APICODE = "CRNDN";


      public static function  buildAgentXML(SimpleXMLElement $requetsEXML){
          $agentsXML = $requetsEXML->addChild("AGENTS");
          $agentSrcXML = $agentsXML->addChild("AGENT_STRC");
          $agentSrcXML->addChild("AGENT_ID" , CorendonAccount::getUsername());
          $agentSrcXML->addChild("AGENT_PWD" , CorendonAccount::getPassword());
      }
  }

?>

