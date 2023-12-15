jQuery(document).ready(function($){
    $('#my_media_manager').click(function(e) {
        e.preventDefault();
        var image_frame;
        if(image_frame){
            image_frame.open();
        }
        // Define image_frame as wp.media object
        image_frame = wp.media({
            title: 'Select Media',
            multiple : false,
            library : {
                type : 'image,application/pdf' // Modify to accept the types you want
            }
        });

        image_frame.on('close',function() {
            // On close, get selections and save to the hidden input
            var selection =  image_frame.state().get('selection').first().toJSON();
            $('#my_custom_document').val(selection.url);
        });

        image_frame.on('select',function() {
            // On select, get selections and save to the hidden input
            var selection =  image_frame.state().get('selection').first().toJSON();
            $('#my_custom_document').val(selection.url);
        });

        image_frame.open();
    });
});