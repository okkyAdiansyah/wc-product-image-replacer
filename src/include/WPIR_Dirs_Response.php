<?php
/**
 * Response object for uploaded file verification
 * 
 * @package WC Product Image Replacer
 */
namespace WPIR;

if( ! defined( 'ABSPATH' ) ){
    exit;
}

class WPIR_Dirs_Response{
    public $product_id;
    public $main_dir_is_exist;
    public $gallery_dir_is_exist;
    public $main_image;
    public $gallery_images;

    public function __construct($product_id) {
        $this->product_id = $product_id;
        $this->main_dir_is_exist = false;
        $this->gallery_dir_is_exist = false;
    }

    /**
     * Change $main_dir_is_exist or $gallery_dir_is_exist value
     * 
     * @param boolean $is_exist
     * @param string $exp_dir
     * 
     * @return void
     */
    public function wpir_exp_dir_exist( $exp_dir, $is_exist ){
        if( $exp_dir === 'main' ){
            $this->main_dir_is_exist = $is_exist;
        } else {
            $this->gallery_dir_is_exist = $is_exist;
        }
    }

    /**
     * Assign filename
     * 
     * @param string $image_cat Image category
     * @param string|array $images Image single or collection filename
     * 
     * @return void
     */
    public function wpir_assign_filename( $image_cat, $images ){
        if( $image_cat === 'main' ){
            $this->main_image = $images;
        } else {
            $this->gallery_images = $images;
        }
    }
}