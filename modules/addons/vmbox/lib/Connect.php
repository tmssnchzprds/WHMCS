<?php

require_once __DIR__ . '/Log.php';
require_once __DIR__ . './../Config.php';

function VMBOX__PostData($url, $data)
{
  $config = VMBOX__GetConfigData();
  $ch = curl_init();
  curl_setopt_array($ch, array(
    CURLOPT_URL => $url,
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_HTTPHEADER => array(
      'Content-type: application/x-www-form-urlencoded',
      'Cookie: token=' . $config["VMBOX_TOKEN"],
    ),
  ));

  if($config['SSL_ENABLED']){
    curl_setopt_array($ch, array(
      CURLOPT_SSL_VERIFYPEER => TRUE,
      CURLOPT_SSL_VERIFYHOST => 2,
      CURLOPT_CAINFO => __DIR__."/../ssl/" . $config['SSL_FileName'],
    ));
  } else {
    curl_setopt_array($ch, array(
      CURLOPT_SSL_VERIFYPEER => FALSE,
      CURLOPT_SSL_VERIFYHOST => 0,
    ));
  }
  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    $result = curl_error($ch);
  }
  curl_close($ch);
  return $result;
}

function VMBOX__GetAllPlans()
{
  $result = VMBOX__PostData(VMBOX__GetConfigData()["VMBOX_URL"] . "/getAllPlans", array());
  return $result;
}