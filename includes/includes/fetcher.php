<?php

// Fetch content from SharePoint
function my_sharepoint_fetcher_fetch_content($atts) {
    $documentId = $atts['id']; // Fetch the access token
    $accessToken = my_sharepoint_fetcher_fetch_access_token(); 

    if (false === $accessToken) {
        return false;
    }

    // Fetch site ID and drive ID
    $siteId = my_sharepoint_fetcher_get_site_id();
    $driveId = my_sharepoint_fetcher_get_drive_id($siteId);

    $url = 'https://graph.microsoft.com/v1.0/sites/' . $siteId . '/drives/' . $driveId . '/items/' . $documentId . '/content';

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
        ],
    ]);

    // Check for errors
    if (is_wp_error($response)) {
        return false;
    }

    // Parse the response and get the content
    $body = wp_remote_retrieve_body($response);

    // Create a new DOMDocument instance and load the HTML content
    $doc = new DOMDocument();
libxml_use_internal_errors(true);  // Enable user error handling
$doc->loadHTML(mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8'));
libxml_clear_errors();  // Clear any errors

    // Create a new DOMXPath instance and query all <img> elements
    $xpath = new DOMXPath($doc);
    $img_elements = $xpath->query('//img');

    // Define the base path for images
    $image_base_path = '/wp-content/uploads/sharepointimages/';

    // Iterate over each <img> element and update the 'src' attribute
    foreach ($img_elements as $img) {
        $img_src = $img->getAttribute('src');
        $img_path_parts = pathinfo($img_src);
        
        // Extract the directory name (your variable folder name)
        $folder_name = $img_path_parts['dirname'];
        
        // Remove any unwanted elements (like '.') from the folder name if needed
        $folder_name = ltrim($folder_name, '.');
        
        // Build the new 'src' path by appending the folder name and the basename to the base path
        $img_new_src = $image_base_path . $folder_name . '/' . $img_path_parts['basename'];
        
        $img->setAttribute('src', $img_new_src);
    }

    // Save the updated HTML content
    $body_updated = $doc->saveHTML();
    
      // Handle black diamond character
   // $body_updated = mb_convert_encoding($body_updated, 'UTF-8', 'UTF-8');
$body_updated = iconv("UTF-8", "UTF-8//IGNORE", $body_updated);

    return $body_updated;
}
add_shortcode('sharepoint_content', 'my_sharepoint_fetcher_fetch_content');