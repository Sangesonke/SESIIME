<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Google\Client;
use Google\Service\Sheets;
use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;



$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey =$_SERVER['API_KEY'] ?: 'API key not found.';  // Fetch the API key from the .env file

// Function to create and return Google Sheets client
function getClient() {
    $client = new Client();
    $client->setApplicationName('Google Sheets API PHP');
    $client->setScopes(Sheets::SPREADSHEETS);
    $client->setAuthConfig('sesime-project-bbf33d5a53d1.json'); 
    $client->setAccessType('offline');
    return $client;
}

// Function to get Google Sheets service
function getService() {
    $client = getClient();
    return new Sheets($client);
}

// Function to get the data from Google Sheets
function getSheetData($spreadsheetId, $range) {
    $service = getService();
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    return $response->getValues();
}

// Function to append data to Google Sheets
function appendSheetData($spreadsheetId, $range, $values) {
    $service = getService();
    $body = new Google_Service_Sheets_ValueRange([
        'values' => $values
    ]);
    $params = [
        'valueInputOption' => 'RAW'
    ];
    $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
}

// Function to check for time conflict with 30-minute gap
function is_time_conflict($data, $date, $time, $barber) {
    $bookingDateTime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);

    foreach ($data as $row) {
        if (count($row) < 4) continue; // Skip incomplete rows

        $existing_date = $row[1];
        $existing_time = $row[2];
        $existing_barber = $row[3];

        if ($existing_date == $date && $existing_barber == $barber) {
            $existingDateTime = DateTime::createFromFormat('Y-m-d H:i', $existing_date . ' ' . $existing_time);
            $interval = $existingDateTime->diff($bookingDateTime);

            // Calculate the total minutes difference
            $minutesDifference = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

            // Check if the difference is less than 30 minutes
            if (abs($minutesDifference) < 30) {
                return true;
            }
        }
    }
    return false;
}

// Function to validate the date
function validate_date($date) {
    $currentDate = new DateTime();
    $bookingDate = DateTime::createFromFormat('Y-m-d', $date);

    if ($bookingDate < $currentDate) {
        return false;
    }
    return true;
}

// Function to validate the time
function validate_time($time) {
    $bookingTime = DateTime::createFromFormat('H:i', $time);
    $startTime = DateTime::createFromFormat('H:i', '08:00');
    $endTime = DateTime::createFromFormat('H:i', '17:00');

    if ($bookingTime >= $startTime && $bookingTime <= $endTime) {
        return true;
    }
    return false;
}

// Function to send confirmation email using Brevo

function sendConfirmationEmail($to, $name, $date, $time, $barber) {
    $apiKey =$_SERVER['API_KEY'] ?: 'API key not found.';  // Fetch the API key from the .env file
    $config = Configuration::getDefaultConfiguration()->setApiKey('api-key',$apiKey);
    $api_instance = new TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

    $sendSmtpEmail = new SendSmtpEmail();
    $sendSmtpEmail->setSubject("Your Booking Confirmation!");
    $sendSmtpEmail->setHtmlContent("<html><body>
        <h1>Hello, $name!</h1>
        <p>Thank you for booking with us. We’re excited to see you on <strong>$date</strong> at <strong>$time</strong> with our top-notch barber <strong>$barber</strong>.</p>
        <p>We're ready to give you an amazing experience. If you have any questions or need to make changes, just hit us up.</p>
        <p>See you soon!</p>
        <p>The Sesime Barbershop Team</p>
    </body></html>");
    $sendSmtpEmail->setSender(['name' => 'Sesime Barbershop', 'email' => 'sagemqayi@gmail.com']);
    $sendSmtpEmail->setTo([['email' => $to, 'name' => $name]]);
    $sendSmtpEmail->setParams(['name' => $name, 'date' => $date, 'time' => $time, 'barber' => $barber]);

    try {
        $result = $api_instance->sendTransacEmail($sendSmtpEmail);
        return "Your confirmation email was sent successfully!";
    } catch (Exception $e) {
        return 'Oops! Something went wrong while sending the confirmation email: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email']; 
    $date = $_POST['date'];
    $time = $_POST['time'];
    $barber = $_POST['barber'];

    // Google Sheets setup
    $spreadsheetId = '1WrTRcANa5yOKzT-jWWnVWB_6CiT7Xa4GPOO3TwRt6_c'; 
    $range = 'Bookings'; 

    $data = getSheetData($spreadsheetId, $range);

    $message = '';
    $success = true;

    // Validate booking date
    if (!validate_date($date)) {
        $message = 'Hey now, your booking date cannot be in the past.';
        $success = false;
    }

    // Validate booking time
    if ($success && !validate_time($time)) {
        $message = 'Hey now, your booking time must be between 08:00 AM and 05:00 PM.';
        $success = false;
    }

    // Check for time conflicts with 30-minute gap
    if ($success && is_time_conflict($data, $date, $time, $barber)) {
        $message = 'Sorry :(, your booking conflicts with another booking. Please try again.';
        $success = false;
    }

    if ($success) {
        $values = [[$name, $date, $time, $barber]];
        appendSheetData($spreadsheetId, $range, $values);
        $message = 'Success: Your form has been submitted.';

        // Send confirmation email
        $emailMessage = sendConfirmationEmail($email, $name,$date,$time,$barber);
        $message .= ' ' . $emailMessage;
    }

    echo "<script>
            alert('$message');
            window.location.href = 'index.html'; // Redirect back to the html page
          </script>";
    exit(); // Prevent further execution after handling the form submission
}
?>
