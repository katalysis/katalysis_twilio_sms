<?php

defined('C5_EXECUTE') or die('Access Denied.');

$site = null;
$subject = $site.' '.t('Enquiry Confirmation');

ob_start();

?>
<h1 style="color:#00CCFF;font-weight:600;font-size:20px;">Thank you for your enquiry</h1>
<br/>
<p>A member of our team will contact you shortly.</p><br>
<br/>
<p><strong>Site contact</strong><br/>
Managing Director, Your Site</p>


<?php

$bodyHTML = ob_get_clean();

$submittedData = '';
foreach ($attributes as $value) {
    $submittedData .= $value->getAttributeKey()->getAttributeKeyDisplayName('text').":\r\n";
    $submittedData .= $value->getPlainTextValue()."\r\n\r\n";
}

/* Katalysis amendment - add header and footer */
include 'email_config.php';
$email = $header;
$email .= $bodyHTML;
$email .= $footer;
$bodyHTML = $email;
$body = strip_tags($bodyHTML);

/* SMS Integration - Add after email processing */
// Check if SMS is enabled
if (\Config::get('katalysis.twilio_sms.enabled', false)) {
    // Extract phone number from form data using pattern matching for any form number
    $phoneNumber = '';
    $customerName = '';
    $customerEmail = '';
    
    foreach ($attributes as $attr) {
        $handle = $attr->getAttributeKey()->getAttributeKeyHandle();
        $value = trim($attr->getPlainTextValue());
        
        // Look for phone_X field (where X is any number)
        if (preg_match('/^phone_\d+$/', $handle) && !empty($value)) {
            $phoneNumber = $value;
        }
        
        // Look for name_X field (where X is any number)
        if (preg_match('/^name_\d+$/', $handle) && !empty($value)) {
            $customerName = $value;
        }
        
        // Look for email_X field (where X is any number, for potential future use)
        if (preg_match('/^email_\d+$/', $handle) && !empty($value)) {
            $customerEmail = $value;
        }
    }
    
    // Send SMS if phone number found
    if (!empty($phoneNumber)) {
        try {
            // Get Twilio credentials
            $accountSid = \Config::get('katalysis.twilio_sms.account_sid');
            $authToken = \Config::get('katalysis.twilio_sms.auth_token');
            $fromNumber = \Config::get('katalysis.twilio_sms.from_number');
            
            if (!empty($accountSid) && !empty($authToken) && !empty($fromNumber)) {
                // Include Twilio autoloader
                $autoloadPath = dirname(__DIR__, 2) . '/katalysis_twilio_sms/vendor/autoload.php';
                if (file_exists($autoloadPath)) {
                    require_once $autoloadPath;
                    
                    // Format phone number (basic UK formatting)
                    $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
                    if (substr($cleanPhone, 0, 1) === '0' && strlen($cleanPhone) === 11) {
                        $cleanPhone = '+44' . substr($cleanPhone, 1);
                    } elseif (substr($cleanPhone, 0, 1) !== '+') {
                        $cleanPhone = '+' . $cleanPhone;
                    }
                    
                    // Send SMS
                    $client = new \Twilio\Rest\Client($accountSid, $authToken);
                    $smsMessage = 'Thank you ' . ($customerName ? $customerName . ' ' : '') . 
                                 'for your enquiry. We have received your submission and will contact you soon on ' . 
                                 $phoneNumber . '.';
                    
                    $client->messages->create(
                        $cleanPhone,
                        [
                            'from' => $fromNumber,
                            'body' => $smsMessage
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            // Silently fail - don't break email sending if SMS fails
            error_log('SMS Error: ' . $e->getMessage());
        }
    }
}
