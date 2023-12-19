jQuery(document).ready(function($) {

    var file_frame; // Declare this outside the click handler

    $('#my_media_manager').click(function(e) {
        e.preventDefault();

        if (file_frame) {
            // Open the existing frame
            file_frame.open();
            return;
        }

        // Initialize the media frame
        file_frame = wp.media({
            title: 'Select a File',
            button: {
                text: 'Use this file'
            },
            multiple: false,
            library: {
                type: ['image', 'application'] // Allow all types initially
            }
        });

        file_frame.on('select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            var fileType = attachment.filename.split('.').pop().toLowerCase();

            if (myPluginData.allowedFileTypes.includes(fileType)) {
                $('#my_custom_document').val(attachment.url);
            } else {
                alert('File type .' + fileType + ' is not allowed.');
            }
        });

        // Open the frame
        file_frame.open();
    });
});
