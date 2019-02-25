<?php

$sldFile = "sld.csv";
$inputFile = "input.csv";
$resultFile = "Result.csv";
$tempFile = "temp.csv";

logToFile("");
logToFile("*********************");
logToFile("START <<<");


$old_file = 'input.csv';
$new_file = 'Result.csv';
$file_to_read = file_get_contents($inputFile);  // Reading entire file
$lines_to_read = explode("\n", $file_to_read);  // Creating array of lines

if (($handle = fopen("input.csv", "r")) !== FALSE) {
    $n = 0;
    logToFile("Extract Root Domain from: " . $inputFile);

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        //if ( $lines_to_read == '' ) die('EOF'); // No dat
        $line_to_append = array_shift($lines_to_read); // Extracting first line
        $file_to_append = file_get_contents('Result.csv');  // Reading entire file
        $extracthost = ExtractRootDomain($line_to_append, $sldFile);//Extract Domeine from line

// Writing files
        file_put_contents($new_file, $file_to_append . $extracthost . "\n");
        file_put_contents($inputFile, implode("\n", $lines_to_read));

        //if ( $lines_to_read == '' ) die('EOF'); // No data
        $n = $n + 1;
    }
    logToFile('number of comblete lines: ' . $n);
}
fclose($handle);


logToFile("START - Remove Duplicate");
//removeDuplicate("Result.csv");
logToFile("Remove Duplicate complete");


function removeDuplicate($file)
{

    $file_1 = file_get_contents('temp.csv');
    $file_2 = file_get_contents('Result.csv');
    file_put_contents('Result.csv', $file_1 . $file_2);

// array to hold all unique lines
    $lines = array();

// array to hold all unique SKU codes
    $skus = array();

// index of the `sku` column
    $skuIndex = -1;

// open the "save-file"
    if (($saveHandle = fopen("Result.csv", "w")) !== false) {
        // open the csv file
        if (($readHandle = fopen($file, "r")) !== false) {
            // read each line into an array
            while (($data = fgetcsv($readHandle, 8192, ",")) !== false) {
                if ($skuIndex == -1) {
                    // we need to determine what column the "sku" is; this will identify
                    // the "unique" rows
                    foreach ($data as $index => $column) {
                        if ($column == 'HOST') {
                            $skuIndex = $index;
                            break;
                        }
                    }
                    if ($skuIndex == -1) {
                        echo "Couldn't determine the SKU-column.";
                        die();
                    }
                    // write this line to the file
                    fputcsv($saveHandle, $data);
                }

                // if the sku has been seen, skip it
                if (isset($skus[$data[$skuIndex]])) continue;
                $skus[$data[$skuIndex]] = true;

                // write this line to the file
                fputcsv($saveHandle, $data);
            }
            fclose($readHandle);
        }
        fclose($saveHandle);
        file_put_contents("Result.csv", "");
    }
}


function logToFile($content)
{
    $file = "log.txt";
    $file_content = date("M-d") . " - " . date("h:i:s") . " |>>>  " . $content . "\n";
    $file_content .= file_get_contents($file);
    file_put_contents($file, $file_content);
}

function ExtractRootDomain($url, $sldFile)
{
    $csvData = file_get_contents($sldFile);
    $urlMap = explode(PHP_EOL, $csvData);

    $host = "";
    $url = trim($url);
    $url = strtolower($url);

    if ($url == "") {
        return "empty line" . "," . "ERORR";
    }
    $urlData = parse_url($url);

    $hostData = explode('.', $urlData['host']);
    $hostData = array_reverse($hostData);

    if (array_search($hostData[1] . '.' . $hostData[0], $urlMap) !== FALSE) {
        $host = $hostData[2] . '.' . $hostData[1] . '.' . $hostData[0];
    }
    if (array_search($hostData[0], $urlMap) !== FALSE) {
        $host = $hostData[1] . '.' . $hostData[0];
    }
    if ($host == "") {
        logToFile("R ");
        return $url . "," . "ERORR";
    } else {
        if ($host == '.') die('EOF');
        return $host;
    }

}


