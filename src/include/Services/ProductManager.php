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

        // Get old image ids collections
        $old_image_ids = $this->wpir_get_old_image_ids( $product );

        // Prepare backup for old images
        if( ! $image_handler->wpir_prepare_backup_image( $product_id, $old_image_ids ) ){
            error_log( 'Failed to create backup' );
        }

        /**
         * Get new main product image and gallery ID collections
         * Then destructurize the array
         */
        $new_product_image_ids = $image_handler->wpir_get_new_image_ids( $product_id );

        // Replaced old image with new image
        $this->wpir_set_new_product_image( $product, $new_product_image_ids );

        // Download backup image
        $image_handler->wpir_download_backup_image();

        // Delete old image from media and database
        $image_handler->wpir_delete_old_images( $old_image_id, $old_gallery_ids );
    }

    /**
     * Set product image and gallery image handler
     * 
     * @param WC_Product $product Woocommerce product object
     * 
     * @return array
     */
    private function wpir_get_old_image_ids( $product ){
        /**
         * Get main image id and gallery ids
         * Then merge it together to simplify
         */
        $old_main_image_id = array();
        $old_main_image_id[] = $product->get_image_id();
        $old_gallery_ids = $product->get_gallery_image_ids();
        $old_image_ids = array_merge( $old_main_image_id, $old_gallery_ids );

        return $old_image_ids;
    }

    /**
     * Set product image and gallery image handler
     * 
     * @param WC_Product $product Woocommerce product object
     * @param array $new_image_ids New main product and gallery image id collections
     * 
     * @return void
     */
    private function wpir_set_new_product_image( $product, $new_image_ids ){
        $main_image_id = array_shift( $new_image_ids );
        $product->set_image_id( $main_image_id );
        $product->set_gallery_image_ids( $new_image_ids );
        $product->save();
    }
}
