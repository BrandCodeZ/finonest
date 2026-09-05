<?php
/**
 * ============================================================
 * Finonest - Submit Loan Application
 * ============================================================
 * Receives the "Apply for Loan" form (posted from apply.php),
 * validates all inputs server-side, and securely stores the
 * application in the loan_applications table using a prepared
 * statement. Then redirects back to apply.php with a flash
 * success/error message.
 * ============================================================
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Only accept POST submissions.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('apply.php');
}

$errors = [];

// ---- CSRF check ----
$token = (string) ($_POST['csrf_token'] ?? '');
if (!csrf_verify($token)) {
    $errors[] = 'Security token expired. Please submit the application again.';
}

// ---- Read + validate inputs ----
$loanType       = post('loan_type');
$fullName       = post('full_name');
$mobileNumber   = post('mobile_number');
$email          = post('email');
$city           = post('city');
$employmentType = post('employment_type');
$monthlyIncome  = post('monthly_income');
$loanAmount     = post('loan_amount');
$message        = trim((string) ($_POST['message'] ?? ''));

if ($loanType === '') {
    $errors[] = 'Please select a loan type.';
}

if ($fullName === '' || mb_strlen($fullName) < 3) {
    $errors[] = 'Please enter your full name (at least 3 characters).';
}

if (preg_match('/^[0-9]{10}$/', $mobileNumber) !== 1) {
    $errors[] = 'Please enter a valid 10-digit mobile number.';
}

if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $errors[] = 'Please enter a valid email address.';
}

if ($city === '') {
    $errors[] = 'Please enter your city.';
}

$allowedEmployment = ['Salaried', 'Self Employed', 'Business Owner', 'Freelancer'];
if (!in_array($employmentType, $allowedEmployment, true)) {
    $errors[] = 'Please select a valid employment type.';
}

$monthlyIncomeNum = filter_var($monthlyIncome, FILTER_VALIDATE_FLOAT);
if ($monthlyIncomeNum === false || $monthlyIncomeNum < 0) {
    $errors[] = 'Please enter a valid monthly income.';
}

$loanAmountNum = filter_var($loanAmount, FILTER_VALIDATE_FLOAT);
if ($loanAmountNum === false || $loanAmountNum <= 0) {
    $errors[] = 'Please enter a valid loan amount.';
}

// ---- Keep form data for repopulation on error ----
$_SESSION['app_form'] = [
    'loan_type'       => $loanType,
    'full_name'       => $fullName,
    'mobile_number'   => $mobileNumber,
    'email'           => $email,
    'city'            => $city,
    'employment_type' => $employmentType,
    'monthly_income'  => $monthlyIncome,
    'loan_amount'     => $loanAmount,
    'message'         => $message,
];

if ($errors) {
    $_SESSION['app_error'] = implode(' ', $errors);
    redirect('apply.php');
}

// ---- Insert the application (prepared statement) ----
try {
    $stmt = $pdo->prepare(
        'INSERT INTO loan_applications
            (loan_type, full_name, mobile_number, email, city, employment_type,
             monthly_income, loan_amount, message)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $loanType,
        $fullName,
        $mobileNumber,
        $email !== '' ? $email : null,
        $city,
        $employmentType,
        $monthlyIncomeNum,
        $loanAmountNum,
        $message !== '' ? $message : null,
    ]);
} catch (PDOException $e) {
    $_SESSION['app_error'] = 'We could not submit your application right now. Please try again later.';
    redirect('apply.php');
}

unset($_SESSION['app_form']);
$_SESSION['app_success'] = '🎉 Thank you, ' . $fullName . '! Your loan application has been received. Our expert will contact you within 24 hours regarding your ' . $loanType . '.';

redirect('apply.php');
