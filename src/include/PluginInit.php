<?php
/**
 * Plugin init class
 * 
 * @package  WC Product Image Replacer
 */
namespace WPIR;

if( ! defined( "ABSPATH" ) ){
    exit;
}

use \ZipArchive;
use WPIR\Services\DirectoryManager;
use WPIR\Services\ProductManager;

class PluginInit{
    /**
     * Plugin initiation
     * 
     * @return void
     */
    public static function wpir_init(){
        add_action( 'admin_menu', array( self::class, 'wpir_register_menu' ), 10 );
        add_action( 'wp_ajax_handle_zip_verification', array( self::class, 'wpir_handle_zip_verification' ) );
        add_action( 'admin_enqueue_scripts', array( self::class, 'wpir_enqueue_scripts' ), 10 );
    }

    /**
     * Plugin activation hook
     * 
     * @return void
     */
    public static function wpir_activate_plugin(){
        /**
         * @var array $wp_upload_dir Wordpress upload directory
         * @var string $wpir_replace_image_dir Define plugin new upload directory
         */
        $wp_upload_dir = wp_get_upload_dir(  );
        $wpir_upload_dir = trailingslashit( $wp_upload_dir['basedir'] ) . 'wpir_upload';
        $wpir_download_dir = trailingslashit( $wp_upload_dir['basedir'] ) . 'wpir_download';

        // Check if plugin replace image directory is not exists
        // If true, create new directory
        if( ! file_exists( $wpir_upload_dir ) ){
            if( ! mkdir( $wpir_upload_dir, 0755, true ) ){
                wp_die( 
                    __( 'Failed to create the "Replace Image" directory in wpir_activate_plugin.', 'wpir' )
                );
            }
            if( ! mkdir( $wpir_download_dir, 0755, true ) ){
                wp_die( 
                    __( 'Failed to create the "Replace Image" directory in wpir_activate_plugin.', 'wpir' )
                );
            }
        }

        flush_rewrite_rules(  );
    }

    /**
     * Plugin deactivaion hook
     * 
     * @return void
     */
    public static function wpir_deactivate_plugin(){
        /**
         * @var array $wp_upload_dir Wordpress upload directory
         * @var string $wpir_replace_image_dir Define plugin new upload directory
         */
        $wp_upload_dir = wp_get_upload_dir(  );
        $wpir_upload_dir = trailingslashit( $wp_upload_dir['basedir'] ) . 'wpir_upload';
        $wpir_download_dir = trailingslashit( $wp_upload_dir['basedir'] ) . 'wpir_download';

        // Check if plugin replace image directory is exists
        // If true, remove directory
        if( file_exists( $wpir_upload_dir ) ){
            rmdir( $wpir_upload_dir );
        }
        if( file_exists( $wpir_download_dir ) ){
            rmdir( $wpir_download_dir );
        }
    }

    /**
     * Register plugin main menu
     * 
     * @return void
     */
    public static function wpir_register_menu(){
        add_menu_page( 
            __( 'WPIR', 'wpir' ), 
            __( 'WPIR', 'wpir' ), 
            'manage_options', 
            'wpir', 
            array( self::class, 'wpir_render_main_admin_page' ), 
            'dashicons-format-gallery', 
            20
        );
    }

    /**
     * Render main admin page
     * 
     * @return void
     */
    public static function wpir_render_main_admin_page(){
        require plugin_dir_path( dirname( __FILE__, 2 ) ) . 'src/admin/main-admin.php';
    }

    /**
     * Plugin enqueue script
     * 
     * @return void
     */
    public static function wpir_enqueue_scripts(){
        wp_enqueue_script( 
            'wpir-main-script', 
            plugin_dir_url( dirname( __FILE__, 1 ) ) . 'assets/scripts/wpir-admin.js',
            array( 'jquery' ), 
            false
        );

        wp_enqueue_style( 
            'wpir-main-style', 
            plugin_dir_url( dirname( __FILE__, 1 ) ) . 'assets/styles/wpir-main-styles.css' 
        );

        wp_localize_script( 
            'wpir-main-script', 
            'wpir_ajax_object',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'wpir_uploading_nonce' => wp_create_nonce( 'wpir_uploading_nonce' ),
                'wpir_replace_image_nonce' => wp_create_nonce( 'wpir_replace_image_nonce' ),
                'wpir_download_backup_nonce' => wp_create_nonce( 'wpir_download_backup_nonce' )
            ) 
        );
    }

    /**
     * Handle ajax zip file verification process
     * 
     * @return void
     */
    public static function wpir_handle_zip_verification(){
        check_ajax_referer( 'wpir_uploading_nonce', 'wpir_nonce');

        $dir_manager = new DirectoryManager( $_FILES['images'] );
        $dir_manager->wpir_zip_move_and_extract();

        $dir_manager->wpir_verified_file_content();
    }
}
