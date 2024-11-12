<?php
// Check if functions are already defined to avoid redeclaration
if ( ! function_exists( 'shortcode_elementor_display_section' ) ) {
    function shortcode_elementor_display_section( $section_id ) {
        // Load Elementor styles for this specific section
        if ( class_exists( 'Elementor\Plugin' ) ) {
            $document = \Elementor\Plugin::$instance->documents->get( $section_id );
            if ( $document ) {
                // Enqueue the styles for this specific section
                \Elementor\Plugin::$instance->frontend->enqueue_styles();
                // Render the section content
                return \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $section_id );
            }
        }
        return ''; // Return nothing if Elementor isn't available or document doesn't exist
    }
}
