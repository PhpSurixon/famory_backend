<?php
// File to check
$filename = 'example.txt'; // Replace with the file you want to check

// Check if the file exists
if (file_exists($filename)) {
    echo "File '$filename' is found on the server.";
} else {
    echo "File '$filename' is NOT found on the server.";
}
?>
