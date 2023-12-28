<?php
function generateKey() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $key = 'mkb_';
    for ($i = 0; $i < 10; $i++) {
        $key .= $chars[rand(0, strlen($chars)-1)];
    }
    return $key;
}

function saveData($data) {
    $file = fopen("database.txt", "a");
    fwrite($file, $data . PHP_EOL);
    fclose($file);
}

if (isset($_POST['delete']) && isset($_POST['deleteUsername'])) {
    $referenceToDelete = $_POST['deleteUsername'];

    $fileContents = file("database.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $foundIndex = -1; 
    foreach ($fileContents as $index => $line) {
        $lineData = explode(',', $line);
        $savedKey = trim($lineData[0]); 
        $savedUsername = trim($lineData[1]); 

        if ($savedUsername == $referenceToDelete || $savedKey == $referenceToDelete) {
            $foundIndex = $index; 
            break;
        }
    }

    if ($foundIndex != -1) { 
        unset($fileContents[$foundIndex]); 

        // Save the data back to file
        file_put_contents("database.txt", implode(PHP_EOL, $fileContents));
        echo "Reference '$referenceToDelete' was deleted successfully.";
    } else { 
        echo "Reference: '$referenceToDelete' does not exist.";
    }
}

if (isset($_POST['check']) && isset($_POST['checkKey']) && isset($_POST['checkHwid'])) {
    $keyToCheck = $_POST['checkKey'];
    $hwidToCheck = $_POST['checkHwid'];

    $fileContents = file("database.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $isValid = false;

    foreach ($fileContents as $line) {
        $lineData = explode(',', $line);
        if (trim($lineData[0]) == $keyToCheck && trim($lineData[2]) == $hwidToCheck) {
            $isValid = true;
            break;
        }
    }

    if ($isValid) {
        echo "The key and HWID are valid.";
    } else {
        echo "Invalid key or HWID.";
    }
}

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $hwid = 'none'; 

    $dataExists = false;
    $file = fopen("database.txt", "r");
    while ($line = fgets($file)) {
        $line = trim($line);
        $lineData = explode(',', $line);
        $savedUsername = $lineData[1];
        
        if ($savedUsername == $username) {
            $dataExists = true;
            echo "Username already exists.";
            break;
        }
    }
    fclose($file);

    if (!$dataExists) {
        $key = generateKey();
        $data = $key . ',' . $username . ',' . $hwid;
        saveData($data);
        echo "Data added successfully. Your key is: $key";
    }
}

if (isset($_POST['updateHwid']) && isset($_POST['reference']) && isset($_POST['newHwid'])) {
    $referenceToUpdate = $_POST['reference'];
    $newHWID = $_POST['newHwid'];
    

    $fileContents = file("database.txt");
    $updatedContents = [];

    $referenceFound = false;

    foreach ($fileContents as $line) {
        $lineData = explode(',', $line);
        if (trim($lineData[0]) == $referenceToUpdate) {
            $referenceFound = true;
            $line = $referenceToUpdate . "," . $lineData[1] . "," . $newHWID . PHP_EOL;
        }
        $updatedContents[] = $line;
    }

    if ($referenceFound) {
        file_put_contents("database.txt", implode("", $updatedContents));
        echo "HWID updated successfully.";
    } else {
        echo "Reference not found.";
    }
}

$file = fopen("database.txt", "r");
echo "<h2>Database:</h2>";
while ($line = fgets($file)) {
    $line = trim($line);
    $lineData = explode(',', $line);
    $key = $lineData[0];
    $username = $lineData[1];
    $hwid = $lineData[2];
    
    // Добавляем onclick атрибут, чтобы вызвать функцию копирования ключа
    echo "<p class='key-display' onclick='copyKeyToClipboard(\"$key\")'>Key: $key</p>";
    echo "<p class='username-display'>Username: $username</p>";
    echo "<p class='hwid-display'>HWID: $hwid</p>";
    echo "<hr>";
}
fclose($file);
?>