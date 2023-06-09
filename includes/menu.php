<?php
function my_sharepoint_fetcher_menu() {
    add_menu_page(
        'My SharePoint Fetcher',
        'Settings',
        'manage_options',
        'my-sharepoint-fetcher',
        'my_sharepoint_fetcher_settings_page',
        'dashicons-text',
        30
    );

    add_submenu_page(
        'my-sharepoint-fetcher',
        'Upload SharePoint Images',
        'Upload SharePoint Images',
        'manage_options',
        'my-sharepoint-images',
        'my_sharepoint_images_options_page'
    );
}


// Create the settings page
function my_sharepoint_fetcher_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
   // Process the form submission if the form has been submitted
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    process_settings_form();
}

// Fetch current settings
$tenantId = get_option('my_sharepoint_fetcher_tenant_id', '');
$clientId = get_option('my_sharepoint_fetcher_client_id', '');
$clientSecret = get_decrypted_client_secret();
$hostname = get_option('my_sharepoint_fetcher_hostname', '');
$sitePath = get_option('my_sharepoint_fetcher_site_path', '');

// Fetch site ID and drive ID
$siteId = my_sharepoint_fetcher_get_site_id();
$driveId = my_sharepoint_fetcher_get_drive_id($siteId);



   // Display the settings form
   echo '<div class="wrap">';
   echo '<h1>My SharePoint Fetcher Settings</h1>';
   echo '<form method="post">';
   echo '<table class="form-table">';
   echo '<tr><th scope="row">Tenant ID</th><td><input type="text" name="tenant_id" value="' . esc_attr($tenantId) . '"></td></tr>';
   echo '<tr><th scope="row">Client ID</th><td><input type="text" name="client_id" value="' . esc_attr($clientId) . '"></td></tr>';
   echo '<tr style="display: none;"><th scope="row">Client Secret</th><td><input type="password" name="client_secret" value=""></td></tr>';
   echo '<tr><th scope="row">Hostname</th><td><input type="text" name="hostname" value="' . esc_attr($hostname) . '"></td></tr>';
   echo '<tr><th scope="row">Site Path</th><td><input type="text" name="site_path" value="' . esc_attr($sitePath) . '"></td></tr>';
   echo '</table>';
   submit_button();
   echo '</form>';
   echo '</div>';

  // List files in drive if settings are present
  if ($clientId && $tenantId && $hostname && $sitePath) {
    $driveId = my_sharepoint_fetcher_get_drive_id($siteId);

    // Only list files if driveId is available
    if ($driveId) {
        $files = my_sharepoint_fetcher_list_files($driveId);

        if ($files) {
            echo '<h2>Files in Drive</h2>';
            echo '<ul>';

            foreach ($files as $file) {
                echo '<li>' . esc_html($file->name) . ' - [sharepoint_content id="' . esc_html($file->id) . '" ]</li>';
            }

            echo '</ul>';
        }
    }
}
}

    // Process the form submission
function process_settings_form() {
    if (isset($_POST['tenant_id']) && isset($_POST['client_id']) && isset($_POST['hostname']) && isset($_POST['site_path'])) {
        update_option('my_sharepoint_fetcher_tenant_id', sanitize_text_field($_POST['tenant_id']));
        update_option('my_sharepoint_fetcher_client_id', sanitize_text_field($_POST['client_id']));
        update_option('my_sharepoint_fetcher_hostname', sanitize_text_field($_POST['hostname']));
        update_option('my_sharepoint_fetcher_site_path', sanitize_text_field($_POST['site_path']));
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }
     

}
}
function my_sharepoint_images_options_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
  
    // Save folder id if form is submitted
    if (isset($_POST['folder_id'])) {
        try {
            $folderId = sanitize_text_field($_POST['folder_id']);
            $accessToken = get_transient('my_sharepoint_fetcher_token');

            // Throw an exception if access token is not available
            if ($accessToken === false) {
                throw new Exception('Access token not found');
            }

            $items = my_sharepoint_fetcher_fetch_folder($folderId, $accessToken);

            foreach ($items as $item) {
                my_sharepoint_fetcher_fetch_content($item->id, $accessToken);
            }

            echo '<div class="updated"><p>Folder imported successfully.</p></div>';
        } catch (Exception $e) {
            echo '<div class="error"><p>Error: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
        }
    }

    // Save uploaded file if form is submitted
    if (isset($_FILES['my_zip_upload'])) {
        $file = $_FILES['my_zip_upload'];

        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . '/sharepointimages/';
        $filename = $upload_dir . $file['name'];

        if (move_uploaded_file($file['tmp_name'], $filename)) {
            // If file was uploaded successfully, try to unzip it
            $zip = new ZipArchive;
            if ($zip->open($filename) === TRUE) {
                $zip->extractTo($upload_dir);
                $zip->close();
                // Change the permissions of the extracted directory and its contents
                chmod($upload_dir, 0755);
                // Change permissions of all files in the directory
                array_map('chmod', glob("$upload_dir/*"), array_fill(0, count(glob("$upload_dir/*")), 0755));
                echo '<div class="updated"><p>Zip file uploaded and extracted successfully.</p></div>';
            } else {
                echo '<div class="error"><p>Failed to extract zip file.</p></div>';
            }
        } else {
            echo '<div class="error"><p>Failed to upload zip file.</p></div>';
        }
    }

    // Display the upload form
    echo '<h1>Upload Zip File to SharePoint Images Folder</h1>';
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="my_zip_upload">';
    submit_button('Upload');
    echo '</form>';
    echo '</div>';
       // Display the folder ID input form
//echo '<div class="wrap">';
//echo '<h1>Import Folder from SharePoint</h1>';
//echo '<form method="post">';
//echo '<input type="text" name="folder_id" placeholder="Enter folder ID">';
//submit_button('Import');
//echo '</form>';
//echo '<br><br>';
}

function process_settings_form() {
    // Check if all necessary POST fields are set
    $required_fields = ['tenant_id', 'client_id', 'client_secret', 'hostname', 'site_path'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field])) {
            return;
        }
    }

    // Encryption key (Please make sure to store this securely and do not expose it publicly)
    $encryption_key = 'your_encryption_key_here'; // Ideally from a secure source or environment variable

    // Save the settings
    foreach ($required_fields as $field) {
        $value = sanitize_text_field($_POST[$field]);
        
        // If this is a sensitive field, encrypt the value
        if ($field === 'client_secret') {
            $cipher = "aes-128-gcm";
            if (in_array($cipher, openssl_get_cipher_methods()))
            {
                $ivlen = openssl_cipher_iv_length($cipher);
                $iv = openssl_random_pseudo_bytes($ivlen);
                $value = openssl_encrypt($value, $cipher, $encryption_key, $options=0, $iv, $tag);
            }
        }

        update_option('my_sharepoint_fetcher_' . $field, $value);
    }

    echo '<div class="updated"><p>Settings saved.</p></div>';
}
