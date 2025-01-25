<?php
/**
 * For manage and search product from Woocommerce product object
 * 
 * @package WC Product Image Replacer
 */
namespace WPIR\Services;

if( ! defined( 'ABSPATH' ) ){
    exit;
}

use \WC_Product;

class ProductManager{
    /**
     * Replace main product image handler
     * 
     * @param string $product_id Woocommerce expected product id
     * 
     * @return void
     */
    public function wpir_replace_product_images( $product_id ){
        $product = wc_get_product( $product_id );

        // Verified if product is exist
        if( ! $product ){
            error_log( 'Product with id' . $dir_name . 'not found' );
        }

        /**
         * @var ImageHandler $image_handler Image handler class
         */
        $image_handler = new ImageHandler();

        // Get the product image id
        $old_image_id = $this->wpir_get_product_image_id( $product );
        
        //Get the product image gallery ids
        $old_gallery_ids = $this->wpir_get_gallery_image_ids( $product );

        // Prepare backup for old images
        if( ! $image_handler->wpir_prepare_backup_image( $old_image_id, $old_gallery_ids ) ){
            error_log( 'Failed to create backup' );
        }

        // Get new image id
        $new_image_id = $image_handler->wpir_get_new_image_id();

        // Get new gallery ids
        $new_gallery_ids = $image_handler->wpir_get_new_gallery_ids();

        // Replaced old image with new image
        $this->wpir_set_new_product_image( $product, $new_image_id, $new_gallery_ids );

        // Download backup image
        $image_handler->wpir_download_backup_image();

        // Delete old image from media and database
        $image_handler->wpir_delete_old_images( $old_image_id, $old_gallery_ids );
    }

    /**
     * Get main product image id handler
     * 
     * @param WC_Product $product Woocommerce product object
     * 
     * @return string
     */
    private function wpir_get_product_image_id( $product ){
        // Product image id
        $image_id = $product->get_image_id();
        
        return $image_id;
    }
    
    /**
     * Get product gallery image ids handler
     * 
     * @param WC_Product $product Woocommerce product object
     * 
     * @return array
     */
    private function wpir_get_gallery_image_ids( $product ){
        // Product gallery image id collections
        $image_ids = $product->get_gallery_image_ids();
        
        return $image_ids;
    }

    /**
     * Set product image and gallery image handler
     * 
     * @param WC_Product $product Woocommerce product object
     * @param int $new_image_id
     * @param array $new_gallery_ids New gallery image id collections
     * 
     * @return void
     */
    private function wpir_set_new_product_image( $product, $new_image_id, $new_gallery_ids ){
        $product->set_image_id( $new_image_id );
        $product->set_gallery_image_ids( $new_gallery_ids );
        $product->save();
    }
}
