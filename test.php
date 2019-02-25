<?php

$sldFile = "sld.csv";
$inputFile = "input.csv";
$resultFile = "Result.csv";
$tempFile = "temp.csv";

include_once('functions.php');


logToFile("");
logToFile("*********************");
logToFile("START <<<");

$urlMap = LoadURLmap($sldFile);


$old_file = 'input.csv';
$new_file = 'Result.csv';
$file_to_read = file_get_contents($inputFile);  // Reading entire file
$lines_to_read = explode("\n", $file_to_read);  // Creating array of lines

if (($handle = fopen("input.csv", "r")) !== FALSE) {
    $n = 0;
    logToFile("Extract Root Domain from: " . $inputFile);

    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        var_dump($data);
        //if ( $lines_to_read == '' ) die('EOF'); // No dat
        $line_to_append = array_shift($lines_to_read); // Extracting first line
        $file_to_append = file_get_contents('Result.csv');  // Reading entire file
        $extracthost = ExtractRootDomain($line_to_append, $urlMap);//Extract Domeine from line

// Writing files
        file_put_contents($new_file, $file_to_append . $extracthost . "\n");
        file_put_contents($inputFile, implode("\n", $lines_to_read));

        //if ( $lines_to_read == '' ) die('EOF'); // No data
        $n = $n + 1;
    }
    logToFile('number of complete lines: ' . $n);
}
fclose($handle);


logToFile("START - Remove Duplicate");
//removeDuplicate("Result.csv");
logToFile("Remove Duplicate complete");

logToFile("RESTART");
file_get_contents('http://localhost/DomainExtract/test.php');
