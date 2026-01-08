<?php

require_once __DIR__ . '/Connect.php';
require_once __DIR__ . '/Log.php';

class Service {
  var $UserId;
  var $Vm_id;

  function Service($params) {
    $this->UserId = $params['userid'];
    $this->Vm_id = $params['accountid'];
  }

  private function SendRequest($url) {
    VMBOX__LogData(2, array(
      "UserId" => $this->UserId,
      "Vm_id" => $this->Vm_id,
      "Url" => VMBOX__GetConfigData()["VMBOX_URL"].$url,
    ));
    return VMBOX__PostData(VMBOX__GetConfigData()["VMBOX_URL"] . $url, array(
      "UserId" => $this->UserId,
      "Vm_id" => $this->Vm_id,
    ));
  }

  public function Suspend() {
    return $this->SendRequest("/suspend");
  }
  public function Unsuspend() {
    return $this->SendRequest("/unSuspend");
  }
  public function Terminate() {
    return $this->SendRequest("/terminate");
  }

  public function GetWsHash() {
    return $this->SendRequest("/registerNewPanel");
  }
  public function GetVncSpec() {
    return $this->SendRequest("/registerNewVnc");
  }
  public function GetWmksSpec() {
    return $this->SendRequest("/registerNewWmks");
  }

  public function GetActionsForCustomer() {
    return $this->SendRequest("/getActionsForCustomer");
  }
  public function GetStatus() {
    return $this->SendRequest("/getVmStatus");
  }
  public function PowerOff() {
    return $this->SendRequest("/powerOffVM");
  }
  public function Delete() {
    return $this->SendRequest("/deleteVM");
  }
  public function PowerOn() {
    return $this->SendRequest("/powerOnVM");
  }
  public function Deploy() {
    return $this->SendRequest("/deployVM");
  }
}