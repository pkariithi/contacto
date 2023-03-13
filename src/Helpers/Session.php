<?php

namespace SMVC\Helpers;

use SMVC\Exceptions\SMVCException;

class Session {

  private $prefix;

  /**
   * start a session if not already started
   * @param String $prefix - The session prefix
   */
  public function __construct($prefix = null) {
    if(session_status() == PHP_SESSION_NONE) {

      $this->prefix = $prefix;

      ini_set('session.gc_maxlifetime', MAX_SESSION_LIFETIME);
      session_set_cookie_params(MAX_SESSION_LIFETIME);

      session_start();
    }
  }

  /**
   * cheks if a certain key is set in the session
   * @param  String $key - the key
   * @return boolean true if set, false otherwise
   */
  public function has($key) {
    if(empty($key)) {
      throw new SMVCException(__FILE__, __LINE__, "You cannot use an empty session key '{$key}'");
    }

    return isset($_SESSION[$this->prefix.$key]);
  }

  /**
   * sets a value or values to session
   * @param String|Array $key - the session key or a key:value array of the data
   * @param String $value - the session value if key is string
   * @return void
   */
  public function set($key, $value = null) {
    if(empty($key)) {
      throw new SMVCException(__FILE__, __LINE__, "You cannot use an empty session key '{$key}'");
    }

    if(is_array($key)) {
      foreach($key as $name => $val) {
        $_SESSION[$this->prefix.$name] = $val;
      }
    } else {
      $_SESSION[$this->prefix.$key] = $value;
    }
  }

  /**
   * gets a session by key
   * @param String $key - the session key
   * @return session value or false
   */
  public function get($key = null) {
    if(empty($key)) {
      throw new SMVCException(__FILE__, __LINE__, "You cannot use an empty session key '{$key}'");
    }

    if(isset($_SESSION[$this->prefix.$key])) {
      return $_SESSION[$this->prefix.$key];
    }
    return false;
  }

  /**
   * gets and deletes a session by key
   * @param String $key - the session key
   * @return session value or false
   */
  public function pull($key = null) {
    if(empty($key)) {
      throw new SMVCException(__FILE__, __LINE__, "You cannot use an empty session key '{$key}'");
    }

    if(isset($_SESSION[$this->prefix.$key])) {
      $value = $_SESSION[$this->prefix.$key];
      unset($_SESSION[$this->prefix.$key]);
      return $value;
    }
    return false;
  }

  /**
   * deletes a session by key
   * @param String $key - the session key
   * @return boolean, true if deleted, false otherwise
   */
  public function delete($key = null, $subkey = null) {
    if(empty($key)) {
      throw new SMVCException(__FILE__, __LINE__, "You cannot use an empty session key '{$key}'");
    }

    if(is_null($subkey)) {
      unset($_SESSION[$this->prefix.$key]);
    } else {
      unset($_SESSION[$this->prefix.$key][$subkey]);
    }
    return !$this->has($key);
  }

  /**
   * get and return the session id
   * @return String - the session id
   */
  public function id() {
    return session_id();
  }

  /**
   * regenerate the session id
   * @var deleteOldSession - boolean to delete or retain the old session
   * @return String - the new session id
   */
  public function regenerate($deleteOldSession = false) {
    session_regenerate_id($deleteOldSession);
    return $this->id();
  }

  /**
   * completely destroy and empty the session
   * @return void
   */
  public function destroy() {
    if(session_status() != PHP_SESSION_NONE) {
      $this->prefix = null;
      $this->regenerate(true);
      session_destroy();
      $_SESSION = [];
    }
  }

}
