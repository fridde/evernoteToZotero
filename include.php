<?php
$USER = "guest";
$PASSWORD = "password";
$CANCEL_TEXT = 'Sorry, but I don\'t want bots to enter. Contact me at Github or otherwise for a password!';

/*
 Easy PHP Upload - version 2.29
 A easy to use class for your (multiple) file uploads

 Copyright (c) 2004 - 2006, Olaf Lederer
 All rights reserved.

 Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * Neither the name of the finalwebsites.com nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

 THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

 ______________________________________________________________________
 available at http://www.finalwebsites.com
 Comments & suggestions: http://www.finalwebsites.com/contact.php
 */

class file_upload {

    var $the_file;
    var $the_temp_file;
    var $upload_dir;
    var $replace;
    var $do_filename_check = "n";
    var $max_length_filename = 100;
    var $extensions;
    var $ext_string;
    var $language;
    var $http_error;
    var $rename_file;
    // if this var is true the file copy get a new name
    var $file_copy;
    // the new name
    var $message = array();
    var $create_directory = true;

    function file_upload() {
        $this -> language = "en";
        // choice of en, nl, es
        $this -> rename_file = false;
        $this -> ext_string = "";
    }

    function show_error_string() {
        $msg_string = "";
        foreach ($this->message as $value) {
            $msg_string .= $value . "<br>\n";
        }
        return $msg_string;
    }

    function set_file_name($new_name = "") {// this "conversion" is used for unique/new filenames
        if ($this -> rename_file) {
            if ($this -> the_file == "")
                return;
            $name = ($new_name == "") ? strtotime("now") : $new_name;
            $name = $name . $this -> get_extension($this -> the_file);
        }
        else {
            $name = $this -> the_file;
        }
        return $name;
    }

    function upload($to_name = "") {
        $new_name = $this -> set_file_name($to_name);
        if ($this -> check_file_name($new_name)) {
            if ($this -> validateExtension()) {
                if (is_uploaded_file($this -> the_temp_file)) {
                    $this -> file_copy = $new_name;
                    if ($this -> move_upload($this -> the_temp_file, $this -> file_copy)) {
                        $this -> message[] = $this -> error_text($this -> http_error);
                        if ($this -> rename_file)
                            $this -> message[] = $this -> error_text(16);
                        return true;
                    }
                }
                else {
                    $this -> message[] = $this -> error_text($this -> http_error);
                    return false;
                }
            }
            else {
                $this -> show_extensions();
                $this -> message[] = $this -> error_text(11);
                return false;
            }
        }
        else {
            return false;
        }
    }

    function check_file_name($the_name) {
        if ($the_name != "") {
            if (strlen($the_name) > $this -> max_length_filename) {
                $this -> message[] = $this -> error_text(13);
                return false;
            }
            else {
                if ($this -> do_filename_check == "y") {
                    if (preg_match("/^[a-z0-9_]*\.(.){1,5}$/i", $the_name)) {
                        return true;
                    }
                    else {
                        $this -> message[] = $this -> error_text(12);
                        return false;
                    }
                }
                else {
                    return true;
                }
            }
        }
        else {
            $this -> message[] = $this -> error_text(10);
            return false;
        }
    }

    function get_extension($from_file) {
        $ext = strtolower(strrchr($from_file, "."));
        return $ext;
    }

    function validateExtension() {
        $extension = $this -> get_extension($this -> the_file);
        $ext_array = $this -> extensions;
        if (in_array($extension, $ext_array)) {
            // check mime type hier too against allowed/restricted mime types (boolean check mimetype)
            return true;
        }
        else {
            return false;
        }
    }

    // this method is only used for detailed error reporting
    function show_extensions() {
        $this -> ext_string = implode(" ", $this -> extensions);
    }

    function move_upload($tmp_file, $new_file) {
        umask(0);
        if ($this -> existing_file($new_file)) {
            $newfile = $this -> upload_dir . $new_file;
            if ($this -> check_dir($this -> upload_dir)) {
                if (move_uploaded_file($tmp_file, $newfile)) {
                    if ($this -> replace == "y") {
                        //system("chmod 0777 $newfile"); // maybe you need to use the system command in some cases...
                        chmod($newfile, 0777);
                    }
                    else {
                        // system("chmod 0755 $newfile");
                        chmod($newfile, 0755);
                    }
                    return true;
                }
                else {
                    return false;
                }
            }
            else {
                $this -> message[] = $this -> error_text(14);
                return false;
            }
        }
        else {
            $this -> message[] = $this -> error_text(15);
            return false;
        }
    }

