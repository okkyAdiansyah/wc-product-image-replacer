<?php
/**
 * Plugin Name: WC Product Image Replacer
 * Version: 1.0
 * Author: Okky Adiansyah
 * Description: Woocommerce extensions plugin for handling product image & gallery image replacement, backup old image, and clean database from old image
 * Text Domain: wpir
 */
if( ! defined( "ABSPATH" ) ){
    exit;
}

if( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ){
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}