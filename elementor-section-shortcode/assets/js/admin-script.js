jQuery(document).ready(function($){
    // Show/hide specific pages selection based on the display option
    function toggleSpecificPages() {
        var displayOption = $('select[name="shortcode_elementor_display_all_pages"]').val();
        if(displayOption == 'specific') {
            $('.shortcode_elementor_specific_pages_row').show();
        } else {
            $('.shortcode_elementor_specific_pages_row').hide();
        }
    }

    // On page load, call the function to adjust visibility
    toggleSpecificPages();

    // On change of the select, toggle the pages section
    $('select[name="shortcode_elementor_display_all_pages"]').change(function() {
        toggleSpecificPages();
    });
});
