<?php
include "include.php";

$fileArray = get_all_files();
$str = "";
foreach ($fileArray as $file) {
    $xml = simplexml_load_file("files/" . $file);
    // echo print_r($xml);
    foreach ($xml->children() as $child) {
        echo $child->title . "<br>";
    }

    // preg_match_all("%(<title>)(.*?)(</title>)%", $fileStr, $titles);
    // $titles = $titles[2];
    // preg_match_all("%(</source><source-url>)(.*?)(</source-url>)%", $fileStr, $urls);
    // $urls = $urls[2];
    // preg_match_all("%(<created>)(.*?)(</created>)%", $fileStr, $created);
    // $created = $created[2];
    // $resultArray = array_combine($titles, $urls);

    // foreach ($resultArray as $title => $url) {
    // $str .= "TY  - ELEC" . PHP_EOL;
    // $str .= "TI  - " . $title . PHP_EOL;
    // $str .= "DA  - " . strftime("%Y") . PHP_EOL;
    // $str .= "UR  - " . $url . PHP_EOL;
    // $str .= "KW  - link" . PHP_EOL;
    // $str .= PHP_EOL;
    // }
    // $i = 0;
    // foreach ($resultArray as $title => $url) {
    // $str .= "TY  - ELEC" . "<br>";
    // $str .= "TI  - " . $title . "<br>";
    // $str .= "DA  - " . substr($created[$i], 0, 4) . "<br>";
    // $str .= "UR  - " . $url . "<br>";
    // $str .= "KW  - link" . "<br>";
    // $str .= "<br>";
    // $i++;
    // }

}
// echo $str;
// create_download($str);
// redirect("admin/upload.php");
?>

