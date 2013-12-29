<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of xml_transformer
 *
 * @author pasa
 */
interface XmlTransformer {
    public function prepareXml();
    public function convertObject($responseXml , $isConverted = FALSE);
}

?>
