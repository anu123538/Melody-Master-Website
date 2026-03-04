<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access. Please log in.");
}

$u_id = $_SESSION['user_id']; 

// Check if product_id is passed in the URL
if (isset($_GET['product_id'])) {
    $p_id = mysqli_real_escape_string($conn, $_GET['product_id']);

    // 1. Fetch the file path from the digital_products table
    $query = "SELECT file_path FROM digital_products WHERE product_id = '$p_id'";
    $result = $conn->query($query);
    $digital = $result->fetch_assoc();

    if ($digital) {
        // 2. Check how many times THIS specific user has downloaded THIS product from the tracking table
        $check_query = "SELECT download_count FROM user_downloads WHERE user_id = '$u_id' AND product_id = '$p_id'";
        $check_result = $conn->query($check_query);
        $user_record = $check_result->fetch_assoc();

        $current_count = $user_record ? $user_record['download_count'] : 0;

        // 3. Check if the personal download limit (e.g., 5) has been reached
        if ($current_count < 5) {
            
            // 4. Update or Insert the download count in the tracking table
            if ($user_record) {
                // If record exists, increment the count
                $conn->query("UPDATE user_downloads SET download_count = download_count + 1 WHERE user_id = '$u_id' AND product_id = '$p_id'");
            } else {
                // If first time, create a new record for this user and product
                $conn->query("INSERT INTO user_downloads (user_id, product_id, download_count) VALUES ('$u_id', '$p_id', 1)");
            }

            // 5. Start the file download process
            $file_path = $digital['file_path'];
            if (file_exists($file_path)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                header('Content-Length: ' . filesize($file_path));
                
                // Clear output buffer and send file to browser
                flush(); 
                readfile($file_path);
                exit;
            } else {
                echo "Error: The file does not exist on the server.";
            }
        } else {
            // 6. Block download if this specific user has reached the limit
            echo "<script>alert('You have reached your download limit of 5 attempts.'); window.history.back();</script>";
        }
    } else {
        echo "Error: No digital record found for this product.";
    }
} else {
    echo "Invalid Request.";
}
?>