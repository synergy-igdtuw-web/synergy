<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method.');
}

// ====== Database connection ======
require_once __DIR__ . '/config.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function renderSuccessPage($participantName)
{
    $safeName = htmlspecialchars($participantName, ENT_QUOTES, 'UTF-8');
    $qrRelativePath = 'img/payment-qr.png';
    $qrAbsolutePath = __DIR__ . '/' . $qrRelativePath;
    $qrAvailable = file_exists($qrAbsolutePath);

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '    <meta charset="UTF-8">';
    echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '    <title>Registration Successful | Ignite 2026</title>';
    echo '    <style>';
    echo '        :root { --bg: #ffc333; --card: #ffffff; --ink: #0f172a; --muted: #475569; --brand: #f59e0b; --brand-dark: #ea580c; --line: #e2e8f0; }';
    echo '        * { box-sizing: border-box; }';
    echo '        body { margin: 0; font-family: Montserrat, Arial, sans-serif; background: radial-gradient(circle at top right, #ffe083, #ffc333 55%); color: var(--ink); min-height: 100vh; display: grid; place-items: center; padding: 1.2rem; }';
    echo '        .card { width: min(760px, 100%); background: var(--card); border: 1px solid rgba(255,255,255,0.75); border-radius: 20px; box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18); overflow: hidden; }';
    echo '        .head { background: linear-gradient(125deg, #fff4cc, #ffd86b); border-bottom: 1px solid var(--line); padding: 1.4rem 1.5rem; }';
    echo '        .tag { margin: 0; font-size: 0.74rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--brand-dark); font-weight: 800; }';
    echo '        h1 { margin: 0.35rem 0 0; font-size: clamp(1.3rem, 2.9vw, 1.95rem); }';
    echo '        .body { padding: 1.3rem 1.5rem 1.6rem; }';
    echo '        .lead { margin: 0; color: var(--muted); line-height: 1.7; }';
    echo '        .name { margin-top: 0.9rem; padding: 0.75rem 0.9rem; background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; font-weight: 700; }';
    echo '        .qr-box { margin-top: 1rem; border: 1px solid #fed7aa; background: #fff7ed; border-radius: 14px; padding: 1rem; text-align: center; }';
    echo '        .qr-title { margin: 0 0 0.45rem; font-size: 1.05rem; font-weight: 800; color: #9a3412; }';
    echo '        .qr-copy { margin: 0; color: #7c2d12; line-height: 1.6; }';
    echo '        .qr-img { margin-top: 0.8rem; width: min(230px, 100%); aspect-ratio: 1 / 1; object-fit: contain; border: 1px solid #fdba74; border-radius: 12px; background: #fff; padding: 10px; }';
    echo '        .soon { margin-top: 0.8rem; padding: 0.85rem; border: 1px dashed #fb923c; border-radius: 10px; color: #9a3412; font-weight: 600; background: #fff; }';
    echo '        .actions { display: flex; gap: 0.7rem; flex-wrap: wrap; margin-top: 1.15rem; }';
    echo '        .btn { border: none; border-radius: 11px; padding: 0.72rem 1rem; text-decoration: none; font-weight: 700; display: inline-block; }';
    echo '        .btn-primary { background: linear-gradient(120deg, #f59e0b, #fde047); color: #111827; }';
    echo '        .btn-secondary { background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; }';
    echo '    </style>';
    echo '</head>';
    echo '<body>';
    echo '    <section class="card">';
    echo '        <header class="head">';
    echo '            <p class="tag">Synergy Sports Fest</p>';
    echo '            <h1>Registration Submitted Successfully</h1>';
    echo '        </header>';
    echo '        <div class="body">';
    echo '            <p class="lead">Your registration form has been submitted successfully. To complete your registration, please make the payment using the QR code/link that is shared below.</p>';
    echo '            <div class="name">Participant: ' . $safeName . '</div>';
    echo '            <div class="qr-box">';
    echo '                <p class="qr-title">Complete Payment</p>';
    echo '                <p class="qr-copy">This section is shown only after a successful form submission.</p>';

    if ($qrAvailable) {
        echo '                <img class="qr-img" src="' . htmlspecialchars($qrRelativePath, ENT_QUOTES, 'UTF-8') . '" alt="Payment QR Code">';
    } else {
        echo '                <div class="soon">Payment QR Link will be provided soon. Please check the Ignite 2026 updates.</div>';
    }

    echo '            </div>';
    echo '            <div class="actions">';
    echo '                <a class="btn btn-primary" href="ignite26.html">Go To Ignite 2026</a>';
    echo '                <a class="btn btn-secondary" href="form.html">Submit Another Response</a>';
    echo '            </div>';
    echo '        </div>';
    echo '    </section>';
    echo '</body>';
    echo '</html>';
}

