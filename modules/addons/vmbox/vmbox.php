<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/lib/Registration.php';
require_once __DIR__ . '/lib/Connect.php';
require_once __DIR__ . '/lib/Service.php';
require_once __DIR__ . '/lib/Utils.php';
require_once __DIR__ . '/lib/Log.php';

/**
 * VMBOX WHMCS Module
 *
 */

if (!defined("WHMCS")) {
  die("This file cannot be accessed directly");
}

function vmbox_ConfigOptions($params)
{

  try {
    $plans = VMBOX__GetAllPlans();
    return array(
      "VM_Plan"  => array("FriendlyName" => "Plan Name", "Type" => "dropdown", "Options" => $plans, "Description" => "Plan name in vmbox Panel",),
      "CustomizeOs" => array(
        "FriendlyName" => "Customize OS",
        "Type" => "yesno",
        "Description" => "creates Configurable Option as Customize OS from plan's templates list",
      ),
      "CustomizeCpu" => array(
        "FriendlyName" => "Customize CPU",
        "Type" => "yesno",
        "Description" => "creates Configurable Option as Customize CPU",
      ),
      "CustomizeMemory" => array(
        "FriendlyName" => "Customize Memory",
        "Type" => "yesno",
        "Description" => "creates Configurable Option as Customize Memory",
      ),
      "CustomizeHardDisk" => array(
        "FriendlyName" => "Additional Hard Disk",
        "Type" => "yesno",
        "Description" => "creates Configurable Option as Customize Hard Disk",
      ),
    );
  } catch (Exception $e) {
    VMBOX__LogError($e);
    return array("Error" => array("Type" => "dropdown", "Options" => $e->getMessage()));
  }
}

function vmbox_MetaData()
{
  return array(
    'DisplayName' => 'VMBOX Module'
  );
}

function vmbox_ClientArea($params)
{
  try {
    $config = VMBOX__GetConfigData();
    VMBOX__SaveClientAreaParams($params, $config);

    if ($_GET["console"] == "novnc" && false) {
      $vncSpec = (new Service($params))->GetVncSpec();
      $vncSpec = json_decode($vncSpec);
      if ($vncSpec->{"Error"}) {
        echo $vncSpec->{"Error"};
        die();
      } else if ($vncSpec->{"Host"} == "") {
        echo "Error on getting VNC Host Address\n\n";
        echo json_encode($vncSpec);
        die();
      }
      $enc = "false";
      if ($vncSpec->{"Encrypt"} == "1") {
        $enc = "true";
      }
      $root = "modules/servers/vmbox";
      $novncView = file_get_contents($root . "/novnc/vnc.html");
      $vncParams = array(
        'ROOT' => $root . "/novnc",
        'HOST' => $vncSpec->{"Host"},
        'PORT' => $vncSpec->{"Port"},
        'ENCRYPT' => $enc,
        'HASH' => $vncSpec->{"Hash"},
        'PASSWORD' => $vncSpec->{"Pass"}
      );
      foreach ($vncParams as $k => $v) {
        $novncView = str_replace('{{' . $k . '}}', $v, $novncView);
      }
      echo $novncView;
      die();
      return;
    } else if ($_GET["console"] == "wmks") {
      $wmksSpec = (new Service($params))->GetWmksSpec();
      $wmksSpec = json_decode($wmksSpec);
      if ($wmksSpec->{"Error"}) {
        echo $wmksSpec->{"Error"};
        die();
      } else if ($wmksSpec->{"Host"} == "") {
        echo "Error on getting WMKS Host Address\n\n";
        die();
      } else if ($wmksSpec->{"Hash"} == "") {
        echo '<html><body><div style="width: 100vw; height: 100vh; display: flex; justify-content: center; align-items: center; position: fixed;">Sorry, No WMKS ticket available !!!</div></body></html>';
        die();
      }
      $proto = "ws";
      if ($wmksSpec->{"Encrypt"} == "1") {
        $proto = "wss";
      }
      $root = "modules/servers/vmbox";
      $wmksView = file_get_contents($root . "/wmks/wmks.html");
      $wmksParams = array(
        'ROOT' => $root . "/wmks",
        'HOST' => $wmksSpec->{"Host"},
        'PORT' => $wmksSpec->{"Port"},
        'PROTO' => $proto,
        'TICKET' => $wmksSpec->{"Hash"},
        'STRTYPE' => "",
      );
      foreach ($wmksParams as $k => $v) {
        $wmksView = str_replace('{{' . $k . '}}', $v, $wmksView);
      }
      echo $wmksView;
      die();
      return;
    } else if ($_GET["panel"] == "1") {
      $wsHost = $config['VMBOX_WS'];
      $wsHash = (new Service($params))->GetWsHash();
      $root = "modules/servers/vmbox";
      $websocketView = file_get_contents($root . "/templates/clientpanel.html");
      $websocketViewParams = array(
        'wsAddr' => $wsHost . "/" . $wsHash,
        'wsHash' => $wsHash,
        'defaultPassword' => $params["password"],
        'defaultUsername' => $params["username"],

        'params' => json_encode($params),
      );
      if ($_GET["lang"]) {
        $websocketViewParams["lang"] = $_GET["lang"];
      }
      foreach ($websocketViewParams as $k => $v) {
        $websocketView = str_replace('{{' . $k . '}}', $v, $websocketView);
      }
      echo $websocketView;
      die();
      return;
    }

    if ($config["WebSocket"]) {
      return array(
        'templatefile' => "templates/clientarea-ws.tpl",
        'vars' => array(
          'params' => json_encode($params),
        ),
      );
    }

    return array(
      'tabOverviewReplacementTemplate' => "templates/clientarea-cgi.tpl",
      'templateVariables' => array(),
    );
  } catch (Exception $e) {
    VMBOX__LogError($e);
    return $e->getMessage();
  }
}


