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
use \DirectoryIterator;
use WPIR\WPIR_Dirs_Response;

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
     * Handler for moving uploaded file to plugin directory and extract it
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
    
        // Extract files manually while removing the first-level directory
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $zip_entry = $this->zip->getNameIndex($i);

            // Skip directories and hidden files
            if (substr($zip_entry, -1) === '/' || strpos(basename($zip_entry), '.') === 0) {
                continue;
            }

            // Remove the first-level directory from the path
            $path_parts = explode('/', $zip_entry);
            array_shift($path_parts); // Remove the first-level directory
            $relative_path = implode('/', $path_parts);

            // Build the destination path inside wpir_upload_dir
            $dest_path = $this->wpir_upload_dir . '/' . $relative_path;

            // Ensure the directory exists
            if (!file_exists(dirname($dest_path))) {
                mkdir(dirname($dest_path), 0755, true);
            }

            // Write the extracted file to the destination
            $file_content = $this->zip->getFromIndex($i);
            if (!file_put_contents($dest_path, $file_content)) {
                wp_send_json_error('Failed to extract file: ' . $relative_path, 500);
            }
        }
        $this->zip->close();
    }

    /**
     * Get file content based on parent dir name
     * 
     * @param string $dir_path Targeted dir path
     * 
     * @return array Name of the directory
     */
    private function wpir_get_dir_content( $dir_path ){
        $dir_contents = array();
        $dir_entries = new DirectoryIterator( $dir_path ); // Directory entry to iterate the content
        
        foreach( $dir_entries as $entry ){

            if( $entry->isDot() ){
                continue; // Skip '.' and '..' directory
            }

            $dir_contents[] = $entry->getFilename();
        }

        return $dir_contents;
    }

    /**
     * Verified uploaded file content
     * 
     * @return void
     */
    public function wpir_verified_file_content(){
        $contents = array();

        // Get all directory name from plugin upload directory
        $parent_path = $this->wpir_upload_dir;
        $parent_dirs = $this->wpir_get_dir_content( $parent_path );

        // Create new object for each directory
        foreach( $parent_dirs as $parent_dir ){
            $dir = new WPIR_Dirs_Response( $parent_dir );
            
            // Get all first level subdirectories
            $first_subdir_path = trailingslashit( $parent_path ) . $parent_dir;
            $first_subdirs = $this->wpir_get_dir_content( $first_subdir_path );
            
            // Verify if expected directories is exist
            if( in_array( 'main', $first_subdirs ) ){
                $dir->wpir_exp_dir_exist( 'main', true );
                $main_img_dir_path = trailingslashit( $first_subdir_path ) . 'main';

                // Get image file from main dir
                $main_image = $this->wpir_get_dir_content( $main_img_dir_path );
                $dir->wpir_assign_filename( 'main', $main_image );
            }
            if( in_array( 'gallery', $first_subdirs ) ){
                $dir->wpir_exp_dir_exist( 'gallery', true );
                $gallery_img_dir_path = trailingslashit( $first_subdir_path ) . 'gallery';

                
                // Get image file from gallery dir
                $gallery_image = $this->wpir_get_dir_content( $gallery_img_dir_path );
                $dir->wpir_assign_filename( 'gallery', $gallery_image );
            }

            // Push dir object to content container
            $contents[] = $dir;
        }

        wp_send_json_success( $contents, 200 );
    }
}