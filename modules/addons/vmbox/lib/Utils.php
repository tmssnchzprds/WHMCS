<?php

use Illuminate\Database\Capsule\Manager as Capsule;


require_once __DIR__ . '/Connect.php';
require_once __DIR__ . '/Plan.php';
require_once __DIR__ . '/Log.php';

function VMBOX__SaveClientAreaParams($params, $ConfigData)
{
  $_SESSION["VMBOX_Clientarea_Params"] = array(
    "token" => $ConfigData["VMBOX_TOKEN"],
    "userid" => $params["userid"], // clientId in customers at vmbox
    "pid" => $params["pid"], // packageId in tblhostings at whmcs and whmcsPackageId in vmachines at vmbox
    "vm_plan" => (new ConfigOption($params))->GetPlanName(), // planName in vmplans at vmbox
    "accountid" => $params["accountid"],
  );
}



function VMBOX__GenerateCustomizeOsOptions($vars)
{
  VMBOX__LogText(2, "GenerateCustomizeOsOptions");
  $configOpName = "vmbox_customizeOS_" . $vars["name"];
  $configOption = Capsule::table('tblproductconfiggroups')->where('name', $configOpName)->get();
  if ((new ConfigOption($vars))->GetIsCustomizeOsEnabled()) {
    if (count($configOption) == 0) {
      $pdo = Capsule::connection()->getPdo();
      $pdo->beginTransaction();
      try {
        $statement = $pdo->prepare('insert into tblproductconfiggroups (name, description) values (:name, :description)');
        $statement->execute([':name' => $configOpName, ':description' => "auto created configurable option",]);
        $pdo->commit();
        VMBOX__LogText(2, "ConfigGroup Created");
        $configOption = Capsule::table('tblproductconfiggroups')->where('name', $configOpName)->get();
        $configOpGroupId = $configOption[0]->id;
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfigoptions (gid, optionname, optiontype) values (:gid, :optionname, :optiontype)');
        $statement->execute([':gid' => $configOpGroupId, ':optionname' => "Operating System", ':optiontype' => 1,]);
        $pdo->commit();
        $configOption = Capsule::table('tblproductconfigoptions')->where('gid', $configOpGroupId)->where('optionname', "Operating System")->get();
        $result = (new Plan((new ConfigOption($vars))->GetPlanName()))->GetTemplateNames();
        $OSS = explode(",", $result);
        foreach ($OSS as $OS) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblproductconfigoptionssub (configid, optionname) values (:configid, :optionname)');
          $statement->execute([':configid' => $configOption[0]->id, ':optionname' => $OS,]);
          $pdo->commit();
          $configOptionSub = Capsule::table('tblproductconfigoptionssub')->where('configid', $configOption[0]->id)
            ->where('optionname', $OS)->get();
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblpricing (type, currency, relid) values (:type, :currency, :relid)');
          $statement->execute([':type' => "configoptions", ':currency' => "1", ':relid' => $configOptionSub[0]->id,]);
          $pdo->commit();
        }
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfiglinks (gid, pid) values (:gid, :pid)');
        $statement->execute([':gid' => $configOpGroupId, ':pid' => $vars["pid"],]);
        $pdo->commit();
      } catch (\Exception $e) {
        $pdo->rollBack();
        VMBOX__LogError($e);
        return;
      }
    } else {
      $pdo = Capsule::connection()->getPdo();
      $configOptionLink = Capsule::table('tblproductconfiglinks')->where('pid', $vars["pid"])->where('gid', $configOption[0]->id)
        ->get();
      if (count($configOptionLink) == 0) {
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfiglinks (gid, pid) values (:gid, :pid)');
        $statement->execute([':gid' => $configOption[0]->id, ':pid' => $vars["pid"],]);
        $pdo->commit();
      }
      $configOptionO = Capsule::table('tblproductconfigoptions')->where('gid', $configOption[0]->id)->get();
      if (count($configOptionO) == 0) {
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfigoptions (gid, optionname, optiontype) values (:gid, :optionname, :optiontype)');
        $statement->execute([':gid' => $configOption[0]->id, ':optionname' => "Operating System", ':optiontype' => 1,]);
        $pdo->commit();
        $configOptionO = Capsule::table('tblproductconfigoptions')->where('gid', $configOption[0]->id)->get();
      }
      $configOptionSubs = Capsule::table('tblproductconfigoptionssub')->where('configid', $configOptionO[0]->id)->get();
      $result = (new Plan((new ConfigOption($vars))->GetPlanName()))->GetTemplateNames();
      $OSS = explode(",", $result);
      foreach ($configOptionSubs as $configOptionSub) {
        $found = false;
        foreach ($OSS as $OS) {
          if ($OS == $configOptionSub->optionname) {
            $found = true;
            break;
          }
        }
        if ($found == false) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('delete from tblproductconfigoptionssub where id=:id');
          $statement->execute([':id' => $configOptionSub->id]);
          $pdo->commit();
          $pdo->beginTransaction();
          $statement = $pdo->prepare('delete from tblpricing where relid=:relid');
          $statement->execute([':relid' => $configOptionSub->id]);
          $pdo->commit();
        }
      }
      foreach ($OSS as $OS) {
        $found = false;
        foreach ($configOptionSubs as $configOptionSub) {
          if ($OS == $configOptionSub->optionname) {
            $found = true;
            break;
          }
        }
        if ($found == false) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblproductconfigoptionssub (configid, optionname) values (:configid, :optionname)');
          $statement->execute([':configid' => $configOptionO[0]->id, ':optionname' => $OS,]);
          $pdo->commit();
          $configOptionSubb = Capsule::table('tblproductconfigoptionssub')->where('configid', $configOptionO[0]->id)
            ->where('optionname', $OS)->get();
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblpricing (type, currency, relid) values (:type, :currency, :relid)');
          $statement->execute([':type' => "configoptions", ':currency' => "1", ':relid' => $configOptionSubb[0]->id,]);
          $pdo->commit();
        }
      }
    }
  } else {
    if (count($configOption) > 0) {
      $pdo = Capsule::connection()->getPdo();
      try {
        foreach ($configOption as $confOp) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('delete from tblproductconfiglinks where pid=:pid and gid=:gid');
          $statement->execute([':pid' => $vars["pid"], ':gid' => $confOp->id,]);
          $pdo->commit();
        }
      } catch (\Exception $e) {
        $pdo->rollBack();
      }
    }
  }
}




