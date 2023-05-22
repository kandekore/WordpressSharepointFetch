<?php 
function my_sharepoint_fetcher_fetch_folder($folderId, $accessToken) {
    $url = 'https://graph.microsoft.com/v1.0/me/drive/items/' . $folderId . '/children';
    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
        ],
    ]);

    // Handle HTTP errors
    if (is_wp_error($response)) {
        throw new Exception('Failed to fetch folder: ' . $response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response));

    // Check if response is valid JSON
    if ($body === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid response received from Microsoft Graph API');
    }

    $items = [];
    foreach ($body->value as $item) {
        if (isset($item->folder)) {
            // If the item is a folder, fetch its children recursively
            $items = array_merge($items, my_sharepoint_fetcher_fetch_folder($item->id, $accessToken));
        } else {
            // If the item is a file, add it to the items array
            $items[] = $item;
        }
    }

    return $items;
}
