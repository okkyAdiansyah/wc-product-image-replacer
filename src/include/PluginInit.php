<?php
/**
 * Plugin init class
 * 
 * @package  WC Product Image Replacer
 */
namespace WPIR;
use \ZipArchive;

if( ! defined( "ABSPATH" ) ){
    exit;
}

class PluginInit{
    /**
     * Plugin initiation
     * 
     * @return void
     */
    public static function wpir_init(){
        add_action( 'admin_menu', array( self::class, 'wpir_register_menu' ), 10 );
        add_action( 'wp_ajax_replace_images', array( self::class, 'wpir_handle_image_replacement' ) );
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
        $wpir_replace_image_dir = trailingslashit( $wp_upload_dir['basedir'] ) . 'replace_image';

        // Check if plugin replace image directory is not exists
        // If true, create new directory
        if( ! file_exists( $wpir_replace_image_dir ) ){
            if( ! mkdir( $wpir_replace_image_dir, 0755, true ) ){
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
        $wpir_replace_image_dir = trailingslashit( $wp_upload_dir['basedir'] ) . 'replace_image';

        // Check if plugin replace image directory is exists
        // If true, remove directory
        if( file_exists( $wpir_replace_image_dir ) ){
            rmdir( $wpir_replace_image_dir );
        }

    }

    /**
     * Register plugin main menu
     * 
     * @return void
     */
    public static function wpir_register_menu(){
        add_menu_page( 
            __( 'WC Product Image Replacer', 'wpir' ), 
            __( 'WC Product Image Replacer', 'wpir' ), 
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
        ?>
            <div class="wrap">
                <h1>Product Image Manager</h1>
                <form id="product-image-manager-form" method="post" enctype="multipart/form-data">
                    <label for="product_ids">Enter Product IDs (comma-separated):</label>
                    <input type="text" id="product_ids" name="product_ids" required>
                    
                    <label for="images">Upload Images (Zip file containing folders for each product ID):</label>
                    <input type="file" id="images" name="images" accept=".zip" required>
                    
                    <?php wp_nonce_field('replace_images', 'wpir_nonce'); ?>
                    
                    <button type="submit">Replace Images</button>
                </form>
            </div>
            <script>
                jQuery('#product-image-manager-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        processData: false,
                        contentType: false,
                        data: formData,
                        success: function(response) {
                            alert(response.data);
                        },
                        error: function(error) {
                            alert('An error occurred: ' + error.responseText);
                        }
                    });
                });
            </script>
        <?php
    }

    /**
     * Ajax handler for image replacement
     * 
     * @return void
     */
    public static function wpir_handle_image_replacement(){
        // Check ajax handler and nonce
        check_ajax_referer( 'replace_images', 'wpir_nonce' );

        $uploaded_file = $_FILES['images'];

        // Check if ZipArchive exists
        if( ! class_exists( 'ZipArchive' ) ){
            wp_send_json( 'PHP ZipArchive extension is not installed.', 500);
            error_log( 'PHP ZipArchive extension is not installed' );
        }

        $zip = new \ZipArchive();
        $wp_upload_dir = wp_upload_dir();
        $wpir_replace_image_dir = $wp_upload_dir['basedir'] . '/replace_image';
        $zip_path = $wp_upload_dir['path'] . '/' . $uploaded_file['name'];

        // Move uploaded file to zip path
        move_uploaded_file( $uploaded_file['tmp_name'], $zip_path );
        if( $zip->open( $zip_path ) !== true ){
            wp_send_json_error('Failed to open zip file.', 500);
        }

        $zip->extract_to( $wpir_replace_image_dir );
        $zip->close();
    }
}
