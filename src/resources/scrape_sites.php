<?php
$pythonPath = '/app/python';
if (!file_exists("$pythonPath/scraper_SK.py")) {
    echo "Python script SK not found.";
    exit();
}

if (!file_exists("$pythonPath/scraper_EU.py")) {
    echo "Python script EU not found.";
    exit();
}

set_time_limit(600);
$command = "python3 $pythonPath/scraper_SK.py && python3 $pythonPath/scraper_EU.py";
$output = shell_exec($command);
header('Content-Type: text/plain');
echo $output ? $output : "Done";
?>