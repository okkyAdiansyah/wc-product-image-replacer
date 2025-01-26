<?php
/**
 * Handle image download, replace, and delete from Woocommerce product image and gallery
 * 
 * @package WC Product Image Replacer
 */
namespace WPIR\Services;

if( ! defined( 'ABSPATH' ) ){
    exit;
}

use WPIR\Services\DirectoryManager;

class ImageHandler {
    /**
     * Prepare backup image handler
     * 
     * @param string $product_id Product ID
     * @param array $image_ids Image ID collections
     * 
     * @return boolean
     */
    public function wpir_prepare_backup_image( $product_id, $image_ids ){
        /**
         * @var DirectoryManager Directory Manager class
         */
        $dir_manager = new DirectoryManager();

        // Create new directory based on product id
        $backup_path = $dir_manager->wpir_create_new_backup_dir( $product_id );

        /**
         * Destructure old image ids
         */
        $main_image_id = array_shift( $image_ids );
        $main_image_path = get_attached_file( $main_image_id );

        // Copy image to destination
        if( ! $dir_manager->wpir_copy_image( 'main', $main_image_path, $backup_path ) ){
            error_log( 'Failed to copy image' . $main_image_id );
        }

        // Copy each gallery image to destination
        foreach( $image_ids as $id ){
            $gallery_image_path = get_attached_file( $id );

            if( ! $dir_manager->wpir_copy_image( 'gallery', $gallery_image_path, $backup_path ) ){
                error_log( 'Failed to copy image' . $id );
            }
        }
    }

    /**
     * Get new image ID handler
     * 
     * @param int $product_id Product ID
     * @param string $image_type Image type container
     * 
     * @return array
     */
    public function wpir_get_new_image_ids( $product_id, $image_type ){
        // Move image target to media dir
        $image_ids = $this->wpir_move_image_to_media( $product_id, $image_type );

        return $image_ids;
    }

    /**
     * Move main image to media library from plugin upload directory
     * 
     * @param int $product_id Product ID
     * @param string $image_type Image type container
     * 
     * @return int
     */
    private function wpir_move_image_to_media( $product_id ){
        /**
         * @var DirectoryManager Directory Manager class
         */
        $dir_manager = new DirectoryManager();

        $directories = [ 'main', 'gallery' ];
        $attachment_ids = array();

        foreach( $directories as $directory ){
            // Get directoy path
            $dir_path = $dir_manager->wpir_get_dir_path( $product_id, $directory );

            // Get files inside the directory path
            $files = scandir( $dir_path );

            // Loop and move each file to media library
            foreach( $files as $file ){
                if( $dir_manager->wpir_is_image_file( $file ) ){
                    $file_path = $dir_path . $file;
                    $attachment_id = $dir_manager->wpir_move_to_media_library( $file_path );
    
                    if( $attachment_id ){
                        $attachment_ids[] = $attachment_id;
                    }
                }
            }
        }
        
        return $attachment_ids;
    }
}