    function check_dir($directory) {
        if (!is_dir($directory)) {
            if ($this -> create_directory) {
                umask(0);
                mkdir($directory, 0777);
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return true;
        }
    }

    function existing_file($file_name) {
        if ($this -> replace == "y") {
            return true;
        }
        else {
            if (file_exists($this -> upload_dir . $file_name)) {
                return false;
            }
            else {
                return true;
            }
        }
    }

    function get_uploaded_file_info($name) {
        $str = "File name: " . basename($name) . "\n";
        $str .= "File size: " . filesize($name) . " bytes\n";
        if (function_exists("mime_content_type")) {
            $str .= "Mime type: " . mime_content_type($name) . "\n";
        }
        if ($img_dim = getimagesize($name)) {
            $str .= "Image dimensions: x = " . $img_dim[0] . "px, y = " . $img_dim[1] . "px\n";
        }
        return $str;
    }

    // this method was first located inside the foto_upload extension
    function del_temp_file($file) {
        $delete = @unlink($file);
        clearstatcache();
        if (@file_exists($file)) {
            $filesys = eregi_replace("/", "\\", $file);
            $delete = @system("del $filesys");
            clearstatcache();
            if (@file_exists($file)) {
                $delete = @chmod($file, 0775);
                $delete = @unlink($file);
                $delete = @system("del $filesys");
            }
        }
    }

    // some error (HTTP)reporting, change the messages or remove options if you like.
    function error_text($err_num) {
        switch ($this->language) {
            case "nl" :
                $error[0] = "Foto succesvol kopieert.";
                $error[1] = "Het bestand is te groot, controlleer de max. toegelaten bestandsgrootte.";
                $error[2] = "Het bestand is te groot, controlleer de max. toegelaten bestandsgrootte.";
                $error[3] = "Fout bij het uploaden, probeer het nog een keer.";
                $error[4] = "Fout bij het uploaden, probeer het nog een keer.";
                $error[10] = "Selecteer een bestand.";
                $error[11] = "Het zijn alleen bestanden van dit type toegestaan: <b>" . $this -> ext_string . "</b>";
                $error[12] = "Sorry, de bestandsnaam bevat tekens die niet zijn toegestaan. Gebruik alleen nummer, letters en het underscore teken. <br>Een geldige naam eindigt met een punt en de extensie.";
                $error[13] = "De bestandsnaam is te lang, het maximum is: " . $this -> max_length_filename . " teken.";
                $error[14] = "Sorry, het opgegeven directory bestaat niet!";
                $error[15] = "Uploading <b>" . $this -> the_file . "...Fout!</b> Sorry, er is al een bestand met deze naam aanwezig.";
                $error[16] = "Het gekopieerde bestand is hernoemd naar <b>" . $this -> file_copy . "</b>.";
                break;
            case "de" :
                $error[0] = "Die Datei: <b>" . $this -> the_file . "</b> wurde hochgeladen!";
                $error[1] = "Die hochzuladende Datei ist gr&ouml;&szlig;er als der Wert in der Server-Konfiguration!";
                $error[2] = "Die hochzuladende Datei ist gr&ouml;&szlig;er als der Wert in der Klassen-Konfiguration!";
                $error[3] = "Die hochzuladende Datei wurde nur teilweise &uuml;bertragen";
                $error[4] = "Es wurde keine Datei hochgeladen";
                $error[10] = "W&auml;hlen Sie eine Datei aus!.";
                $error[11] = "Es sind nur Dateien mit folgenden Endungen erlaubt: <b>" . $this -> ext_string . "</b>";
                $error[12] = "Der Dateiname enth&auml;lt ung&uuml;ltige Zeichen. Benutzen Sie nur alphanumerische Zeichen f&uuml;r den Dateinamen mit Unterstrich. <br>Ein g&uuml;ltiger Dateiname endet mit einem Punkt, gefolgt von der Endung.";
                $error[13] = "Der Dateiname &uuml;berschreitet die maximale Anzahl von " . $this -> max_length_filename . " Zeichen.";
                $error[14] = "Das Upload-Verzeichnis existiert nicht!";
                $error[15] = "Upload <b>" . $this -> the_file . "...Fehler!</b> Eine Datei mit gleichem Dateinamen existiert bereits.";
                $error[16] = "Die hochgeladene Datei ist umbenannt in <b>" . $this -> file_copy . "</b>.";
                break;
            //
            // place here the translations (if you need) from the directory "add_translations"
            //
            default :
                // start http errors
                $error[0] = "File: <b>" . $this -> the_file . "</b> successfully uploaded!";
                $error[1] = "The uploaded file exceeds the max. upload filesize directive in the server configuration.";
                $error[2] = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form.";
                $error[3] = "The uploaded file was only partially uploaded";
                $error[4] = "No file was uploaded";
                // end  http errors
                $error[10] = "Please select a file for upload.";
                $error[11] = "Only files with the following extensions are allowed: <b>" . $this -> ext_string . "</b>";
                $error[12] = "Sorry, the filename contains invalid characters. Use only alphanumerical chars and separate parts of the name (if needed) with an underscore. <br>A valid filename ends with one dot followed by the extension.";
                $error[13] = "The filename exceeds the maximum length of " . $this -> max_length_filename . " characters.";
                $error[14] = "Sorry, the upload directory doesn't exist!";
                $error[15] = "Uploading <b>" . $this -> the_file . "...Error!</b> Sorry, a file with this name already exitst.";
                $error[16] = "The uploaded file is renamed to <b>" . $this -> file_copy . "</b>.";
        }
        return $error[$err_num];
    }

}

//error_reporting(E_ALL);
$max_size = 1024 * 100 * 100;
// the max. size for uploading

class multi_files extends file_upload {

