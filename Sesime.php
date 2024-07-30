<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'C:\xampp\htdocs\SESIME\vendor\autoload.php';


use PhpOffice\PhpSpreadsheet\IOFactory;



// function validate_time($booking_time) {
//     $booking_hour = (int)date('H', strtotime($booking_time));
//     return $booking_hour >= 8 && $booking_hour < 17;
// }

function is_time_conflict($worksheet, $booking_date, $booking_time) {
    $highestRow = $worksheet->getHighestRow();
    for ($row = 2; $row <= $highestRow; $row++) {
        $existing_date = $worksheet->getCell("B$row")->getValue();
        $existing_time = $worksheet->getCell("C$row")->getValue();
        if ($existing_date == $booking_date && $existing_time == $booking_time) {
            return true;
        }
    }
    return false;
}

function save_booking($worksheet, $customer_name, $barber, $booking_date, $booking_time) {
    $highestRow = $worksheet->getHighestRow();
    $nextRow = $highestRow + 1;
    $worksheet->setCellValue("A$nextRow", $customer_name);
    $worksheet->setCellValue("B$nextRow", $barber);
    $worksheet->setCellValue("C$nextRow", $booking_date);
    $worksheet->setCellValue("D$nextRow", $booking_time);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['name'];
    $barber = $_POST['barber'];
    $booking_date = $_POST['date'];
    $booking_time = $_POST['time'];

    // if (!validate_time($booking_time)) {
    //     echo json_encode(['message' => 'Error: Booking time must be between 8am and 5pm.']);
    //     exit();
    // }

    $excel_file = 'Bookings.xlsx';

        // Check if the file exists and create it if it doesn't
        if (!file_exists($excel_file)) {
            // Attempt to create the file
            $fileHandle = fopen($excel_file, 'w');
            
            if ($fileHandle) {
                // Optionally, write some initial content to the file
                fwrite($fileHandle, "Booking File Created on " . date('Y-m-d H:i:s') . "\n");
                fclose($fileHandle);
            }
        }

 
    $spreadsheet = IOFactory::load($excel_file);
    $worksheet = $spreadsheet->getActiveSheet();

    if (is_time_conflict($worksheet, $booking_date, $booking_time)) {
        echo json_encode(['message' => 'Error: The selected time conflicts with another booking.']);
        exit();
    }

    save_booking($worksheet, $customer_name, $barber, $booking_date, $booking_time);
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($excel_file);

    echo json_encode(['message' => 'Success: Your form has been submitted.']);
}
?>
