let $ = jQuery;

$(document).ready(function(){
    $('#wpir-file-upload').on('submit', function(e){
        e.preventDefault();
        console.log('test');

        const fileInput = document.querySelector("#zip-file");
        const images = fileInput.files[0];
        
        const formData = new FormData();
        formData.append('action', 'handle_zip_verification');
        formData.append('wpir_nonce', wpir_ajax_object.wpir_uploading_nonce);
        formData.append('images', images);

        jQuery.ajax({
            url: wpir_ajax_object.ajax_url,
            type: 'POST',
            processData: false,
            contentType: false,
            data: formData,
            success: function(response) {
                alert(response.data);
            },
            error: function(error) {
                alert('An error occurred: ' + error.responseText);
            }
        });
    });
})

// jQuery('#wpir-file-upload').on('submit', function(e) {
//     e.preventDefault();
//     console.log('test');
    
//     const formData = new FormData();
//     formData.append('action', 'handle_zip_verification');
//     formData.append('wpir_nonce', wpir_ajax_object.wpir_uploading_nonce);
//     formData.append('images', images);

//     jQuery.ajax({
//         url: wpir_ajax_object.ajax_url,
//         type: 'POST',
//         processData: false,
//         contentType: false,
//         data: formData,
//         success: function(response) {
//             alert(response.data);
//         },
//         error: function(error) {
//             alert('An error occurred: ' + error.responseText);
//         }
//     });
// });