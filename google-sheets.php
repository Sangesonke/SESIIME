<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

// Path to your `credentials.json` file
$credentialsPath = 'path/to/your/credentials.json';

// Initialize the Google Client
$client = new Client();
$client->setApplicationName('SESIME Bookings');
$client->setScopes([Sheets::SPREADSHEETS]);
$client->setAuthConfig($credentialsPath);
$client->setAccessType('offline');

// Initialize the Google Sheets API
$service = new Sheets($client);

// ID of the Google Sheet (found in the URL of your Google Sheet)
$spreadsheetId = '1WrTRcANa5yOKzT-jWWnVWB_6CiT7Xa4GPOO3TwRt6_c';

// Function to read data from the sheet
function readSheet($service, $spreadsheetId) {
    $range = 'Sheet1!A:D'; // Change this to your desired range
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();
    
    if (empty($values)) {
        print "No data found.\n";
    } else {
        foreach ($values as $row) {
            // Print columns A to E, which correspond to indices 0 to 3.
            printf("%s, %s, %s, %s\n", $row[0], $row[1], $row[2], $row[3]);
        }
    }
}

// Function to write data to the sheet
function writeSheet($service, $spreadsheetId, $data) {
    $range = 'Sheet1!A:E'; // Change this to your desired range
    $body = new Google_Service_Sheets_ValueRange([
        'values' => $data
    ]);
    $params = [
        'valueInputOption' => 'RAW'
    ];
    $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
}

// Read data from the sheet
readSheet($service, $spreadsheetId);

// Example data to write to the sheet
$data = [
    ['John Doe', 'john.doe@example.com', '01/01/2024', '10:00', 'Mr Dee']
];

// Write data to the sheet
writeSheet($service, $spreadsheetId, $data);

?>
