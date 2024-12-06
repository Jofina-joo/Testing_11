
<?php
session_start(); // start session
error_reporting(0); // The error reporting function

extract($_REQUEST); // Extract the request

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the file data is received
    if (isset($_FILES['valid_numbers']) && $_FILES['valid_numbers']['error'] === UPLOAD_ERR_OK) {
        // Get the sanitized file name
        $filename = isset($_POST['filename']) ? basename($_POST['filename']) : ''; // Sanitize the file name

        // Set the desired location
        $location = 'uploads/compose_variables/' . $filename;

        // Move the uploaded file to the specified location
        if (move_uploaded_file($_FILES['valid_numbers']['tmp_name'], $location)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to move the uploaded file']);
        }
    } else {
        echo json_encode(['error' => 'No file uploaded or an error occurred during upload']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>