function VMBOX__GenerateCustomizeCpuOptions($vars)
{
  $configOpName = "vmbox_customizeCpu_" . $vars["name"];
  $configOpNameS = "CPU";
  $configOption = Capsule::table('tblproductconfiggroups')->where('name', $configOpName)->get();
  if ((new ConfigOption($vars))->GetIsCustomizeCpuEnabled()) // CustomizeCpu
  {
    if (count($configOption) == 0) {
      $pdo = Capsule::connection()->getPdo();
      $pdo->beginTransaction();
      try {
        $statement = $pdo->prepare('insert into tblproductconfiggroups (name, description) values (:name, :description)');
        $statement->execute([':name' => $configOpName, ':description' => "auto created configurable option",]);
        $pdo->commit();
        $configOption = Capsule::table('tblproductconfiggroups')->where('name', $configOpName)->get();
        $configOpGroupId = $configOption[0]->id;
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfigoptions (gid, optionname, optiontype) values (:gid, :optionname, :optiontype)');
        $statement->execute([':gid' => $configOpGroupId, ':optionname' => $configOpNameS, ':optiontype' => 1,]);
        $pdo->commit();
        $configOption = Capsule::table('tblproductconfigoptions')->where('gid', $configOpGroupId)->where('optionname', $configOpNameS)->get();
        $result = (new Plan((new ConfigOption($vars))->GetPlanName()))->GetCpuProfileNames();
        $CPUS = explode(",", $result);
        foreach ($CPUS as $CPU) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblproductconfigoptionssub (configid, optionname) values (:configid, :optionname)');
          $statement->execute([':configid' => $configOption[0]->id, ':optionname' => $CPU,]);
          $pdo->commit();
          $configOptionSub = Capsule::table('tblproductconfigoptionssub')->where('configid', $configOption[0]->id)
            ->where('optionname', $CPU)->get();
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblpricing (type, currency, relid) values (:type, :currency, :relid)');
          $statement->execute([':type' => "configoptions", ':currency' => "1", ':relid' => $configOptionSub[0]->id,]);
          $pdo->commit();
        }
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfiglinks (gid, pid) values (:gid, :pid)');
        $statement->execute([':gid' => $configOpGroupId, ':pid' => $vars["pid"],]);
        $pdo->commit();
      } catch (\Exception $e) {
        $pdo->rollBack();
        return;
      }
    } else {
      $pdo = Capsule::connection()->getPdo();
      $configOptionLink = Capsule::table('tblproductconfiglinks')->where('pid', $vars["pid"])->where('gid', $configOption[0]->id)
        ->get();
      if (count($configOptionLink) == 0) {
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfiglinks (gid, pid) values (:gid, :pid)');
        $statement->execute([':gid' => $configOption[0]->id, ':pid' => $vars["pid"],]);
        $pdo->commit();
      }
      $configOptionO = Capsule::table('tblproductconfigoptions')->where('gid', $configOption[0]->id)->get();
      if (count($configOptionO) == 0) {
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfigoptions (gid, optionname, optiontype) values (:gid, :optionname, :optiontype)');
        $statement->execute([':gid' => $configOption[0]->id, ':optionname' => $configOpNameS, ':optiontype' => 1,]);
        $pdo->commit();
        $configOptionO = Capsule::table('tblproductconfigoptions')->where('gid', $configOption[0]->id)->get();
      }
      $configOptionSubs = Capsule::table('tblproductconfigoptionssub')->where('configid', $configOptionO[0]->id)->get();
      $result = (new Plan((new ConfigOption($vars))->GetPlanName()))->GetCpuProfileNames();
      $CPUS = explode(",", $result);
      foreach ($configOptionSubs as $configOptionSub) {
        $found = false;
        foreach ($CPUS as $CPU) {
          if ($CPU == $configOptionSub->optionname) {
            $found = true;
            break;
          }
        }
        if ($found == false) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('delete from tblproductconfigoptionssub where id=:id');
          $statement->execute([':id' => $configOptionSub->id]);
          $pdo->commit();
          $pdo->beginTransaction();
          $statement = $pdo->prepare('delete from tblpricing where relid=:relid');
          $statement->execute([':relid' => $configOptionSub->id]);
          $pdo->commit();
        }
      }
      foreach ($CPUS as $CPU) {
        $found = false;
        foreach ($configOptionSubs as $configOptionSub) {
          if ($CPU == $configOptionSub->optionname) {
            $found = true;
            break;
          }
        }
        if ($found == false) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblproductconfigoptionssub (configid, optionname) values (:configid, :optionname)');
          $statement->execute([':configid' => $configOptionO[0]->id, ':optionname' => $CPU,]);
          $pdo->commit();
          $configOptionSubb = Capsule::table('tblproductconfigoptionssub')->where('configid', $configOptionO[0]->id)
            ->where('optionname', $CPU)->get();
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblpricing (type, currency, relid) values (:type, :currency, :relid)');
          $statement->execute([':type' => "configoptions", ':currency' => "1", ':relid' => $configOptionSubb[0]->id,]);
          $pdo->commit();
        }
      }
    }
  } else {
    if (count($configOption) > 0) {
      $pdo = Capsule::connection()->getPdo();
      try {
        foreach ($configOption as $confOp) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('delete from tblproductconfiglinks where pid=:pid and gid=:gid');
          $statement->execute([':pid' => $vars["pid"], ':gid' => $confOp->id,]);
          $pdo->commit();
        }
      } catch (\Exception $e) {
        $pdo->rollBack();
      }
    }
  }
}




