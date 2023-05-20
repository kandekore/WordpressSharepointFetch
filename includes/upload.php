<?php 
// Fetch an access token from Azure AD
function my_sharepoint_fetcher_fetch_access_token() {
    // Check if we have a valid token already in transient storage
    $accessToken = get_transient('my_sharepoint_fetcher_token');

    if (false === $accessToken) {
    //     No valid token found. Request a new one.
        $tenantId = get_option('my_sharepoint_fetcher_tenant_id', '');
        $clientId = get_option('my_sharepoint_fetcher_client_id', '');
        $clientSecret = get_option('my_sharepoint_fetcher_client_secret', '');
        $scope = 'https://graph.microsoft.com/.default';


        $url = 'https://login.microsoftonline.com/' . $tenantId . '/oauth2/v2.0/token';
        $body = [
            'client_id' => $clientId,
            'scope' => $scope,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
        ];

        // Make the request
        $response = wp_remote_post($url, [
            'body' => $body,
]);

    // Check for errors
    if (is_wp_error($response)) {
        return false;
    }

    // Parse the response and store the token
    $body = json_decode(wp_remote_retrieve_body($response));
    $accessToken = $body->access_token;
    set_transient('my_sharepoint_fetcher_token', $accessToken, 3600);  // token is valid for 1 hour
}

return $accessToken; }

function my_sharepoint_fetcher_get_site_id() {
    $accessToken = get_transient('my_sharepoint_fetcher_token');
    $hostname = get_option('my_sharepoint_fetcher_hostname', '');
    $sitePath = get_option('my_sharepoint_fetcher_site_path', '');

    $url = 'https://graph.microsoft.com/v1.0/sites/' . $hostname . ':/' . $sitePath;

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
        ],
    ]);

    if (is_wp_error($response)) {
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response));

    return $body->id;
}

function my_sharepoint_fetcher_get_drive_id($siteId) {
    $accessToken = get_transient('my_sharepoint_fetcher_token');

    $url = 'https://graph.microsoft.com/v1.0/sites/' . $siteId . '/drives';

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
        ],
    ]);

    if (is_wp_error($response)) {
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response));
    

    foreach ($body->value as $drive) {
        if (strpos($drive->webUrl, 'Shared%20Documents') !== false) {
            return $drive->id;
        }
    }

    return false;
    
}

function my_sharepoint_fetcher_list_files($driveId) {
    $accessToken = get_transient('my_sharepoint_fetcher_token');

    $url = 'https://graph.microsoft.com/v1.0/drives/' . $driveId . '/root/children';

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
        ],
    ]);

    if (is_wp_error($response)) {
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response));

    return $body->value;
}
