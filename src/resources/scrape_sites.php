<?php
$pythonPath = '/app/python';
$scripts = [
    'scraper_SK.py',
    'scraper_EU.py',
    'sendmail.py'
];

foreach ($scripts as $script) {
    if (!file_exists("$pythonPath/$script")) {
        echo "Python script $script not found.";
    exit();
}
}

set_time_limit(600);

$commands = [
    //"python3 $pythonPath/scraper_SK.py",
    //"python3 $pythonPath/scraper_EU.py",
    "python3 $pythonPath/sendmail.py"
];

$output = '';
foreach ($commands as $command) {
    $result = shell_exec($command);
    if ($result === null) {
        echo "Error executing command: $command";
        exit();
    }
    $output .= $result . "\n";
}

header('Content-Type: text/plain');
echo $output ? $output : "Done";
?>