function VMBOX__GenerateCustomizeMemoryOptions($vars)
{
  $configOpName = "vmbox_customizeMemory_" . $vars["name"];
  $configOpNameS = "Memory";
  $configOption = Capsule::table('tblproductconfiggroups')->where('name', $configOpName)->get();
  if ((new ConfigOption($vars))->GetIsCustomizeMemoryEnabled()) // CustomizeMemory
  {
    if (count($configOption) == 0) {
      $pdo = Capsule::connection()->getPdo();
      $pdo->beginTransaction();
      try {
        $statement = $pdo->prepare('insert into tblproductconfiggroups (name, description) values (:name, :description)');
        $statement->execute([':name' => $configOpName, ':description' => "auto created configurable option",]);
        $pdo->commit();
        $configOption = Capsule::table('tblproductconfiggroups')->where('name', $configOpName)->get();
        $configOpGroupId = $configOption[0]->id;
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfigoptions (gid, optionname, optiontype) values (:gid, :optionname, :optiontype)');
        $statement->execute([':gid' => $configOpGroupId, ':optionname' => $configOpNameS, ':optiontype' => 1,]);
        $pdo->commit();
        $configOption = Capsule::table('tblproductconfigoptions')->where('gid', $configOpGroupId)->where('optionname', $configOpNameS)->get();
        $result = (new Plan((new ConfigOption($vars))->GetPlanName()))->GetMemoryProfileNames();
        $MemoryS = explode(",", $result);
        foreach ($MemoryS as $Memory) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblproductconfigoptionssub (configid, optionname) values (:configid, :optionname)');
          $statement->execute([':configid' => $configOption[0]->id, ':optionname' => $Memory,]);
          $pdo->commit();
          $configOptionSub = Capsule::table('tblproductconfigoptionssub')->where('configid', $configOption[0]->id)
            ->where('optionname', $Memory)->get();
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblpricing (type, currency, relid) values (:type, :currency, :relid)');
          $statement->execute([':type' => "configoptions", ':currency' => "1", ':relid' => $configOptionSub[0]->id,]);
          $pdo->commit();
        }
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfiglinks (gid, pid) values (:gid, :pid)');
        $statement->execute([':gid' => $configOpGroupId, ':pid' => $vars["pid"],]);
        $pdo->commit();
      } catch (\Exception $e) {
        $pdo->rollBack();
        return;
      }
    } else {
      $pdo = Capsule::connection()->getPdo();
      $configOptionLink = Capsule::table('tblproductconfiglinks')->where('pid', $vars["pid"])->where('gid', $configOption[0]->id)
        ->get();
      if (count($configOptionLink) == 0) {
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfiglinks (gid, pid) values (:gid, :pid)');
        $statement->execute([':gid' => $configOption[0]->id, ':pid' => $vars["pid"],]);
        $pdo->commit();
      }
      $configOptionO = Capsule::table('tblproductconfigoptions')->where('gid', $configOption[0]->id)->get();
      if (count($configOptionO) == 0) {
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfigoptions (gid, optionname, optiontype) values (:gid, :optionname, :optiontype)');
        $statement->execute([':gid' => $configOption[0]->id, ':optionname' => $configOpNameS, ':optiontype' => 1,]);
        $pdo->commit();
        $configOptionO = Capsule::table('tblproductconfigoptions')->where('gid', $configOption[0]->id)->get();
      }
      $configOptionSubs = Capsule::table('tblproductconfigoptionssub')->where('configid', $configOptionO[0]->id)->get();
      $result = (new Plan((new ConfigOption($vars))->GetPlanName()))->GetMemoryProfileNames();
      $MemoryS = explode(",", $result);
      foreach ($configOptionSubs as $configOptionSub) {
        $found = false;
        foreach ($MemoryS as $Memory) {
          if ($Memory == $configOptionSub->optionname) {
            $found = true;
            break;
          }
        }
        if ($found == false) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('delete from tblproductconfigoptionssub where id=:id');
          $statement->execute([':id' => $configOptionSub->id]);
          $pdo->commit();
          $pdo->beginTransaction();
          $statement = $pdo->prepare('delete from tblpricing where relid=:relid');
          $statement->execute([':relid' => $configOptionSub->id]);
          $pdo->commit();
        }
      }
      foreach ($MemoryS as $Memory) {
        $found = false;
        foreach ($configOptionSubs as $configOptionSub) {
          if ($Memory == $configOptionSub->optionname) {
            $found = true;
            break;
          }
        }
        if ($found == false) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblproductconfigoptionssub (configid, optionname) values (:configid, :optionname)');
          $statement->execute([':configid' => $configOptionO[0]->id, ':optionname' => $Memory,]);
          $pdo->commit();
          $configOptionSubb = Capsule::table('tblproductconfigoptionssub')->where('configid', $configOptionO[0]->id)
            ->where('optionname', $Memory)->get();
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblpricing (type, currency, relid) values (:type, :currency, :relid)');
          $statement->execute([':type' => "configoptions", ':currency' => "1", ':relid' => $configOptionSubb[0]->id,]);
          $pdo->commit();
        }
      }
    }
  } else {
    if (count($configOption) > 0) {
      $pdo = Capsule::connection()->getPdo();
      try {
        foreach ($configOption as $confOp) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('delete from tblproductconfiglinks where pid=:pid and gid=:gid');
          $statement->execute([':pid' => $vars["pid"], ':gid' => $confOp->id,]);
          $pdo->commit();
        }
      } catch (\Exception $e) {
        $pdo->rollBack();
      }
    }
  }
}




