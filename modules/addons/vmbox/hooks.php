<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/lib/Registration.php';
require_once __DIR__ . '/lib/Log.php';
require_once __DIR__ . '/lib/Utils.php';

add_hook('ProductEdit', 1, function ($vars) {
  if ($vars["servertype"] == "vmbox") {
    // functions at '/lib/Utils.php'
    VMBOX__GenerateCustomizeOsOptions($vars);
    VMBOX__GenerateCustomizeCpuOptions($vars);
    VMBOX__GenerateCustomizeMemoryOptions($vars);
    VMBOX__GenerateCustomizeHardDiskOptions($vars);
  }
});
