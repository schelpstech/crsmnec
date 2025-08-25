<?php
header('Content-Type: application/json');

// Database credentials
$host = 'localhost';
$dbname = 'nec';
$uname = 'root';
$pd = 'pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $uname, $pd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = htmlspecialchars(trim($_POST['fullname'] ?? ''));
    $gender = htmlspecialchars(trim($_POST['gender'] ?? ''));
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $education_section = htmlspecialchars(trim($_POST['education_section'] ?? ''));

    // Validate required fields
    if (
        empty($fullname) ||  empty($gender) || empty($phone) ||
        empty($email) || empty($education_section)
    ) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    try {
        // Check if email already exists
        $checkQuery = "SELECT COUNT(*) FROM educators_registrations WHERE email = :email";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();
        $emailExists = $checkStmt->fetchColumn();

        if ($emailExists > 0) {
            http_response_code(409); // Conflict
            echo json_encode(['success' => false, 'message' => 'This email has already been registered.']);
            exit;
        }

        // Generate unique reference
        do {
            $ref = "VEC25" . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $query = "SELECT COUNT(*) FROM educators_registrations WHERE regid = :ref";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':ref', $ref);
            $stmt->execute();
            $exists = $stmt->fetchColumn();
        } while ($exists > 0);

        $query = "INSERT INTO educators_registrations 
            (regid,  fullname, gender, phone, email, education_section)
            VALUES 
            (:ref, :fullname, :gender, :phone, :email, :education_section)";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':ref', $ref);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':education_section', $education_section);

        $stmt->execute();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => "Registration successful! Thank you, $fullname."
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error saving data: ' . $e->getMessage()
        ]);
    }
}
exit;