// "Register VM" action from admin panel
function vmbox_register($params)
{
  return handleRegisterAction($params, false);
}

// "Register and Deploy VM" action from admin panel
function vmbox_registerDeploy($params)
{
  return handleRegisterAction($params, true);
}

function handleRegisterAction($params, $deployAfterRegister)
{
  $hostings = Capsule::table('tblhosting')->where('id', $params['accountid'])->get();
  $hosting = $hostings[0];

  // function at '/lib/Registration.php'
  if ($deployAfterRegister) {
    $result = VMBOX__RegisterAndDeploy($hosting);
  } else {
    $result = VMBOX__Register($hosting);
  }

  if (is_bool($result)) { // error on getting Registration Spec, usualy happens when $hosting server type is not 'vmbox' (product is using other modules)
    return "error on registering VM";
  }

  if ($result->{'result'} == "err") {
    return $result->{'err'};
  } else if ($result->{'result'} != "ok") {
    return "Unknown Error";
  }

  $updateService = false;
  $updates = array();

  if ($result->{'Password'} != "") {
    $updateService = true;
    $command = 'EncryptPassword';
    $postData = array(
      'password2' => $result->{'Password'},
    );
    $adminUsername = '';
    $encPassRes = localAPI($command, $postData, $adminUsername);

    $updates['password'] = $encPassRes['password'];
  }

  if ($result->{'Hostname'} != "") {
    $updateService = true;
    $updates['domain'] = $result->{'Hostname'};
  }

  if ($result->{'IpAddress'} != "") {
    $updateService = true;
    $updates['dedicatedip'] = $result->{'IpAddress'};
  }

  if ($result->{'Username'} != "") {
    $updateService = true;
    $updates['username'] = $result->{'Username'};
  }

  if ($updateService) {
    Capsule::table('tblhosting')->where('id', $hosting->id)->update($updates);
  }

  return "success";
}

function vmbox_AdminCustomButtonArray($params)
{
  if ($_GET["console"] == "wmks") {
    $wmksSpec = (new Service($params))->GetWmksSpec();
    $wmksSpec = json_decode($wmksSpec);
    if ($wmksSpec->{"Error"}) {
      echo $wmksSpec->{"Error"};
      die();
    } else if ($wmksSpec->{"Host"} == "") {
      echo "Error on getting WMKS Host Address\n\n";
      die();
    } else if ($wmksSpec->{"Hash"} == "") {
      echo '<html><body><div style="width: 100vw; height: 100vh; display: flex; justify-content: center; align-items: center; position: fixed;">Sorry, No WMKS ticket available !!!</div></body></html>';
      die();
    }
    $proto = "ws";
    if ($wmksSpec->{"Encrypt"} == "1") {
      $proto = "wss";
    }
    $root = "../modules/servers/vmbox";
    $wmksView = file_get_contents($root . "/wmks/wmks.html");
    $wmksParams = array(
      'ROOT' => $root . "/wmks",
      'HOST' => $wmksSpec->{"Host"},
      'PORT' => $wmksSpec->{"Port"},
      'PROTO' => $proto,
      'TICKET' => $wmksSpec->{"Hash"},
      'STRTYPE' => $params["password"],
    );
    foreach ($wmksParams as $k => $v) {
      $wmksView = str_replace('{{' . $k . '}}', $v, $wmksView);
    }
    echo $wmksView;
    die();
    return;
  }
  return array(
    "Register VM" => "register",
    "Register and Deploy VM" => "registerDeploy",
  );
}

function vmbox_AdminServicesTabFields($params)
{
  $Link = VMBOX__GetConfigData()['VMBOX_Config'] . "/panel/customers/machines/package/" . $params['accountid'];
  return array(
    'VMBOX Panel' => '
     <script>
     function RefreshAdminFrame() {
      document.getElementById("adminPanel").src=document.getElementById("adminPanel").src;
     }
     </script>
     <div style="display: flex; justify-content: flex-end;">
     <span style="cursor: pointer;" onclick="RefreshAdminFrame()">Reload</span>
     </div>
     <iframe id="adminPanel" style="width: 100%; height: 80vh; border: none;" src="' . $Link . '?hideMenu&hideAppBar"></iframe>
     <div style="display: flex; justify-content: center;"><a target="_blank" style="margin:auto;" href="' . $Link . '">' . $Link . '</a></div>',

    'Console' => '<button onclick="window.open(window.location.href + `&console=wmks`, `_blank`);">WMKS</button>',
  );
}

function vmbox_CreateAccount($params)
{
  return handleRegisterAction($params, true);
}

function vmbox_SuspendAccount($params)
{
  (new Service($params))->Suspend();
  return "success";
}

function vmbox_UnsuspendAccount($params)
{
  (new Service($params))->Unsuspend();
  return "success";
}

function vmbox_TerminateAccount($params)
{
  (new Service($params))->Terminate();
  return "success";
}

function vmbox_Renew($params)
{
  (new Service($params))->Unsuspend();
  return "success";
}