function VMBOX__GenerateCustomizeHardDiskOptions($vars)
{
  $configOpName = "vmbox_customizeHardDisk_" . $vars["name"];
  $configOpNameS = "HardDisk";
  $configOption = Capsule::table('tblproductconfiggroups')->where('name', $configOpName)->get();
  if ((new ConfigOption($vars))->GetIsCustomizeHardDiskEnabled()) // CustomizeHardDisk
  {
    if (count($configOption) == 0) {
      $pdo = Capsule::connection()->getPdo();
      $pdo->beginTransaction();
      try {
        $statement = $pdo->prepare('insert into tblproductconfiggroups (name, description) values (:name, :description)');
        $statement->execute([':name' => $configOpName, ':description' => "auto created configurable option",]);
        $pdo->commit();
        $configOption = Capsule::table('tblproductconfiggroups')->where('name', $configOpName)->get();
        $configOpGroupId = $configOption[0]->id;
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfigoptions (gid, optionname, optiontype) values (:gid, :optionname, :optiontype)');
        $statement->execute([':gid' => $configOpGroupId, ':optionname' => $configOpNameS, ':optiontype' => 1,]);
        $pdo->commit();
        $configOption = Capsule::table('tblproductconfigoptions')->where('gid', $configOpGroupId)->where('optionname', $configOpNameS)->get();
        $result = (new Plan((new ConfigOption($vars))->GetPlanName()))->GetHardDiskProfileNames();
        $HardDiskS = explode(",", $result);
        foreach ($HardDiskS as $HardDisk) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblproductconfigoptionssub (configid, optionname) values (:configid, :optionname)');
          $statement->execute([':configid' => $configOption[0]->id, ':optionname' => $HardDisk,]);
          $pdo->commit();
          $configOptionSub = Capsule::table('tblproductconfigoptionssub')->where('configid', $configOption[0]->id)
            ->where('optionname', $HardDisk)->get();
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblpricing (type, currency, relid) values (:type, :currency, :relid)');
          $statement->execute([':type' => "configoptions", ':currency' => "1", ':relid' => $configOptionSub[0]->id,]);
          $pdo->commit();
        }
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfiglinks (gid, pid) values (:gid, :pid)');
        $statement->execute([':gid' => $configOpGroupId, ':pid' => $vars["pid"],]);
        $pdo->commit();
      } catch (\Exception $e) {
        $pdo->rollBack();
        return;
      }
    } else {
      $pdo = Capsule::connection()->getPdo();
      $configOptionLink = Capsule::table('tblproductconfiglinks')->where('pid', $vars["pid"])->where('gid', $configOption[0]->id)
        ->get();
      if (count($configOptionLink) == 0) {
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfiglinks (gid, pid) values (:gid, :pid)');
        $statement->execute([':gid' => $configOption[0]->id, ':pid' => $vars["pid"],]);
        $pdo->commit();
      }
      $configOptionO = Capsule::table('tblproductconfigoptions')->where('gid', $configOption[0]->id)->get();
      if (count($configOptionO) == 0) {
        $pdo->beginTransaction();
        $statement = $pdo->prepare('insert into tblproductconfigoptions (gid, optionname, optiontype) values (:gid, :optionname, :optiontype)');
        $statement->execute([':gid' => $configOption[0]->id, ':optionname' => $configOpNameS, ':optiontype' => 1,]);
        $pdo->commit();
        $configOptionO = Capsule::table('tblproductconfigoptions')->where('gid', $configOption[0]->id)->get();
      }
      $configOptionSubs = Capsule::table('tblproductconfigoptionssub')->where('configid', $configOptionO[0]->id)->get();
      $result = (new Plan((new ConfigOption($vars))->GetPlanName()))->GetHardDiskProfileNames();
      $HardDiskS = explode(",", $result);
      foreach ($configOptionSubs as $configOptionSub) {
        $found = false;
        foreach ($HardDiskS as $HardDisk) {
          if ($HardDisk == $configOptionSub->optionname) {
            $found = true;
            break;
          }
        }
        if ($found == false) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('delete from tblproductconfigoptionssub where id=:id');
          $statement->execute([':id' => $configOptionSub->id]);
          $pdo->commit();
          $pdo->beginTransaction();
          $statement = $pdo->prepare('delete from tblpricing where relid=:relid');
          $statement->execute([':relid' => $configOptionSub->id]);
          $pdo->commit();
        }
      }
      foreach ($HardDiskS as $HardDisk) {
        $found = false;
        foreach ($configOptionSubs as $configOptionSub) {
          if ($HardDisk == $configOptionSub->optionname) {
            $found = true;
            break;
          }
        }
        if ($found == false) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblproductconfigoptionssub (configid, optionname) values (:configid, :optionname)');
          $statement->execute([':configid' => $configOptionO[0]->id, ':optionname' => $HardDisk,]);
          $pdo->commit();
          $configOptionSubb = Capsule::table('tblproductconfigoptionssub')->where('configid', $configOptionO[0]->id)
            ->where('optionname', $HardDisk)->get();
          $pdo->beginTransaction();
          $statement = $pdo->prepare('insert into tblpricing (type, currency, relid) values (:type, :currency, :relid)');
          $statement->execute([':type' => "configoptions", ':currency' => "1", ':relid' => $configOptionSubb[0]->id,]);
          $pdo->commit();
        }
      }
    }
  } else {
    if (count($configOption) > 0) {
      $pdo = Capsule::connection()->getPdo();
      try {
        foreach ($configOption as $confOp) {
          $pdo->beginTransaction();
          $statement = $pdo->prepare('delete from tblproductconfiglinks where pid=:pid and gid=:gid');
          $statement->execute([':pid' => $vars["pid"], ':gid' => $confOp->id,]);
          $pdo->commit();
        }
      } catch (\Exception $e) {
        $pdo->rollBack();
      }
    }
  }
}


class ConfigOption {
  var $allConfigs;
  function ConfigOption($vars) {
    $this->allConfigs = $vars;
  }

  function GetPlanName() {
    return $this->allConfigs["configoption1"];
  }

  function GetIsCustomizeOsEnabled() {
    if($this->allConfigs["configoption2"] == "on") {
      return true;
    }
    return false;
  }

  function GetIsCustomizeCpuEnabled() {
    if($this->allConfigs["configoption3"] == "on") {
      return true;
    }
    return false;
  }

  function GetIsCustomizeMemoryEnabled() {
    if($this->allConfigs["configoption4"] == "on") {
      return true;
    }
    return false;
  }

  function GetIsCustomizeHardDiskEnabled() {
    if($this->allConfigs["configoption5"] == "on") {
      return true;
    }
    return false;
  }
}