function handleFileUpload($inputName, $relativeDir, $baseName)
{
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        die("No file uploaded or upload error for {$inputName}.");
    }

    $uploadDir = __DIR__ . '/' . trim($relativeDir, '/\\') . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileExt = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
    $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array(strtolower($fileExt), $allowedExts, true)) {
        die("Invalid file type for {$inputName}. Allowed: JPG, JPEG, PNG, PDF");
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($_FILES[$inputName]['size'] > $maxSize) {
        die("File size exceeds 5MB limit for {$inputName}.");
    }

    $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', $baseName);
    if ($safeBase === null || $safeBase === '') {
        $safeBase = 'participant';
    }
    $safeBase = substr($safeBase, 0, 60);
    $filename = $safeBase . '_' . $inputName . '_' . time() . '.' . strtolower($fileExt);

    $relativePath = trim($relativeDir, '/\\') . '/' . $filename;
    if (strlen($relativePath) > 255) {
        die("Generated file path exceeds column length limit for {$inputName}.");
    }

    $fullPath = $uploadDir . $filename;
    if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $fullPath)) {
        die("Failed to save {$inputName} file.");
    }

    return $relativePath;
}

// ====== Get form data safely ======
$EnrollmentNo = isset($_POST['EnrollmentNo']) ? trim($_POST['EnrollmentNo']) : '';
$RegNo_EnrollNo = $EnrollmentNo; // Unified field for DB column RegNo_EnrollNo
$Name = isset($_POST['Name']) ? trim($_POST['Name']) : '';
$Affiliation = isset($_POST['Affiliation']) ? trim($_POST['Affiliation']) : '';
$Course = isset($_POST['Course']) ? trim($_POST['Course']) : '';
$MobileNo = isset($_POST['MobileNo']) ? trim($_POST['MobileNo']) : '';
$EmailID = isset($_POST['EmailID']) ? trim($_POST['EmailID']) : '';
$CoachComingRaw = isset($_POST['CoachComing']) ? trim($_POST['CoachComing']) : '';
$CoachComing = ($CoachComingRaw === 'Yes') ? 'Yes' : (($CoachComingRaw === 'No') ? 'No' : '');
$CoachFullName = isset($_POST['CoachFullName']) ? trim($_POST['CoachFullName']) : '';
$CoachMobileNoRaw = isset($_POST['CoachMobileNo']) ? trim($_POST['CoachMobileNo']) : '';
$CoachMobileNo = preg_replace('/\D/', '', $CoachMobileNoRaw);

if ($CoachComing === '') {
    die('Please select whether a coach is accompanying you.');
}

if ($CoachComing === 'Yes') {
    if ($CoachFullName === '') {
        die('Coach full name is required when a coach is accompanying you.');
    }
    if (strlen($CoachMobileNo) !== 10) {
        die('Coach mobile number must be exactly 10 digits.');
    }
} else {
    $CoachFullName = '';
    $CoachMobileNo = '';
}

$MobileDigits = preg_replace('/\D/', '', $MobileNo);
if (strlen($MobileDigits) !== 10) {
    die('Mobile number must be exactly 10 digits.');
}
$MobileNo = $MobileDigits;

// ====== Handle file uploads ======
$IDCard = handleFileUpload('IDCard', 'uploads/idcards', $RegNo_EnrollNo);
$PaymentScreenshot = handleFileUpload('PaymentScreenshot', 'uploads/payment_screenshots', $RegNo_EnrollNo);

// ====== Track & Field events ======
$A100 = isset($_POST['A100']) ? 'Yes' : 'No';
$A200 = isset($_POST['A200']) ? 'Yes' : 'No';
$A400 = isset($_POST['A400']) ? 'Yes' : 'No';
$ShotPut = isset($_POST['ShotPut']) ? 'Yes' : 'No';
$allowedRelayEvents = ['4 x 100m'];
$relaySelections = (isset($_POST['Relay']) && is_array($_POST['Relay'])) ? array_values(array_intersect($_POST['Relay'], $allowedRelayEvents)) : [];
$Relay = count($relaySelections) > 0 ? 'Yes' : 'No';

// ====== Indoor event ======
$Chess = isset($_POST['Chess']) ? 'Yes' : 'No';

// ====== Optional events ======
$BBOption = isset($_POST['BBOption']) ? 'Yes' : 'No';
$VBOption = isset($_POST['VBOption']) ? 'Yes' : 'No';
$KKOption = isset($_POST['KKOption']) ? 'Yes' : 'No';
$FBOption = isset($_POST['FBOption']) ? 'Yes' : 'No';

// ====== Prepare SQL ======
$stmt = $conn->prepare("
    INSERT INTO `2026_Participants`
    (RegNo_EnrollNo, Name, Affiliation, Course, MobileNo, EmailID,
     A100, A200, A400, ShotPut, Relay, Chess,
     BBOption, VBOption, KKOption, FBOption,
     CoachComing, CoachFullName, CoachMobileNo,
     IDCard, PaymentScreenshot)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// ====== Bind parameters ======
$stmt->bind_param(
    "sssssssssssssssssssss",
    $RegNo_EnrollNo, $Name, $Affiliation, $Course, $MobileNo, $EmailID,
    $A100, $A200, $A400, $ShotPut, $Relay, $Chess,
    $BBOption, $VBOption, $KKOption, $FBOption,
    $CoachComing, $CoachFullName, $CoachMobileNo,
    $IDCard, $PaymentScreenshot
);

// ====== Execute ======
if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    renderSuccessPage($Name);
    exit;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>