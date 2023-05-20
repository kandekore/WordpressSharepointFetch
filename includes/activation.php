<?php
function my_plugin_create_upload_folder() {
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . '/sharepointimages';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0700);
    }
}
