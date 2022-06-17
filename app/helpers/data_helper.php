<?php

function checkEmpty($data, $key, $err = '')
{
  if (empty($data[$key])) {
    $err = empty($err) ? 'Please enter ' . $key : $err;
    $data[$key . '_err'] = $err;
  }
  return $data;
}
