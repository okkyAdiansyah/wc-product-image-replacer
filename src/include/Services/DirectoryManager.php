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
            $this->$zip = new \ZipArchive();
        }

        add_action( 'wp_ajax_handle_uploaded_file', array( $this, 'wpir_handle_uploaded_file' ) );
    }

    /**
     * Ajax handler for moving uploaded file to plugin directory and extract it
     * 
     * @return void
     */
    public function wpir_zip_move_and_extract(){
        $files = $this->uploaded_file;
        $zip_path_destination = $this->upload_dir['path'] . '/' . $files['name'];

        move_uploaded_file( $files['temp_name'], $zip_path_destination );
        if( $this->zip->open( $zip_path_destination ) !== true ){
            wp_send_json_error( 'Failed to open zip file', 500 );
        }
        $this->zip->extract_to( $this->wpir_upload_dir );
        $this->zip->close(  );

        return true;
    }
}