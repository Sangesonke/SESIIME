<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'C:\xampp\htdocs\SESIME\vendor\autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract form data
    $name = $_POST['name'];
    $number = $_POST['number'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $barber = $_POST['barber'];

    // Debug: Print form data
    echo "Name: $name<br>";
    echo "Number: $number<br>";
    echo "Date: $date<br>";
    echo "Time: $time<br>";
    echo "Barber: $barber<br>";

    // Create or load the Spreadsheet object
    $filename = 'SESIME Bookings.xlsx';

    if (file_exists($filename)) {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
    } else {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        // Set cell headers for new file
        $worksheet->setCellValue('A1', 'Name')
                  ->setCellValue('B1', 'Number')
                  ->setCellValue('C1', 'Date')
                  ->setCellValue('D1', 'Time')
                  ->setCellValue('E1', 'Barber');
    }

    // Find the next available row
    $lastRow = $worksheet->getHighestRow() + 1;

    // Set the new booking data
    $worksheet->setCellValue('A' . $lastRow, $name)
              ->setCellValue('B' . $lastRow, $number)
              ->setCellValue('C' . $lastRow, $date)
              ->setCellValue('D' . $lastRow, $time)
              ->setCellValue('E' . $lastRow, $barber);

    // Save the Excel file
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);

    $message = "Your booking has been added";

    // Redirect the user back to the booking page
    header("Location: home.html");
    echo "<script type='text/javascript'>alert('$message');</script>";
    exit();
} else {
    echo "The if method has been skipped";
    exit();
}
?>