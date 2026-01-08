<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/Connect.php';
require_once __DIR__ . '/Log.php';

function VMBOX__RegisterAndDeploy($hosting) {
  $spec = VMBOX__getRegistrationSpec($hosting);
  if(is_bool($spec)) { // check for error
    return false;
  }
  $result = VMBOX__PostData(VMBOX__GetConfigData()["VMBOX_URL"] . "/registerDeployPlan", $spec);
  return json_decode($result);
}

function VMBOX__Register($hosting) {
  $spec = VMBOX__getRegistrationSpec($hosting);
  if(is_bool($spec)) { // check for error
    return false;
  }
  $result = VMBOX__PostData(VMBOX__GetConfigData()["VMBOX_URL"] . "/registerPlan", $spec);
  return json_decode($result);
}


function VMBOX__getRegistrationSpec($hosting) {
  $PackageId = $hosting->packageid;
  $password = $hosting->password;
  if ($password != "") {
    $command = 'DecryptPassword';
    $postData = array(
      'password2' => $password,
    );
    $adminUsername = "";
    $passwordRes = localAPI($command, $postData, $adminUsername);
    $password = $passwordRes['password'];
  }
  $singleProduct = Capsule::table('tblproducts')->where('id', $PackageId)->get();
  $serverType = $singleProduct[0]->servertype;
  if ($serverType != "vmbox") {
    return false;
  }

  $user = Capsule::table('tblclients')->where('id', $hosting->userid)->get();
  $fName = $user[0]->firstname;
  $lName = $user[0]->lastname;
  $email = $user[0]->email;

  // getting CustomizeOS Option Value
  $CustomizeOs = "0";
  $CustomizeOsOption = "";
  $CustomizeCpu = "";
  $CustomizeMemory = "";
  $CustomizeHardDisk = "";
  if ($singleProduct[0]->configoption2 == "on") {
    $configGroup = Capsule::table('tblproductconfiggroups')->where('name', "vmbox_customizeOS_" . $singleProduct[0]->name)->get();
    $configOption = Capsule::table('tblproductconfigoptions')->where('gid', $configGroup[0]->id)->get();
    $hostingOption = Capsule::table('tblhostingconfigoptions')->where('relid', $hosting->id)->where('configid', $configOption[0]->id)->get();
    $OptionSub = Capsule::table('tblproductconfigoptionssub')->where('id', $hostingOption[0]->optionid)->get();
    $CustomizeOs = "1";
    $CustomizeOsOption = $OptionSub[0]->optionname;
  }
  // Customize CPU
  if ($singleProduct[0]->configoption3 == "on") {
    $configGroup = Capsule::table('tblproductconfiggroups')->where('name', "vmbox_customizeCpu_" . $singleProduct[0]->name)->get();
    $configOption = Capsule::table('tblproductconfigoptions')->where('gid', $configGroup[0]->id)->get();
    $hostingOption = Capsule::table('tblhostingconfigoptions')->where('relid', $hosting->id)->where('configid', $configOption[0]->id)->get();
    $OptionSub = Capsule::table('tblproductconfigoptionssub')->where('id', $hostingOption[0]->optionid)->get();
    $CustomizeCpu = $OptionSub[0]->optionname;
  }
  // Customize Memory
  if ($singleProduct[0]->configoption4 == "on") {
    $configGroup = Capsule::table('tblproductconfiggroups')->where('name', "vmbox_customizeMemory_" . $singleProduct[0]->name)->get();
    $configOption = Capsule::table('tblproductconfigoptions')->where('gid', $configGroup[0]->id)->get();
    $hostingOption = Capsule::table('tblhostingconfigoptions')->where('relid', $hosting->id)->where('configid', $configOption[0]->id)->get();
    $OptionSub = Capsule::table('tblproductconfigoptionssub')->where('id', $hostingOption[0]->optionid)->get();
    $CustomizeMemory = $OptionSub[0]->optionname;
  }
  // Customize HardDisk
  if ($singleProduct[0]->configoption5 == "on") {
    $configGroup = Capsule::table('tblproductconfiggroups')->where('name', "vmbox_customizeHardDisk_" . $singleProduct[0]->name)->get();
    $configOption = Capsule::table('tblproductconfigoptions')->where('gid', $configGroup[0]->id)->get();
    $hostingOption = Capsule::table('tblhostingconfigoptions')->where('relid', $hosting->id)->where('configid', $configOption[0]->id)->get();
    $OptionSub = Capsule::table('tblproductconfigoptionssub')->where('id', $hostingOption[0]->optionid)->get();
    $CustomizeHardDisk = $OptionSub[0]->optionname;
  }
  VMBOX__LogData(1, array(
    "Registration" => $hosting->id,
  ));
  return array(
    "UserId" => $hosting->userid,
    "UserFirstName" => $fName,
    "UserLastName" => $lName,
    "UserEmail" => $email,
    "Password" => $password,
    "Hostname" => $hosting->domain,
    "Vm_id" => $hosting->id,
    "Vm_plan" => $singleProduct[0]->configoption1, // plan name
    "CustomizeOs" => $CustomizeOs, // 0 1 for customize os
    "CustomizeOsOption" => $CustomizeOsOption,
    "CustomizeCpu" => $CustomizeCpu,
    "CustomizeMemory" => $CustomizeMemory,
    "CustomizeHardDisk" => $CustomizeHardDisk
  );
}