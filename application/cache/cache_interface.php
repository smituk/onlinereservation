<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author pasa
 */
interface CacheInterface {
   public  function  get($key);
   public  function  set($key, $value , $expiretime = 0);
   public  function  getServerStatu();
    
}
?>