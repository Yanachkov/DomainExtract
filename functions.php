<?php

function logToFile($content)
{
    $file = "log.txt";
    $file_content = date("M-d") . " - " . date("h:i:s") . " |>>>  " . $content . "\n";
    $file_content .= file_get_contents($file);
    file_put_contents($file, $file_content);
}

function LoadURLmap($sldFile)
{
    $csvData = file_get_contents($sldFile);
    $urlMap = explode(PHP_EOL, $csvData);
    return $urlMap;
}

function ExtractRootDomain($url, $urlMap)
{
    $host = "";
    $url = trim($url);
    $url = strtolower($url);
    if ($url == "") {
        return "ERORR" . ";" . "empty line";
    }

    $urlData = parse_url($url);
    $hostData = explode('.', $urlData['host']);
    $hostData = array_reverse($hostData);

    if (array_search($hostData[1] . '.' . $hostData[0], $urlMap) !== FALSE) {
        if (isset($hostData[2])) {
            $host = $hostData[2] . '.' . $hostData[1] . '.' . $hostData[0];

        } else {
            return "ERORR" . ";" . "Domain missing";
        }
    } elseif (array_search($hostData[0], $urlMap) !== FALSE) {
        $host = $hostData[1] . '.' . $hostData[0];
    }
    if ($host == "") {
        return $url . ";" . "TLD missing";
    } else {
        return $host;
    }
}

function removeDuplicate($file)
{

    $file_1 = file_get_contents('temp.csv');
    $file_2 = file_get_contents('Result.csv');
    file_put_contents('Result.csv', $file_1 . $file_2);

// array to hold all unique lines
    //   $lines = array();

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