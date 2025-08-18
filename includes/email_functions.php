<?php
/**
 * Email Functions for Shiksha Mitra
 * Simple email system for sending Special IDs and notifications
 */

// Check if we're in local development mode
function isLocalDevelopment() {
    return ($_SERVER['HTTP_HOST'] === 'localhost' || 
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false || 
            strpos($_SERVER['HTTP_HOST'], '.local') !== false);
}

// Show email notification for local development
function showLocalEmailNotification($email, $subject, $type = 'info') {
    $colors = [
        'success' => ['bg' => '#10b981', 'title' => '✅ EMAIL SENT'],
        'info' => ['bg' => '#7c3aed', 'title' => '📧 EMAIL SENT'], 
        'warning' => ['bg' => '#f59e0b', 'title' => '⚠️ EMAIL SENT'],
        'error' => ['bg' => '#ef4444', 'title' => '❌ EMAIL SENT']
    ];
    
    $color = $colors[$type] ?? $colors['info'];
    
    echo "<div style='position: fixed; top: 20px; right: 20px; z-index: 9999; background: white; border: 3px solid {$color['bg']}; border-radius: 12px; padding: 20px; max-width: 400px; box-shadow: 0 8px 25px rgba(0,0,0,0.2); font-family: Arial, sans-serif;'>";
    echo "<h6 style='color: {$color['bg']}; margin: 0 0 10px 0; font-size: 14px; font-weight: bold;'>{$color['title']} (Local Mode)</h6>";
    echo "<p style='margin: 5px 0; font-size: 12px; color: #333;'><strong>To:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p style='margin: 5px 0; font-size: 12px; color: #333;'><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>";
    echo "<p style='margin: 10px 0 5px 0; font-size: 11px; color: #666; font-style: italic;'>Email would be sent in production mode</p>";
    echo "<button onclick='this.parentElement.style.display=\"none\"' style='background: {$color['bg']}; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; float: right; font-size: 11px;'>Close</button>";
    echo "<div style='clear: both;'></div>";
    echo "</div>";
}