    var $number_of_files = 0;
    var $names_array;
    var $tmp_names_array;
    var $error_array;
    var $wrong_extensions = 0;
    var $bad_filenames = 0;

    function extra_text($msg_num) {
        switch ($this->language) {
            case "de" :
                // add you translations here
                break;
            default :
                $extra_msg[1] = "Error for: <b>" . $this -> the_file . "</b>";
                $extra_msg[2] = "You have tried to upload " . $this -> wrong_extensions . " files with a bad extension, the following extensions are allowed: <b>" . $this -> ext_string . "</b>";
                $extra_msg[3] = "Select at least on file.";
                $extra_msg[4] = "Select the file(s) for upload.";
                $extra_msg[5] = "You have tried to upload <b>" . $this -> bad_filenames . " files</b> with invalid characters inside the filename.";
        }
        return $extra_msg[$msg_num];
    }

    // this method checkes the number of files for upload
    // this example works with one or more files
    function count_files() {
        foreach ($this->names_array as $test) {
            if ($test != "") {
                $this -> number_of_files++;
            }
        }
        if ($this -> number_of_files > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    function upload_multi_files() {
        $this -> message = "";
        if ($this -> count_files()) {
            foreach ($this->names_array as $key => $value) {
                if ($value != "") {
                    $this -> the_file = $value;
                    $new_name = $this -> set_file_name();
                    if ($this -> check_file_name($new_name)) {
                        if ($this -> validateExtension()) {
                            $this -> file_copy = $new_name;
                            $this -> the_temp_file = $this -> tmp_names_array[$key];
                            if (is_uploaded_file($this -> the_temp_file)) {
                                if ($this -> move_upload($this -> the_temp_file, $this -> file_copy)) {
                                    $this -> message[] = $this -> error_text($this -> error_array[$key]);
                                    if ($this -> rename_file)
                                        $this -> message[] = $this -> error_text(16);
                                    sleep(1);
                                    // wait a seconds to get an new timestamp (if rename is set)
                                }
                            }
                            else {
                                $this -> message[] = $this -> extra_text(1);
                                $this -> message[] = $this -> error_text($this -> error_array[$key]);
                            }
                        }
                        else {
                            $this -> wrong_extensions++;
                        }
                    }
                    else {
                        $this -> bad_filenames++;
                    }
                }
            }
            if ($this -> bad_filenames > 0)
                $this -> message[] = $this -> extra_text(5);
            if ($this -> wrong_extensions > 0) {
                $this -> show_extensions();
                $this -> message[] = $this -> extra_text(2);
            }
        }
        else {
            $this -> message[] = $this -> extra_text(3);
        }
    }

}

$multi_upload = new multi_files;

$multi_upload -> upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/evernoteToZotero/files/";
// "files" is the folder for the uploaded files (you have to create this folder)
$multi_upload -> extensions = array(
    ".png",
    ".zip",
    ".txt",
    ".enex"
);
// specify the allowed extensions here
$multi_upload -> message[] = $multi_upload -> extra_text(4);
// a different standard message for multiple files
//$multi_upload->rename_file = true; // set to "true" if you want to rename all files with a timestamp value
$multi_upload -> do_filename_check = "n";
// check filename ...

if (isset($_POST['Submit'])) {
    $multi_upload -> tmp_names_array = $_FILES['upload']['tmp_name'];
    $multi_upload -> names_array = $_FILES['upload']['name'];
    $multi_upload -> error_array = $_FILES['upload']['error'];
    $multi_upload -> replace = (isset($_POST['replace'])) ? $_POST['replace'] : "n";
    // because only a checked checkboxes is true
    $multi_upload -> upload_multi_files();
}

function filter_words($wordArray, $rules) {

    foreach ($rules as $rule) {
        $rule = array_map("trim", explode("=", $rule));
        //echo print_r($rule) . "<br>";
        $newArray = array();
        switch ($rule[0]) {

            case 'Case_insensitive' :
                if ($rule[1]) {
                    foreach ($wordArray as $word => $occurrences) {
                        if ($word === strtolower($word)) {
                            if (isset($wordArray[ucwords($word)])) {
                                $newArray[$word] = $wordArray[ucwords($word)] + $occurrences;
                            }
                            else {
                                $newArray[$word] = $occurrences;
                            }
                        }
                    }
                    arsort($newArray);
                    $wordArray = $newArray;
                }
                break;

            case "higher_than" :
                foreach ($wordArray as $word => $occurrences) {
                    if ($occurrences > $rule[1]) {
                        $newArray[$word] = $occurrences;
                    }
                }
                $wordArray = $newArray;
                break;

            case "exclude" :
                $wordsToExclude = array_map("trim", explode(",", $rule[1]));

                foreach ($wordArray as $word => $occurrences) {
                    if (!(in_array($word, $wordsToExclude))) {
                        $newArray[$word] = $occurrences;
                    }
                }
                $wordArray = $newArray;
                break;

            case "longer_than" :
                foreach ($wordArray as $word => $occurrences) {
                    if (strlen($word) > $rule[1]) {
                        $newArray[$word] = $occurrences;
                    }
                }
                $wordArray = $newArray;

                break;

            case "max" :
                $i = 0;
                foreach ($wordArray as $word => $occurrences) {
                    if ($i < $rule[1]) {
                        $newArray[$word] = $occurrences;
                    }
                    $i++;
                }
                $wordArray = $newArray;
                break;
        }
    }
    //echo print_r($wordArray) . "<br>";
    return $wordArray;
}

function calculate_frequencies($wordArray) {
    foreach ($wordArray as $fileName => $content) {
        $wordCount = $content["wordCount"];
        $frequencyArray = $content["frequencies"];
        foreach ($frequencyArray as $word => $occurrences) {
            $frequencyArray[$word] = round(($occurrences / $wordCount) * 100, 2);
        }
        $wordArray[$fileName]["frequencies"] = $frequencyArray;
    }
    return $wordArray;
}

function sort_according_to($wordArray, $name) {

    $normArray = array($name => $wordArray[$name]);
    $otherArrays = array_diff_key($wordArray, $normArray);

    foreach ($otherArrays as $file => $frequencies) {
        $frequencies = $frequencies["frequencies"];
        $newArray = array();

        foreach ($normArray[$name]["frequencies"] as $word => $frequency) {
            if (isset($frequencies[$word])) {
                $newArray[$word] = $frequencies[$word];
            }
            else {
                $newArray[$word] = NULL;
            }
        }
        $otherArrays[$file]["frequencies"] = $newArray;

    }
    $returnArray = array_merge($normArray, $otherArrays);
    return $returnArray;
}

function rectify_wordArray($wordArray) {
    $allWords = array();
    foreach ($wordArray as $file => $array) {
        $frequencies = $array["frequencies"];
        $allWords = array_merge($allWords, array_keys($frequencies));
    }
    $allWords = array_unique($allWords);

    foreach ($wordArray as $file => $array) {
        $frequencies = $array["frequencies"];
        $newArray = array();
        foreach ($allWords as $word) {
            if (isset($frequencys[$word])) {
                $newArray[$word] = $frequencys[$word];
            }
            else {
                $newArray[$word] = NULL;
            }
            $wordArray[$file] = $newArray;
        }
    }

    return $wordArray;
}

function redirect($to) {
    @session_write_close();
    if (!headers_sent()) {
        header("Location: $to");
        flush();
        exit();
    }
    else {
        print "<html><head><META http-equiv='refresh' content='0;URL=$to'></head><body><a href='$to'>$to</a></body></html>";
        flush();
        exit();
    }
}

function create_rules_from_ini($ini_array) {

    $rulesOptions = array(
        "Case_insensitive",
        "higher_than",
        "exclude",
        "longer_than",
        "max"
    );

    $rulesArray = array();
    foreach ($ini_array as $key => $value) {
        if (in_array($key, $rulesOptions)) {
            $rulesArray[] = $key . " = " . $value;
        }
    }
    return $rulesArray;
}

function create_download($source, $filename = "export.ris") {
    
    $f = fopen('php://memory', 'w+');
    fwrite($f, $source);
    fseek($f, 0);

    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    // make php send the generated lines to the browser
    fpassthru($f);
}

function get_all_files($dir = 'files') {
    $fileArray = array();
    $handle = opendir($dir);

    while (false !== ($entry = readdir($handle))) {
        if (!in_array($entry, array(
            ".",
            ".."
        ))) {
            $fileArray[] = $entry;
        }
    }
    closedir($handle);
    sort($fileArray);

    return $fileArray;
}
?>