<?php
/**
 * Plugin Name: BuildWithGuru
 * Description: A plugin to manage headers, footers, and blocks with customizable options using Elementor.
 * Version: 1.1
 * Author: Guruprasad
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register custom post types for Header and Footer
function guru_builder_register_post_types() {
    // Header post type
    register_post_type( 'guru_header', array(
        'labels' => array(
            'name'               => __( 'Headers', 'guru_builder' ),
            'singular_name'      => __( 'Header', 'guru_builder' ),
            'add_new'            => __( 'Add New', 'guru_builder' ),
            'edit_item'          => __( 'Edit Header', 'guru_builder' ),
            'all_items'          => __( 'All Headers', 'guru_builder' ),
        ),
        'public'             => true,
        'has_archive'        => false,
        'supports'           => array( 'title', 'editor' ),
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-editor-insertmore', // Icon for the menu
    ));

    // Footer post type
    register_post_type( 'guru_footer', array(
        'labels' => array(
            'name'               => __( 'Footers', 'guru_builder' ),
            'singular_name'      => __( 'Footer', 'guru_builder' ),
            'add_new'            => __( 'Add New', 'guru_builder' ),
            'edit_item'          => __( 'Edit Footer', 'guru_builder' ),
            'all_items'          => __( 'All Footers', 'guru_builder' ),
        ),
        'public'             => true,
        'has_archive'        => false,
        'supports'           => array( 'title', 'editor' ),
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-editor-insertmore', // Icon for the menu
    ));
}
add_action( 'init', 'guru_builder_register_post_types' );

// Add meta boxes for header and footer
function guru_builder_add_meta_boxes() {
    add_meta_box(
        'guru_header_options',
        __( 'Header Options', 'guru_builder' ),
        'guru_builder_header_options_callback',
        'guru_header'
    );

    add_meta_box(
        'guru_footer_options',
        __( 'Footer Options', 'guru_builder' ),
        'guru_builder_footer_options_callback',
        'guru_footer'
    );
}
add_action( 'add_meta_boxes', 'guru_builder_add_meta_boxes' );

// Callback for header options meta box
function guru_builder_header_options_callback( $post ) {
    $selected_pages = get_post_meta( $post->ID, '_guru_selected_pages', true );
    $is_sticky = get_post_meta( $post->ID, '_guru_header_sticky', true );

    // Page selection
    $pages = get_pages();
    echo '<label for="guru_selected_pages">' . esc_html( __( 'Select Pages:', 'guru_builder' ) ) . '</label><br>';
    foreach ( $pages as $page ) {
        $checked = ( is_array( $selected_pages ) && in_array( $page->ID, $selected_pages ) ) ? 'checked' : '';
        echo '<input type="checkbox" name="guru_selected_pages[]" value="' . esc_attr( $page->ID ) . '" ' . esc_attr( $checked ) . '> ' . esc_html( $page->post_title ) . '<br>';
    }

    // Sticky header
    echo '<label for="guru_header_sticky">' . esc_html( __( 'Make Header Sticky:', 'guru_builder' ) ) . '</label>';
    echo '<input type="checkbox" name="guru_header_sticky" value="1" ' . checked( $is_sticky, '1', false ) . '>';
}

// Callback for footer options meta box
function guru_builder_footer_options_callback( $post ) {
    $selected_pages = get_post_meta( $post->ID, '_guru_selected_pages', true );
    $is_sticky = get_post_meta( $post->ID, '_guru_footer_sticky', true );

    // Page selection
    $pages = get_pages();
    echo '<label for="guru_selected_pages">' . esc_html( __( 'Select Pages:', 'guru_builder' ) ) . '</label><br>';
    foreach ( $pages as $page ) {
        $checked = ( is_array( $selected_pages ) && in_array( $page->ID, $selected_pages ) ) ? 'checked' : '';
        echo '<input type="checkbox" name="guru_selected_pages[]" value="' . esc_attr( $page->ID ) . '" ' . esc_attr( $checked ) . '> ' . esc_html( $page->post_title ) . '<br>';
    }

    // Sticky footer
    echo '<label for="guru_footer_sticky">' . esc_html( __( 'Make Footer Sticky:', 'guru_builder' ) ) . '</label>';
    echo '<input type="checkbox" name="guru_footer_sticky" value="1" ' . checked( $is_sticky, '1', false ) . '>';
}

// Save the meta box data
function guru_builder_save_meta_boxes( $post_id ) {
    if ( ! isset( $_POST['guru_selected_pages'] ) ) {
        return;
    }
    $selected_pages = array_map( 'intval', $_POST['guru_selected_pages'] );
    update_post_meta( $post_id, '_guru_selected_pages', $selected_pages );

    $is_header_sticky = isset( $_POST['guru_header_sticky'] ) ? '1' : '0';
    update_post_meta( $post_id, '_guru_header_sticky', $is_header_sticky );

    $is_footer_sticky = isset( $_POST['guru_footer_sticky'] ) ? '1' : '0';
    update_post_meta( $post_id, '_guru_footer_sticky', $is_footer_sticky );
}
add_action( 'save_post', 'guru_builder_save_meta_boxes' );

// Output the header content
function guru_builder_display_header() {
    $header_query = new WP_Query( array(
        'post_type' => 'guru_header',
        'posts_per_page' => 1,
    ));

    if ( $header_query->have_posts() ) {
        echo '<header class="guru-builder-header';
        $header_post = $header_query->post;
        $is_sticky = get_post_meta( $header_post->ID, '_guru_header_sticky', true );
        if ($is_sticky) {
            echo ' sticky-header';
        }
        echo '">';
        while ( $header_query->have_posts() ) {
            $header_query->the_post();
            echo '<div>' . wp_kses_post( apply_filters( 'the_content', get_the_content() ) ) . '</div>'; // Display header content with Elementor
        }
        echo '</header>';
    }

    // Restore original post data
    wp_reset_postdata();
}
add_action( 'wp_head', 'guru_builder_display_header' );

// Add custom content below the plugin name in the Plugins Dashboard
function guru_builder_display_slideshow_in_plugins_dashboard( $links, $file ) {
    // Make sure it's for your specific plugin (replace 'buildwithguru/buildwithguru.php' with your plugin's main file)
    if ( strpos( $file, 'buildwithguru/buildwithguru.php' ) !== false ) {
        // Define the URLs for your WEBP images
        $image_url_1 = plugin_dir_url( __FILE__ ) . 'assets/header-footer-preview.webp'; // Static Image
        $image_url_2 = plugin_dir_url( __FILE__ ) . 'assets/header-footer-animation.webp'; // Animated Image

        // Add the slideshow HTML and JavaScript
        echo '<div class="guru-builder-slideshow">';
        echo '<img src="' . esc_url( $image_url_1 ) . '" alt="Header & Footer Preview" class="guru-slide" style="width: 100%; max-width: 600px; height: auto; display: none;">';
        echo '<img src="' . esc_url( $image_url_2 ) . '" alt="Header & Footer Animation" class="guru-slide" style="width: 100%; max-width: 600px; height: auto; display: block;">';
        echo '</div>';

        // Include the script to cycle through the images every few seconds
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                var currentIndex = 0;
                var slides = $(".guru-slide");
                var totalSlides = slides.length;

                // Function to show the next slide
                function showNextSlide() {
                    slides.eq(currentIndex).fadeOut(500); // Fade out the current slide
                    currentIndex = (currentIndex + 1) % totalSlides; // Get the next slide index
                    slides.eq(currentIndex).fadeIn(500); // Fade in the next slide
                }

                // Set an interval to change slides every 4 seconds
                setInterval(showNextSlide, 4000); // Adjust timing as needed (4000ms = 4 seconds)
            });
        </script>
        <?php
    }

    return $links; // Ensure other links are still displayed correctly
}
add_filter( 'plugin_row_meta', 'guru_builder_display_slideshow_in_plugins_dashboard', 10, 2 );

// Output the footer content
function guru_builder_display_footer() {
    $footer_query = new WP_Query( array(
        'post_type' => 'guru_footer',
        'posts_per_page' => 1,
    ));

    if ( $footer_query->have_posts() ) {
        echo '<footer class="guru-builder-footer';
        $footer_post = $footer_query->post;
        $is_sticky = get_post_meta( $footer_post->ID, '_guru_footer_sticky', true );
        if ($is_sticky) {
            echo ' sticky-footer';
        }
        echo '">';
        while ( $footer_query->have_posts() ) {
            $footer_query->the_post();
            echo '<div>' . wp_kses_post( apply_filters( 'the_content', get_the_content() ) ) . '</div>'; // Display footer content with Elementor
        }
        echo '</footer>';
    }

    // Restore original post data
    wp_reset_postdata();
}
add_action( 'wp_footer', 'guru_builder_display_footer' );

// Enqueue sticky styles
function guru_builder_enqueue_styles() {
    echo '<style>
        .sticky-header {
            position: fixed; /* Change to fixed */
            top: 0;
            left: 0; /* Ensure it stays aligned to the left */
            width: 100%; /* Ensure full width */
            z-index: 1000;
        }
        .sticky-footer {
            position: sticky;
            bottom: 0;
            z-index: 1000;
        }
        /* Add space for sticky header */
        header {
            padding: 0; /* Remove padding if not needed */
        }
    </style>';
}
add_action( 'wp_head', 'guru_builder_enqueue_styles' );
