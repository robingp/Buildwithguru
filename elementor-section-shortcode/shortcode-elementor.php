<?php
/**
 * Plugin Name: Shortcode Elementor
 * Description: Create, edit, delete sections as posts with Elementor and generate shortcodes to display them anywhere.
 * Version: 1.3
 * Author: Guruprasad Y
 * License: GPL2
 */

// Direct access protection
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcode-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-functions.php';

// Enqueue Admin Scripts and Styles
function shortcode_elementor_admin_scripts() {
    wp_enqueue_style( 'shortcode-elementor-admin-style', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css' );
    wp_enqueue_script( 'shortcode-elementor-admin-script', plugin_dir_url( __FILE__ ) . 'assets/js/admin-script.js', array( 'jquery' ), false, true );
}
add_action( 'admin_enqueue_scripts', 'shortcode_elementor_admin_scripts' );

// Register Shortcode Functionality
function shortcode_elementor_register_section_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'section_id' => '',
    ), $atts, 'shortcode_elementor_section' );

    if ( ! empty( $atts['section_id'] ) ) {
        return shortcode_elementor_display_section( $atts['section_id'] );
    }

    return '';
}
add_shortcode( 'shortcode_elementor_section', 'shortcode_elementor_register_section_shortcode' );

// Admin page function
function shortcode_elementor_admin_page() {
    if ( isset( $_POST['bulk_delete'] ) && check_admin_referer( 'bulk_delete_action', 'bulk_delete_nonce' ) ) {
        $section_ids = isset( $_POST['section_ids'] ) ? array_map( 'intval', $_POST['section_ids'] ) : array();
        if ( ! empty( $section_ids ) ) {
            foreach ( $section_ids as $section_id ) {
                if ( current_user_can( 'delete_post', $section_id ) ) {
                    wp_delete_post( $section_id, true );
                }
            }
            echo '<div class="notice notice-success is-dismissible"><p>Selected sections have been deleted successfully.</p></div>';
        }
    }

    ?>
    <div class="wrap shortcode-elementor-wrap">
        <h1>Shortcode Elementor</h1>
        <form method="post" action="">
            <?php
            if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['section_id'] ) ) {
                $section_id = intval( $_GET['section_id'] );
                shortcode_elementor_edit_section( $section_id );
            } else {
                shortcode_elementor_create_section();
            }
            ?>
        </form>
        <hr>
        <h2>Manage Sections</h2>
        <?php shortcode_elementor_manage_sections(); ?>
    </div>
    <?php
}

// Add admin menu
function shortcode_elementor_admin_menu() {
    add_menu_page(
        'Shortcode Elementor',
        'Shortcode Elementor',
        'manage_options',
        'shortcode-elementor',
        'shortcode_elementor_admin_page'
    );
}
add_action( 'admin_menu', 'shortcode_elementor_admin_menu' );
