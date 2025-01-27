<?php
//$pythonPath = '/app/python';
$scripts = [
    'scraper_SK.py',
    'scraper_EU.py',
    'sendmail.py'
];

foreach ($scripts as $script) {
    if (!file_exists("$script")) {
        echo "Python script $script not found.";
    exit();
}
}

set_time_limit(600);

$commands = [
    "python3 scraper_SK.py",
    "python3 scraper_EU.py",
    "python3 sendmail.py"
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
