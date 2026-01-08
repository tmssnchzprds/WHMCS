<?php

namespace WHMCS\Module\Addon\Resellerclubdomains\Library;

class Debug
{
    public function printMemoryUsage()
    {
        if (DEBUG_MODE) {
            echo "<pre>";
            echo __METHOD__ . PHP_EOL;
            print_r("<strong>MEMORY: " . number_format(round(memory_get_usage() / 1024)) . " KB</strong>");
            echo "</pre>";
        }
    }
}
