<?php
// Helper function for file uploads
function handleFileUpload($userId, $fileData, $targetDir = null)
{
    if ($targetDir === null) {
        // Default to the root 'uploads' directory
        $targetDir = dirname(dirname(dirname(__FILE__))) . '/uploads/';
    }

    if (!is_array($fileData) || !isset($fileData['name']) || $fileData['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $file_extension = pathinfo($fileData["name"], PATHINFO_EXTENSION);
    $unique_file_name = "resume_" . $userId . "_" . time() . "." . $file_extension;
    $target_file = $targetDir . $unique_file_name;

    $move_function = defined('TESTING_MODE') ? 'copy' : 'move_uploaded_file';

    if ($move_function($fileData["tmp_name"], $target_file)) {
        return $target_file;
    }

    return null;
}
