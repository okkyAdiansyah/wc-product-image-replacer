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

use WPIR\PluginInit;

PluginInit::wpir_init();

register_activation_hook( __FILE__, array( 'WPIR\PluginInit', 'wpir_activate_plugin' ) );
register_deactivation_hook( __FILE__, array( 'WPIR\PluginInit', 'wpir_deactivate_plugin' ) );