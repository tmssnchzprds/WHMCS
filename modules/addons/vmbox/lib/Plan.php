<?php

require_once __DIR__ . '/Connect.php';

class Plan {
  var $PlanName;

  function Plan($planName) {
    $this->PlanName = $planName;
  }

  private function SendRequest($url) {
    return VMBOX__PostData(VMBOX__GetConfigData()["VMBOX_URL"] . $url, array(
      "PlanName" => $this->PlanName,
    ));
  }

  public function GetTemplateNames() {
    return $this->SendRequest("/getPlanTemplates");
  }
  public function GetCpuProfileNames() {
    return $this->SendRequest("/getCpuProfiles");
  }
  public function GetMemoryProfileNames() {
    return $this->SendRequest("/getMemoryProfiles");
  }
  public function GetHardDiskProfileNames() {
    return $this->SendRequest("/getHardDiskProfiles");
  }
}