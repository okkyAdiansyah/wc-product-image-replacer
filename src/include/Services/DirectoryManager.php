<?php
/**
 * Manage directory
 * 
 * @package WC Product Image Replacer
 */
namespace WPIR\Services;

if( ! defined( 'ABSPATH' ) ){
    exit;
}

use \ZipArchive;

class DirectoryManager{
    /**
     * @var ZipArchive $zip PHP Zip functionalities
     * @var array $upload_dir Wordpress default upload directory
     * @var string $wpir_upload_dir Plugin base uploaded directory
     * @var string $wpir_download_dir Plugin base download directory
     * @var array $uploaded_file File received from $_FILES handler
     */
    public $zip;
    public $upload_dir;
    public $wpir_upload_dir;
    public $wpir_download_dir;
    public $uploaded_file;

    public function __construct($files) {
        $this->uploaded_file = $files;
        $this->upload_dir = wp_upload_dir(  );
        $this->wpir_upload_dir = rtrim( $this->upload_dir['basedir'], '/' ) . '/' . 'wpir_upload';
        $this->wpir_download_dir = rtrim( $this->upload_dir['basedir'], '/' ) . '/' . 'wpir_download';

        if( ! class_exists( 'ZipArchive' ) ){
            error_log( 'PHP ZipArchive extension is not installed' );
        }else{
            $this->zip = new \ZipArchive();
        }
    }

    /**
     * Ajax handler for moving uploaded file to plugin directory and extract it
     * 
     * @return void
     */
    public function wpir_zip_move_and_extract(){
        $files = $this->uploaded_file;
        $zip_path_destination = $this->upload_dir['path'] . '/' . $files['name'];
    
        // Log the destination path
        error_log('Zip Path Destination: ' . $zip_path_destination);
    
        // Check if the temporary file exists
        if (!file_exists($files['tmp_name'])) {
            wp_send_json_error('Temporary file not found: ' . $files['tmp_name'], 500);
        }
    
        // Attempt to move the uploaded file
        if (!move_uploaded_file($files['tmp_name'], $zip_path_destination)) {
            error_log('Failed to move file from ' . $files['tmp_name'] . ' to ' . $zip_path_destination);
            wp_send_json_error('Failed to move file', 500);
        }
    
        // Check if the moved file exists
        if (!file_exists($zip_path_destination)) {
            wp_send_json_error('File not found at destination: ' . $zip_path_destination, 500);
        }
    
        // Attempt to open the zip file
        $result = $this->zip->open($zip_path_destination);
        if ($result !== true) {
            $errorMessages = [
                \ZipArchive::ER_NOENT => 'No such file or directory.',
                \ZipArchive::ER_NOZIP => 'Not a zip archive.',
                \ZipArchive::ER_INCONS => 'Zip archive inconsistent.',
                \ZipArchive::ER_CRC => 'CRC error.',
            ];
            $errorMessage = isset($errorMessages[$result]) ? $errorMessages[$result] : 'Unknown error.';
            wp_send_json_error('Failed to open zip file: ' . $errorMessage, 500);
        }
    
        // Extract the zip contents
        if (!$this->zip->extractTo($this->wpir_upload_dir)) {
            wp_send_json_error('Failed to extract zip file.', 500);
        }
        $this->zip->close();
    
        return true;
    }
}