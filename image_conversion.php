<?php

$uploadDir = 'uploads/';
$response = ['files' => []];

// Create the upload directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Check if files were uploaded
if (isset($_FILES['files'])) {
    $uploadedFiles = $_FILES['files'];

    // Get the background color from the request
    $backgroundColor = isset($_POST['background_color']) ? $_POST['background_color'] : '245,245,245';
    list($r, $g, $b) = array_map('intval', explode(',', $backgroundColor));

    // Iterate over each uploaded file
    for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
        $fileTmpPath = $uploadedFiles['tmp_name'][$i];
        $fileName = $uploadedFiles['name'][$i];
        $fileNameCmps = explode('.', $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Validate file extension
        if ($fileExtension != 'png') {
            continue;
        }

        // Generate a unique name for the file
        $newFileName = $fileNameCmps[0] . '-converted.jpeg';
        $destPath = $uploadDir . $newFileName;

        // Process and convert the PNG to JPEG
        try {
            $image = imagecreatefrompng($fileTmpPath);
            if (!$image) {
                continue;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            // Create a true color image with the specified background color
            $jpegImage = imagecreatetruecolor($width, $height);
            $backgroundColorAllocated = imagecolorallocate($jpegImage, $r, $g, $b);
            imagefill($jpegImage, 0, 0, $backgroundColorAllocated);

            imagecopy($jpegImage, $image, 0, 0, 0, 0, $width, $height);
            imagejpeg($jpegImage, $destPath, 75);

            imagedestroy($image);
            imagedestroy($jpegImage);

            // Return the file path
            $response['files'][] = [
                'name' => $newFileName,
                'url'  => $uploadDir . $newFileName
            ];

        } catch (Exception $e) {
            continue;
        }
    }
}

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
