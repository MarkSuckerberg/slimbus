<?php
ini_set('session.gc_maxlifetime', 432000);
ini_set('session.cookie_lifetime', 432000);
ini_set('session.use_strict_mode', 1);

$secure = true;
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
  $secure = false;
}
session_set_cookie_params(432000, '/stats', parse_url($_SERVER['REQUEST_URI'], PHP_URL_HOST), $secure, true);

start_statbus_session();

function start_statbus_session()
{
  $options = [];
  if (!getenv('DEBUG')) {
    $options = [
      'cookie_httponly' => true,
      'cookie_secure' => (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')
    ];
  }

  session_start($options);
}

function destroy_statbus_session()
{
  session_destroy();
  start_statbus_session();
}

function regenerate_statbus_session()
{
  $_SESSION['deleted'] = time();

  session_regenerate_id();

  unset($_SESSION['deleted']);
  $_SESSION['canary'] = time();

  /* Unused until I figure out why this doesn't work

  $new_id = session_create_id();
  $_SESSION['new_session_id'] = $new_id;
  $_SESSION['deleted'] = time();

  session_commit();

  _set_statbus_session_id($new_id);

  unset($_SESSION['new_session_id']);
  unset($_SESSION['deleted']);

  $_SESSION['canary'] = time();
  */
}

function _set_statbus_session_id($id)
{
  session_commit();

  //Strict mode off to allow us to set the session ID
  ini_set('session.use_strict_mode', 0);
  session_id($id);
  start_statbus_session();

  check_statbus_session_valid();
}

function check_statbus_session_valid()
{
  if (isset($_SESSION['deleted']) && $_SESSION['deleted'] < time()) {
    error_log('Possible session hijack detected by ' . $_SERVER['REMOTE_ADDR']);
    destroy_statbus_session();
    return;
  }

  /*if (isset($_SESSION['new_session_id'])) {
    _set_statbus_session_id($_SESSION['new_session_id']);
    return;
  }*/

  $time = time();

  //Set session expiry to five days
  $timeout_duration = 432000;
  if (
    isset($_SESSION['LAST_ACTIVITY']) &&
    ($_SESSION['LAST_ACTIVITY'] < $time - $timeout_duration)
  ) {
    destroy_statbus_session();
    return;
  }

  $_SESSION['LAST_ACTIVITY'] = $time;

  // Make sure we have a canary set, and regenerate every five minutes
  if (!isset($_SESSION['canary']) || ($_SESSION['canary'] < $time - 300)) {
    regenerate_statbus_session();
    return;
  }

}
