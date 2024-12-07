let $ = jQuery;

$(document).ready(function(){
    $('#wpir-file-upload').on('submit', function(e){
        e.preventDefault();
        $('#wpir-file-upload-loading').show();
        $('#wpir-file-upload').hide();

        const fileInput = document.querySelector("#zip-file");
        const images = fileInput.files[0];

        const formData = new FormData();
        formData.append('action', 'handle_zip_verification');
        formData.append('wpir_nonce', wpir_ajax_object.wpir_uploading_nonce);
        formData.append('images', images);

        $.ajax({
            url: wpir_ajax_object.ajax_url,
            type: 'POST',
            processData: false,
            contentType: false,
            data: formData,
            success: function(response) {
                $('#wpir-file-upload-loading').hide();
                $('#wpir-replace').css('display', 'flex');
                console.log(response.data);
            },
            error: function(error) {
                $('#wpir-file-upload-loading').hide();
            }
        });
    });
})