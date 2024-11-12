<?php
/**
 * Plugin Name: Exit Tab Favicon and Title
 * Description: This plugin allows you to change the favicon and title of your website when the user exits the tab or the page is hidden.
 * Version: 1.5
 * Author: Guruprasad
 * Author URI: https://github.com/robingp
 * License: GPL2
 * Text Domain: exit-tab-favicon-title
 * Plugin URI: http://your-plugin-url.com
 * Banner Image: /assets/banner-1280x192.png
 * Icon: /assets/icon-128x128.png
 */

// Hook to enqueue the script and style for the plugin
function exit_tab_favicon_title_enqueue_scripts() {
    wp_enqueue_media(); // Enqueue WordPress media uploader script
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Store the original title and favicon URL
            var originalTitle = document.title;
            var defaultFavicon = "<?php echo esc_url(get_site_icon_url()); ?>";
            var exitFaviconUrl = "<?php echo esc_url(get_option('exit_favicon', '')); ?>";
            var exitTitle = "<?php echo esc_js(get_option('exit_title', 'Come Back!')); ?>";

            // Set up the favicon element if it doesn't exist
            var link = document.querySelector('link[rel="icon"]') || document.createElement('link');
            link.type = 'image/png';
            link.rel = 'icon';
            document.head.appendChild(link);

            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'hidden') {
                    // Change favicon and title when the tab is hidden
                    link.href = exitFaviconUrl;
                    document.title = exitTitle;
                } else {
                    // Restore favicon and title when the tab is active again
                    link.href = defaultFavicon;
                    document.title = originalTitle;
                }
            });

            // Open the media library when the 'Select Image' button is clicked
            var mediaUploader;
            $('#select_favicon_button').click(function(e) {
                e.preventDefault();

                // If the media frame already exists, reopen it
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                // Create the media frame
                mediaUploader = wp.media.frames.file_frame = wp.media({
                    title: 'Select Favicon Image',  // Title of the media uploader window
                    button: {
                        text: 'Use this image'  // Button text
                    },
                    multiple: false  // Allow only one image to be selected
                });

                // When an image is selected, set the input value to the image URL
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#exit_favicon').val(attachment.url);  // Set the URL of the selected image in the input field

                    // Update the favicon preview
                    $('#favicon_preview').attr('src', attachment.url);  // Show the image preview
                });

                // Open the media uploader
                mediaUploader.open();
            });
        });
    </script>
    <?php
}
add_action('wp_head', 'exit_tab_favicon_title_enqueue_scripts');

// Add settings page to the WordPress Admin
function exit_tab_favicon_title_settings_page() {
    add_options_page(
        'Exit Tab Favicon and Title Settings',
        'Exit Tab Favicon and Title',
        'manage_options',
        'exit-tab-favicon-title',
        'exit_tab_favicon_title_settings_page_html'
    );
}
add_action('admin_menu', 'exit_tab_favicon_title_settings_page');

// Settings page HTML
function exit_tab_favicon_title_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
// Handle form submission with nonce verification
if (isset($_POST['save_exit_settings']) && isset($_POST['_exit_settings_nonce']) && wp_verify_nonce(wp_unslash($_POST['_exit_settings_nonce']), 'exit_settings_nonce')) {
    // Sanitize inputs after removing slashes
    $exit_favicon = isset($_POST['exit_favicon']) ? esc_url_raw(wp_unslash($_POST['exit_favicon'])) : '';
    $exit_title = isset($_POST['exit_title']) ? sanitize_text_field(wp_unslash($_POST['exit_title'])) : '';
    
    update_option('exit_favicon', $exit_favicon);
    update_option('exit_title', $exit_title);

    echo '<div class="updated"><p><strong>Settings Saved Successfully!</strong></p></div>';
}

    ?>
    <div class="wrap" style="font-family: Arial, sans-serif; padding: 20px;">
        <h1 class="wp-heading-inline" style="font-size: 2em; margin-bottom: 15px;">Exit Tab Favicon and Title Settings</h1>
        <form method="post" style="max-width: 800px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <!-- Add a nonce for security -->
            <?php wp_nonce_field('exit_settings_nonce', '_exit_settings_nonce'); ?>

            <div style="margin-bottom: 30px;">
                <h2 class="exit-favicon-title" style="font-size: 1.6em; color: #333;">Exit Tab Favicon</h2>
                <p style="font-size: 1.1em; color: #777;">Upload a favicon image or enter a URL for the favicon that will appear when the user exits the page (browser tab change).</p>

                <!-- Favicon Upload from Media Library -->
                <div class="exit-favicon-upload" style="margin-top: 15px;">
                    <label for="exit_favicon" style="font-size: 1.2em; font-weight: bold;">Upload Favicon Image (16x16px or 32x32px recommended):</label>
                    <input type="text" name="exit_favicon" id="exit_favicon" value="<?php echo esc_url(get_option('exit_favicon', '')); ?>" class="regular-text" style="padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 75%;" />
                    <input type="button" class="button" value="Select Image" id="select_favicon_button" style="margin-left: 10px; padding: 10px 20px;" />
                    
                    <div style="margin-top: 15px;">
                        <img id="favicon_preview" src="<?php echo esc_url(get_option('exit_favicon', '')); ?>" style="max-width: 80px; max-height: 80px; border-radius: 5px;" alt="Favicon Preview" />
                    </div>

                    <p style="font-size: 0.9em; color: #777; margin-top: 10px;">Recommended image sizes: 16x16px or 32x32px. Larger images may not display correctly.</p>
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <h2 class="exit-title-settings" style="font-size: 1.6em; color: #333;">Exit Tab Title</h2>
                <label for="exit_title" style="font-size: 1.2em; font-weight: bold;">Enter the title for the tab when the user exits:</label>
                <input type="text" name="exit_title" id="exit_title" value="<?php echo esc_attr(get_option('exit_title', 'Come Back!')); ?>" class="regular-text" style="padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 75%;" />

                <p style="font-size: 0.9em; color: #777; margin-top: 10px;">This title will be shown when the tab is hidden or the user leaves the page.</p>
            </div>

            <p style="text-align: center;">
                <input type="submit" name="save_exit_settings" class="button-primary" value="Save Settings" style="padding: 10px 20px; background-color: #0073aa; border: none; border-radius: 5px; color: white; font-size: 1em;" />
            </p>
        </form>
    </div>
    <?php
} 
?>