function sendSpecialIDEmail($email, $full_name, $special_id, $community_name) {
    $subject = "🎉 Shiksha Mitra - Your Application Approved! / आवेदन स्वीकृत भयो!";
    
    $message = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f8fafc; }
            .special-id { background: #7c3aed; color: white; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0; }
            .nepali { font-family: 'Mukti', sans-serif; color: #6b7280; }
            .footer { background: #374151; color: white; padding: 15px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>🎓 Shiksha Mitra - शिक्षा मित्र</h1>
            <h2>Welcome to Our Educational Community!</h2>
        </div>
        
        <div class='content'>
            <h3>Dear " . htmlspecialchars($full_name) . ",</h3>
            
            <p><strong>🎉 Congratulations! Your application has been approved!</strong></p>
            <p class='nepali'><strong>बधाई छ! तपाईंको आवेदन स्वीकृत भएको छ!</strong></p>
            
            <p>You are now a verified member of the Shiksha Mitra educational community representing <strong>" . htmlspecialchars($community_name) . "</strong>.</p>
            
            <div class='special-id'>
                <h3>Your Special ID / तपाईंको विशेष आईडी:</h3>
                <h1 style='font-size: 32px; margin: 10px 0; letter-spacing: 3px;'>" . $special_id . "</h1>
                <p>Keep this ID safe and use it to login to Shiksha Mitra</p>
            </div>
            
            <h4>How to Login / कसरी लग इन गर्ने:</h4>
            <ol>
                <li>Go to: <a href='https://shiksha-mitra.np/auth/login.php'>Shiksha Mitra Login</a></li>
                <li>Enter your Special ID: <strong>" . $special_id . "</strong></li>
                <li>Enter the password you created during application</li>
                <li>Click Login and start contributing to educational discussions!</li>
            </ol>
            
            <div style='background: #dbeafe; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                <h4>🔒 Security & Privacy / सुरक्षा र गोपनीयता:</h4>
                <ul>
                    <li>Your Special ID is unique and linked to your verified identity</li>
                    <li>Never share your Special ID or password with others</li>
                    <li>Your personal information is protected and will not be shared</li>
                    <li>You can only participate in discussions related to your community</li>
                </ul>
            </div>
            
            <h4>What's Next / अब के गर्ने:</h4>
            <ul>
                <li>🏠 <strong>Explore Issues:</strong> View and vote on educational problems in your community</li>
                <li>📝 <strong>Post Issues:</strong> Share educational challenges you've observed</li>
                <li>💬 <strong>Join Discussions:</strong> Comment and collaborate on solutions</li>
                <li>🤝 <strong>Connect:</strong> Network with other educators, parents, and students</li>
            </ul>
            
            <p class='nepali' style='font-style: italic;'>
                शिक्षा मित्र समुदायमा स्वागत छ। सँगै मिलेर नेपालको शिक्षा क्षेत्रमा सकारात्मक परिवर्तन ल्याउनुहोस्।
            </p>
            
            <p><strong>Thank you for joining our mission to improve education in Nepal!</strong></p>
            
            <hr>
            <p><small>If you have any questions, please reply to this email or contact our support team.</small></p>
        </div>
        
        <div class='footer'>
            <p>&copy; 2024 Shiksha Mitra - Educational Community Platform</p>
            <p>Building Better Education Together | शिक्षा सुधारमा सबै मिलेर</p>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Shiksha Mitra <noreply@shiksha-mitra.np>" . "\r\n";
    $headers .= "Reply-To: support@shiksha-mitra.np" . "\r\n";
    
    // Check if we're in local development mode
    if (isLocalDevelopment()) {
        showLocalEmailNotification($email, $subject, 'success');
        return true; // Simulate successful sending
    }
    
    // In production, use a proper email service like PHPMailer, SendGrid, etc.
    // For now, using basic mail() function
    return mail($email, $subject, $message, $headers);
}

function sendRejectionEmail($email, $full_name, $reason = '') {
    $subject = "Shiksha Mitra Application Update / आवेदन अपडेट";
    
    $message = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: linear-gradient(135deg, #ef4444, #f87171); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f8fafc; }
            .footer { background: #374151; color: white; padding: 15px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>📧 Shiksha Mitra - शिक्षा मित्र</h1>
            <h2>Application Status Update</h2>
        </div>
        
        <div class='content'>
            <h3>Dear " . htmlspecialchars($full_name) . ",</h3>
            
            <p>Thank you for your interest in joining the Shiksha Mitra educational community.</p>
            
            <p>After careful review, we are unable to approve your application at this time.</p>
            
            " . (!empty($reason) ? "<div style='background: #fef2f2; padding: 15px; border-left: 4px solid #ef4444; margin: 20px 0;'>
                <h4>Reason:</h4>
                <p>" . htmlspecialchars($reason) . "</p>
            </div>" : "") . "
            
            <h4>What You Can Do:</h4>
            <ul>
                <li>🔄 <strong>Reapply:</strong> You can submit a new application with updated information</li>
                <li>📞 <strong>Contact Us:</strong> Reach out if you have questions about the decision</li>
                <li>📋 <strong>Review Requirements:</strong> Ensure all documentation meets our verification standards</li>
            </ul>
            
            <p>We encourage you to reapply once you have addressed any issues with your application.</p>
            
            <p><strong>Thank you for your understanding.</strong></p>
            
            <hr>
            <p><small>If you have questions, please reply to this email or contact our support team.</small></p>
        </div>
        
        <div class='footer'>
            <p>&copy; 2024 Shiksha Mitra - Educational Community Platform</p>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Shiksha Mitra <noreply@shiksha-mitra.np>" . "\r\n";
    $headers .= "Reply-To: support@shiksha-mitra.np" . "\r\n";
    
    // Check if we're in local development mode
    if (isLocalDevelopment()) {
        showLocalEmailNotification($email, $subject, 'error');
        return true; // Simulate successful sending
    }
    
    return mail($email, $subject, $message, $headers);
}

function sendApplicationConfirmationEmail($email, $full_name) {
    $subject = "Application Received - Shiksha Mitra / आवेदन प्राप्त भयो";
    
    $message = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f8fafc; }
            .footer { background: #374151; color: white; padding: 15px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>📋 Shiksha Mitra - शिक्षा मित्र</h1>
            <h2>Application Confirmation</h2>
        </div>
        
        <div class='content'>
            <h3>Dear " . htmlspecialchars($full_name) . ",</h3>
            
            <p><strong>✅ Your application has been successfully received!</strong></p>
            <p class='nepali'><strong>तपाईंको आवेदन सफलतापूर्वक प्राप्त भयो!</strong></p>
            
            <h4>What Happens Next / अब के हुन्छ:</h4>
            <ol>
                <li><strong>Review Process:</strong> Our team will carefully review your application and documents</li>
                <li><strong>Verification:</strong> We will verify your proof of residence and community details</li>
                <li><strong>Decision:</strong> You will receive an email with our decision within 2-3 business days</li>
                <li><strong>Special ID:</strong> If approved, you'll receive your unique Special ID for login</li>
            </ol>
            
            <div style='background: #dbeafe; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                <h4>📋 Review Timeline:</h4>
                <ul>
                    <li>Application received: <strong>Today</strong></li>
                    <li>Review period: <strong>2-3 business days</strong></li>
                    <li>Email notification: <strong>Within 72 hours</strong></li>
                </ul>
            </div>
            
            <h4>Important Notes:</h4>
            <ul>
                <li>🔍 <strong>Document Quality:</strong> Ensure your uploaded documents are clear and readable</li>
                <li>📧 <strong>Email Check:</strong> Monitor your email (including spam folder) for updates</li>
                <li>⏳ <strong>Patience:</strong> We review each application carefully to maintain community integrity</li>
            </ul>
            
            <p class='nepali' style='font-style: italic;'>
                धैर्य गर्नुहोस्। हामी प्रत्येक आवेदनलाई ध्यानपूर्वक समीक्षा गर्छौं।
            </p>
            
            <p><strong>Thank you for your interest in joining Shiksha Mitra!</strong></p>
            
            <hr>
            <p><small>If you have any questions, please reply to this email.</small></p>
        </div>
        
        <div class='footer'>
            <p>&copy; 2024 Shiksha Mitra - Educational Community Platform</p>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Shiksha Mitra <noreply@shiksha-mitra.np>" . "\r\n";
    $headers .= "Reply-To: support@shiksha-mitra.np" . "\r\n";
    
    // Check if we're in local development mode
    if (isLocalDevelopment()) {
        showLocalEmailNotification($email, $subject, 'info');
        return true; // Simulate successful sending
    }
    
    return mail($email, $subject, $message, $headers);
}
?>
