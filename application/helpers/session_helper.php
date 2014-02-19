<?php

class Session {

    // To permit the same session var being accessed
    // more than once at same time on different places.
    var $prefix;
    var $activityTime = 600;
    function Session($life_time = null) {
        if (!isset($_SESSION)) {
            session_start();
        }
        $this->prefix = $_SERVER['HTTP_HOST'];

        if ($this->get('flash')) {
            foreach ($_SESSION[$this->prefix]['flash'] as $name => $vals) {
                ++$_SESSION[$this->prefix]['flash'][$name]['counter'];
                if ($_SESSION[$this->prefix]['flash'][$name]['counter'] > 1) {
                    unset($_SESSION[$this->prefix]['flash'][$name]);
                }
            }
        }
        if (isset($life_time)) {
            $this->activityTime = $life_time;
           // ini_set("session.gc_maxlifetime", $life_time);
            //ini_set("session.cookie_lifetime", $life_time);
        }
    }

    function get($session_var , $activityTime = null) {
        
        if(!isset($activityTime)){
            $activityTime = $this->activityTime;
        }
        if(!$this->isActivityValid($session_var, $activityTime)){
            unset($_SESSION[$this->prefix][$session_var]);
            return FALSE;
        }
        return ((isset($_SESSION[$this->prefix][$session_var])) ? unserialize($_SESSION[$this->prefix][$session_var]) : false);
    }

    function get_cookie($cookie_name) {
        return ((isset($_COOKIE[$cookie_name])) ? $_COOKIE[$cookie_name] : false);
    }

    function set($session_var, $value , $activityTime = null) {
        
        $_SESSION[$this->prefix][$session_var] = serialize($value);
        $this->logActivity($session_var);
    }

    function set_cookie($cookie_name, $value) {
        setcookie($cookie_name, $value, time() + 3600 * 24, '/');
    }

    function del() {
        $session_vars = func_get_args();
        foreach ($session_vars as $session_var) {
            if ($this->get($session_var) || is_array($this->get($session_var))) {
                unset($_SESSION[$this->prefix][$session_var]);
            }
        }
    }

    function del_cookie() {
        $cookies = func_get_args();
        foreach ($cookies as $cookie) {
            if ($this->get_cookie($cookie)) {
                setcookie($cookie, 'del', time() - 3600, '/');
            }
        }
    }

    function flash($flash_var, $value) {
        $_SESSION[$this->prefix]['flash'][$flash_var] = array('val' => $value, 'counter' => 0);
    }

    function get_flash($flash_var) {
        return ((isset($_SESSION[$this->prefix]['flash'][$flash_var]['val'])) ? $_SESSION[$this->prefix]['flash'][$flash_var]['val'] : false);
    }
    
    function isActivityValid($key, $activityTime = 600){
        
      if (isset($_SESSION[$key."-lastActivity"])) {
            if(($_SESSION[$key.'-lastActivity'] + $activityTime) < time()) {
                return false;
            }
          return true;
      }  
      
      return false;
            
    }
    function logActivity($key){
        $_SESSION[$key."-lastActivity"] = time();
    }

}

?>
