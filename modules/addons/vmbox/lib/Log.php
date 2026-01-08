<?php

require_once __DIR__ . './../Config.php';

function VMBOX__LogData($level, $data)
{
  VMBOX__LogText($level, json_encode($data)."\n----------------------------------------------------------\n");
}

function VMBOX__LogError($e)
{
  VMBOX__LogData(1, array(
    "errorMessage" => $e->getMessage(),
    "trace" => $e->getTraceAsString()
  ));
}

function VMBOX__LogText($level, $Message)
{
  $confLevel = VMBOX__GetConfigData()["Log_Level"];
  if($confLevel != 0 && $level <= $confLevel) // print only logs with level less than or equal Log_Level of Config
  {
    $fp = fopen( __DIR__ . '/../logs/' . date("Y-m-d") . '.log', 'a');
    fwrite($fp, "[Level_".$level." at ".date("h:i:s a")."]: ".$Message."\n");
    fclose($fp);
  }
}