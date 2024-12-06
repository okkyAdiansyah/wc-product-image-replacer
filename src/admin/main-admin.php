<?php
/**
 * Plugin admin page
 * 
 * @package WC Product Image Replacer
 */
?>
<div class="wrap">
    <h1>WC Product Image Replacer</h1>
    <div class="upload-plugin" style="display:block;">
        <div class="upload-plugin-wrap">
            <p class="install-help">Insert .zip file contain image you want to replace</p>
            <form id="wpir-file-upload" class="wpir-upload__form wp-upload-form" method="post" enctype="multipart/form-data">
                <input type="file" id="zip-file" name="zip-file" accept=".zip" required>
                <input type="submit" id="zip-file-submit" name="zip-file-submit" class="button" value="Check file" disabled>
            </form>
        </div>
    </div>
</div>