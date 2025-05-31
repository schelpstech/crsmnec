<?php 
header('Content-Type: application/json');

// Database credentials
$host = 'localhost';
$dbname = 'rebicor4_my_e_church_repo';
$uname = 'rebicor4_shalom';
$pd = 'UNYOpat2017@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $uname, $pd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = htmlspecialchars(trim($_POST['fullname'] ?? ''));
    $gender = htmlspecialchars(trim($_POST['gender'] ?? ''));
    $title = htmlspecialchars(trim($_POST['title'] ?? ''));
    $position = htmlspecialchars(trim($_POST['position'] ?? ''));
    $department = htmlspecialchars(trim($_POST['department'] ?? ''));
    $participation_mode = htmlspecialchars(trim($_POST['participation_mode'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $church_name = htmlspecialchars(trim($_POST['church_name'] ?? ''));

    // Generate unique reference
    $ref = "WLC24" . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);

    // Validate required fields
    if (empty($fullname) || empty($gender) || empty($title) || empty($position) ||
        empty($department) || empty($participation_mode) || empty($phone) || empty($church_name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }
    // Ensure unique ref
    try {
        do {
            $ref = "WLC24" . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $query = "SELECT COUNT(*) FROM registrations WHERE regid = :ref";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':ref', $ref);
            $stmt->execute();
            $exists = $stmt->fetchColumn();
        } while ($exists > 0);

        $query = "INSERT INTO registrations 
                  (regid, fullname, gender, title, position, department, participation_mode, email, phone, church_name)
                  VALUES (:ref, :fullname, :gender, :title, :position, :department, :participation_mode, :email, :phone, :church_name)";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':participation_mode', $participation_mode);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':church_name', $church_name);
        $stmt->bindParam(':ref', $ref);

        $stmt->execute();
        http_response_code(200);
        $named = $title." ".$fullname;
        echo json_encode(['success' => true, 'message' => "Registration successful! Thank you, $named."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error saving data: ' . $e->getMessage()]);
    }
}
exit;
