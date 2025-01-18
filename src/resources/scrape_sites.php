<?php
if (!file_exists("scraper_SK.py")) {
    echo "Python script not found.";
    exit();
}
if (!file_exists("scraper_EU.py")) {
    echo "Python script not found.";
    exit();
}
$pythonScriptSK = 'C:\\xampp\\htdocs\\inspection\\inspection\\src\\resources\\scraper_SK.py';
$pythonScriptEU = 'C:\\xampp\\htdocs\\inspection\\inspection\\src\\resources\\scraper_EU.py';
$venvPath = 'C:\\xampp\\htdocs\\inspection\\inspection\\venv\\Scripts\\activate';
set_time_limit(600);
$command = "cmd /c \"$venvPath && python $pythonScriptSK && python $pythonScriptEU\"";
$output = shell_exec($command);
header('Content-Type: text/plain');
echo $output ? $output : "Done";
?>