<?php
// Check if functions are already defined to avoid redeclaration
if ( ! function_exists( 'shortcode_elementor_create_section' ) ) {

    function shortcode_elementor_create_section() {
        ?>
        <div class="wrap">
            <h1>Create New Section</h1>
            <form method="post" action="">
                <?php wp_nonce_field( 'shortcode_elementor_create_section', 'shortcode_elementor_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="section_title">Section Title</label></th>
                        <td><input type="text" name="section_title" id="section_title" required /></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit_section" id="submit_section" class="button button-primary" value="Create Section">
                </p>
            </form>
        </div>
        <?php

        // Handle form submission to create the section and generate shortcode
        if ( isset( $_POST['submit_section'] ) && check_admin_referer( 'shortcode_elementor_create_section', 'shortcode_elementor_nonce' ) ) {
            $section_title = sanitize_text_field( $_POST['section_title'] );

            // Create a new post (section)
            $section_id = wp_insert_post( array(
                'post_title'   => $section_title,
                'post_content' => '',
                'post_status'  => 'publish',
                'post_type'    => 'post', // Changed from 'page' to 'post'
            ) );

            // Save the generated shortcode as post meta
            $shortcode = '[shortcode_elementor_section section_id="' . $section_id . '"]';
            update_post_meta( $section_id, '_section_shortcode', $shortcode );

            // Show success message with generated shortcode
            echo '<div class="updated"><p>Section created successfully! Shortcode: <code>' . $shortcode . '</code></p></div>';
        }
    }
}

if ( ! function_exists( 'shortcode_elementor_edit_section' ) ) {
    function shortcode_elementor_edit_section( $section_id ) {
        $section = get_post( $section_id );
        ?>
        <div class="wrap">
            <h1>Edit Section - <?php echo esc_html( $section->post_title ); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field( 'shortcode_elementor_edit_section', 'shortcode_elementor_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="section_title">Section Title</label></th>
                        <td><input type="text" name="section_title" id="section_title" value="<?php echo esc_attr( $section->post_title ); ?>" required /></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit_section" id="submit_section" class="button button-primary" value="Update Section">
                </p>
            </form>
            <a href="<?php echo get_edit_post_link( $section_id ); ?>" class=" button button-primary1">Edit with Elementor</a>
        </div>
        <?php
    }
}

if ( ! function_exists( 'shortcode_elementor_manage_sections' ) ) {
    function shortcode_elementor_manage_sections() {
        $args = array(
            'post_type'   => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
        );
        $sections = get_posts( $args );
        ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'bulk_delete_sections', 'bulk_delete_nonce' ); ?>
            <table class="form-table shortcode-elementor-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" /> Select All</th>
                        <th>Section Title</th>
                        <th>Shortcode</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $sections as $section ) : ?>
                        <tr>
                            <td><input type="checkbox" name="sections[]" value="<?php echo esc_attr( $section->ID ); ?>" class="select-section" /></td>
                            <td><?php echo esc_html( $section->post_title ); ?></td>
                            <td><code>[shortcode_elementor_section section_id="<?php echo $section->ID; ?>"]</code></td>
                            <td>
                                <a href="<?php echo admin_url( 'admin.php?page=shortcode-elementor&action=edit&section_id=' . $section->ID ); ?>" class="button-primary">Edit</a>
                                <a href="<?php echo get_delete_post_link( $section->ID ); ?>" class="button-danger" onclick="return confirm('Are you sure you want to delete this section?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>
                <input type="submit" name="bulk_delete" class="button button-danger" value="Delete Selected">
            </p>
        </form>
        <script>
            // JavaScript to handle the "Select All" checkbox
            document.getElementById('select-all').addEventListener('click', function(e) {
                var checkboxes = document.querySelectorAll('.select-section');
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = e.target.checked;
                });
            });
        </script>
        <?php
    }
}

add_action( 'admin_init', 'handle_bulk_delete_sections' );

function handle_bulk_delete_sections() {
    // Ensure that WP functions are loaded and nonce is verified
    if ( isset( $_POST['bulk_delete'] ) && isset( $_POST['sections'] ) && is_array( $_POST['sections'] ) ) {
        // Verify nonce for security
        if ( ! isset( $_POST['bulk_delete_nonce'] ) || ! wp_verify_nonce( $_POST['bulk_delete_nonce'], 'bulk_delete_sections' ) ) {
            die( 'Security check failed.' );
        }

        foreach ( $_POST['sections'] as $section_id ) {
            // Ensure valid post ID before deleting
            $section_id = intval( $section_id );
            if ( get_post_status( $section_id ) ) {
                wp_delete_post( $section_id, true ); // Force delete (bypass trash)
            }
        }

        // Redirect to avoid form resubmission
        wp_redirect( admin_url( 'admin.php?page=shortcode-elementor' ) );
        exit;
    }
}

