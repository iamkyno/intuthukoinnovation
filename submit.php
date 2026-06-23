<?php
// ── Booking handler: emails submissions to the studio ──
declare(strict_types=1);

// CONFIG
$TO_EMAIL   = 'mbhelelindo23@gmail.com';
$SITE_NAME  = 'Intuthuiko Innovation — DJ Shoot';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// Helper: clean + required check
function field(string $key, bool $required = true): string {
    $val = trim($_POST[$key] ?? '');
    // strip header-injection attempts
    $val = str_replace(["\r", "\n", "%0a", "%0d"], '', $val);
    return $val;
}

$firstName = field('firstName');
$lastName  = field('lastName');
$djName     = field('djName', false);
$email      = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: '';
$phone      = field('phone');
$location   = field('location');
$genre      = field('genre');
$bookingDate = field('bookingDate');
$message    = field('message', false);

// Server-side validation
$errors = [];
if ($firstName === '')   $errors[] = 'first name';
if ($lastName === '')    $errors[] = 'last name';
if ($email === '')       $errors[] = 'valid email';
if ($phone === '')       $errors[] = 'phone';
if ($location === '')    $errors[] = 'location';
if ($genre === '')       $errors[] = 'genre';
if ($bookingDate === '') $errors[] = 'shoot date';

header('Content-Type: application/json');

if ($errors) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Missing: ' . implode(', ', $errors)]);
    exit;
}

// Build email
$subject = "🎧 New DJ Booking — {$firstName} {$lastName} ({$bookingDate})";

$body  = "NEW DJ SHOOT BOOKING\n";
$body .= "========================\n\n";
$body .= "Name:        {$firstName} {$lastName}\n";
$body .= "DJ Name:     " . ($djName ?: '—') . "\n";
$body .= "Email:       {$email}\n";
$body .= "WhatsApp:    {$phone}\n";
$body .= "Area:        {$location}\n";
$body .= "Genre:       {$genre}\n";
$body .= "Shoot Date:  {$bookingDate}\n\n";
$body .= "Notes:\n" . ($message ?: '—') . "\n\n";
$body .= "------------------------\n";
$body .= "Sent " . date('Y-m-d H:i') . " from the booking page.\n";

$headers  = "From: {$SITE_NAME} <no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ">\r\n";
$headers .= "Reply-To: {$firstName} {$lastName} <{$email}>\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$sent = @mail($TO_EMAIL, $subject, $body, $headers);

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not send. Please WhatsApp us directly.']);
}
