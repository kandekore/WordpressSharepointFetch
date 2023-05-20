![badmath](https://img.shields.io/badge/license-GPL-blue)

# WordPress SharePoint Document Fetcher

## Table of Contents

- [Description](#Description)
- [Installation](#installation)
- [Usage](#usage)
- [Testing](#testing)
- [Contributors](#Contributors)
- [Questions](#Questions)

## Description

A plugin that fetches content from a Sharepoint site and enables the user to add it to WordPress using a shortcode, a workaround for displaying Word Documents as HTML within WordPress pages, but potentially any kind of document can be imported.

- The Motivation for building this application was I needed a method to display content from complex word documents, keeping index links and images.
- The application was built to provide a method of importing content to WordPress sites from SharePoint
- It solves the problem of having to rebuild documents to display on a website, it also presents an opportunity for end users to use SharePoint as a content managment system.
- The plugin presents future development opportunities of other ways to handle, display or distribute SharePoint documents
- Through the process of building this application I learned how to query the Microsoft Graph API to get drive and file ID's

## Installation

Upload and install the plugin to WordPress

Register an App in Azure AD

Create a Client Secret in Azure AD

Set API Permissions in Azure AD

Add credentials to plugin settings page

## Usage

You can download a copy of the plugin from here [this link.](https://wpsharepointfetch.wordpresswizard.net/)

You create the document in Word

Download the .docx file and save as a .html document

Upload the the .html to SharePoint

Zip and upload the images folder to the plugin

Add a shortcode on the page of where you want to display the doc

## Testing

There is a "Test connection" button on the settings page

## License

This application is covered under the GNU GPL licence

## Contributors

[Darren Kandekore](https://github.com/Kandekore)

## Questions

Please contact me if you have any questions

[Kandekore](https://github.com/Kandekore)

[darren@kandekore.net](mailto:darren@kandekore.net)
