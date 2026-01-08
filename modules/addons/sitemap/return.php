<?php
$moduleDir = dirname(__FILE__);

// Directory to check - using $moduleDir
$dir = $moduleDir . "/";

// Check if directory exists
if (!is_dir($dir)) {
    die("Directory does not exist: $dir");
}

// Get all XML files
$files = glob($dir . "*.xml");

if (!$files) {
    die("No XML files found in: $dir");
}

// Sort files by last modified time, descending (latest first)
usort($files, function ($a, $b) {
    return filemtime($b) - filemtime($a);
});

// Get the latest XML file (first in sorted list)
$latestFile = $files[0];

// Set header to output XML properly
header("Content-Type: application/xml");

// Output the contents of the latest file
readfile($latestFile);