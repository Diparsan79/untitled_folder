<?php
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('../index.php');
}

$errors = [];
$success_message = '';

// Get communities for dropdown
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT id, name, district, province FROM communities ORDER BY province, district, name");
$stmt->execute();
$communities = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $community_id = (int)($_POST['community_id'] ?? 0);
    $address_detail = sanitizeInput($_POST['address_detail'] ?? '');
    $occupation = $_POST['occupation'] ?? '';
    $motivation = sanitizeInput($_POST['motivation'] ?? '');
    $document_type = $_POST['document_type'] ?? '';
    $proof_document = $_FILES['proof_document'] ?? null;
    
    // Validation
    if (empty($full_name) || strlen($full_name) < 3) {
        $errors[] = "पूरा नाम आवश्यक छ (Full name is required, minimum 3 characters)";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $errors[] = "Please enter a properly formatted email address";
    }
    
    if (empty($phone) || !preg_match('/^[0-9+\-\s()]{10,15}$/', $phone)) {
        $errors[] = "फोन नम्बर आवश्यक छ (Valid phone number is required)";
    }
    
    if (empty($password) || strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if ($community_id <= 0) {
        $errors[] = "कृपया आफ्नो समुदाय छान्नुहोस् (Please select your community)";
    }
    
    if (empty($address_detail) || strlen($address_detail) < 10) {
        $errors[] = "पूरा ठेगाना आवश्यक छ (Complete address is required, minimum 10 characters)";
    }
    
    if (empty($occupation)) {
        $errors[] = "पेशा छान्नुहोस् (Please select your occupation)";
    }
    
    if (empty($motivation) || strlen($motivation) < 20) {
        $errors[] = "शिक्षा मित्रमा सामेल हुने कारण लेख्नुहोस् (Please explain why you want to join Shiksha Mitra, minimum 20 characters)";
    }
    
    if (empty($document_type)) {
        $errors[] = "प्रमाण कागजातको प्रकार छान्नुहोस् (Please select document type)";
    }
    
    // Validate proof document upload
    if (!$proof_document || $proof_document['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "निवास प्रमाणको कागजात अपलोड गर्नुहोस् (Please upload proof of residence document)";
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($proof_document['type'], $allowed_types)) {
            $errors[] = "केवल JPEG, PNG वा PDF फाइलहरू मात्र अनुमति छ (Only JPEG, PNG, or PDF files are allowed)";
        } elseif ($proof_document['size'] > $max_size) {
            $errors[] = "फाइलको साइज 5MB भन्दा कम हुनुपर्छ (File size must be less than 5MB)";
        }
    }
    
    // Check if email already exists in applications or users
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM user_applications WHERE email = ? UNION SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email, $email]);
        
        if ($stmt->fetch()) {
            $errors[] = "यो इमेल पहिले नै प्रयोग भएको छ (This email is already in use)";
        }
    }
    
    // Process application if no errors
    if (empty($errors)) {
        // Upload proof document
        $upload_dir = __DIR__ . '/../uploads/proof_documents/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($proof_document['name'], PATHINFO_EXTENSION);
        $filename = 'proof_' . uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $filename;
        
        if (move_uploaded_file($proof_document['tmp_name'], $file_path)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO user_applications 
                (full_name, email, phone, password_hash, community_id, address_detail, 
                 proof_document_path, document_type, occupation, motivation) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([
                $full_name, $email, $phone, $hashedPassword, $community_id, 
                $address_detail, 'uploads/proof_documents/' . $filename, 
                $document_type, $occupation, $motivation
            ])) {
                $success_message = "आवेदन सफलतापूर्वक पेश गरियो! (Application submitted successfully!)<br>" .
                                 "हाम्रो टोलीले तपाईंको आवेदन समीक्षा गर्नेछ र 2-3 दिनमा इमेल मार्फत जानकारी दिनेछ।<br>" .
                                 "<em>Our team will review your application and notify you via email within 2-3 days.</em>";
            } else {
                $errors[] = "आवेदन पेश गर्न असफल (Failed to submit application). Please try again.";
                unlink($file_path); // Remove uploaded file on database error
            }
        } else {
            $errors[] = "फाइल अपलोड गर्न असफल (Failed to upload document). Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Shiksha Mitra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-graduation-cap fa-3x text-primary"></i>
                            <h2 class="mt-3">Shiksha Mitra मा आवेदन दिनुहोस्</h2>
                            <p class="text-muted">Apply to join our verified educational community</p>
                            <p class="small text-muted">सत्यापित शैक्षिक समुदायमा सामेल हुन आवेदन दिनुहोस्</p>
                        </div>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>कृपया निम्न त्रुटिहरू सच्याउनुहोस्:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>खाता बनाउनुहोस् (Create Account)
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">पहिले नै खाता छ? (Already have an account?) 
                                <a href="login.php" class="text-decoration-none text-primary fw-bold">Sign In</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="../index.php" class="text-decoration-none text-primary">
                        <i class="fas fa-arrow-left me-2"></i>घर फर्किनुहोस् (Back to Home)
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
