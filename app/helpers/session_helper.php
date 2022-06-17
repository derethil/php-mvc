<?php

session_start();

// Flash messages
// EXAMPLE - flash('register_success', 'You are now registered')
// DISPLAY IN VIEW - echo flash('register_success');
function flash($name = '', $message = '', $class = 'alert alert-success')
{
  if (!empty($name)) {
    $classKey = $name . '_class';

    // Handle creating a flash message to pass to new page
    if (!empty($message) && empty($_SESSION[$name])) {

      if (!empty($_SESSION[$name])) unset($_SESSION[$name]);
      if (!empty($_SESSION[$classKey])) unset($_SESSION[$classKey]);

      $_SESSION[$name] = $message;
      $_SESSION[$classKey] = $class;

      // Handle displaying a flash message set from previous page
    } else if (empty($message) && !empty($_SESSION[$name])) {
      $class = !empty($_SESSION[$classKey]) ? $_SESSION[$classKey] : '';
      echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';

      unset($_SESSION[$name]);
      unset($_SESSION[$classKey]);
    }
  }
}
