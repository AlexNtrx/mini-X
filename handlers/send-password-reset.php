<?php
// Käsittelee salasanan palautuspyynnön ja lähettää sähköpostin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../functions/init.php";
$conn = dbConnect();

$email = trim($_POST['email'] ?? '');
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

if (empty($email)) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ole hyvä ja anna sähköpostiosoite.']);
        exit;
    }
    header('Location: ../index.php?page=forgot-password');
    exit;
}

$token = createPasswordResetToken($conn, $email);
$success = false;
$errorMessage = '';

if ($token) {
    $mailResult = sendPasswordResetEmail($email, $token);
    if ($mailResult === true) {
        $success = true;
    } else {
        $errorMessage = $mailResult;
    }
} else {
    $errorMessage = "Sähköpostiosoitetta ei löytynyt tai tili on poistettu käytöstä.";
}

if ($isAjax) {
    header('Content-Type: application/json');
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Palautuslinkki on lähetetty sähköpostiisi! Tarkista postilaatikkosi.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $errorMessage
        ]);
    }
    exit;
}

// Normaali lomakelähetys (ei-AJAX): uudelleenohjaus sivulle ilman HTML:ää handler-kansiossa
if ($success) {
    header('Location: ../index.php?page=forgot-password&status=sent');
} else {
    $_SESSION['reset_error'] = $errorMessage;
    header('Location: ../index.php?page=forgot-password&status=error');
}
exit();
