<?php

namespace Concrete\Package\YourTheme;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Mail\Service as MailService;

class FormReformListener implements EventSubscriberInterface
{
    protected $config;
    protected $mailService;

    public function __construct(Repository $config, MailService $mailService)
    {
        $this->config = $config;
        $this->mailService = $mailService;
    }

    public static function getSubscribedEvents()
    {
        return [
            'on_form_reform_hdrform' => 'handleFormReformSubmission'
        ];
    }

    public function handleFormReformSubmission($event)
    {
        try {
            // Get submission data from the event
            $submissionData = $event->getArgument('submission_data', []);
            
            // Log the raw submission data to understand the structure
            error_log('Your Theme Form Reform Submission Data: ' . print_r($submissionData, true));
            
            if (empty($submissionData)) {
                error_log('Your Theme Form Reform: No submission data received');
                return;
            }

            // Extract form data
            $phoneNumber = $this->extractFieldValue($submissionData, 'phone');
            $customerName = trim($this->extractFieldValue($submissionData, 'first_name') . ' ' . $this->extractFieldValue($submissionData, 'last_name'));
            $customerEmail = $this->extractFieldValue($submissionData, 'email');
                        
            // Send confirmation email if email address provided
            if (!empty($customerEmail)) {
                error_log('Your Theme Form Reform: Sending confirmation email to ' . $customerEmail);
                $this->sendConfirmationEmail($customerEmail, $customerName, $submissionData);
            }
            
            // Send SMS if phone number provided and SMS is enabled
            if (!empty($phoneNumber) && $this->config->get('katalysis.twilio_sms.enabled', false)) {
                error_log('Your Theme Form Reform: Sending SMS to ' . $phoneNumber);
                $this->sendConfirmationSMS($phoneNumber, $customerName);
            }

        } catch (\Exception $e) {
            // Log error but don't break the form submission
            error_log('Your Theme Form Reform integration error: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
        }
    }

    protected function extractFieldValue($submissionData, $fieldType)
    {
        // Look for fields with pattern fieldType_X (where X is any number)
        $pattern = '/^' . $fieldType . '_\d+$/';
        
        foreach ($submissionData as $fieldName => $fieldValue) {
            if (preg_match($pattern, $fieldName)) {
                // Handle array values
                if (is_array($fieldValue)) {
                    $value = implode(', ', $fieldValue);
                } else {
                    $value = trim($fieldValue);
                }
                
                if (!empty($value)) {
                    return $value;
                }
            }
        }
        
        // Also check for exact field name without number
        if (isset($submissionData[$fieldType])) {
            $fieldValue = $submissionData[$fieldType];
            
            // Handle array values
            if (is_array($fieldValue)) {
                $value = implode(', ', $fieldValue);
            } else {
                $value = trim($fieldValue);
            }
            
            if (!empty($value)) {
                return $value;
            }
        }
        
        // Try common variations
        $variations = [
            $fieldType,
            $fieldType . '_1',
            'customer_' . $fieldType,
            'user_' . $fieldType,
            $fieldType . '_number',
            $fieldType . '_address'
        ];
        
        foreach ($variations as $variation) {
            if (isset($submissionData[$variation])) {
                $fieldValue = $submissionData[$variation];
                
                // Handle array values
                if (is_array($fieldValue)) {
                    $value = implode(', ', $fieldValue);
                } else {
                    $value = trim($fieldValue);
                }
                
                if (!empty($value)) {
                    return $value;
                }
            }
        }
        
        return '';
    }

    protected function sendConfirmationEmail($email, $name, $submissionData)
    {
        try {
            $this->mailService->reset();
            $this->mailService->to($email);
            $this->mailService->from('website@yoursite.com', 'Your Site');
            $this->mailService->setSubject('Enquiry Confirmation - Your Site');
            
            // Create email body
            $emailBody = $this->createEmailBody($name, $submissionData);
            $this->mailService->setBodyHTML($emailBody);
            
            $this->mailService->sendMail();
            
        } catch (\Exception $e) {
            error_log('Your Theme Email sending error: ' . $e->getMessage());
        }
    }

    protected function createEmailBody($name, $submissionData)
    {
        ob_start();
        ?>
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff; padding: 20px;">
            <!-- SDS Header -->
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color:#00CCFF;font-weight:600;font-size:24px;margin:0;">Sheldon Davidson Solicitors</h1>
                <p style="color:#666;margin:5px 0 0 0;">Housing Disrepair Specialists</p>
            </div>
            
            <h2 style="color:#00CCFF;font-weight:600;font-size:20px;">Thank you for your housing disrepair enquiry</h2>
            
            <p>Dear <?= !empty($name) ? htmlspecialchars(trim($name)) : 'Customer' ?>,</p>

            <p>Thank you for contacting Us. We have received your enquiry and our team will review your submission.</p>

            <h3 style="color:#333;margin-top:30px;border-bottom:2px solid #00CCFF;padding-bottom:5px;">Your Submission Details:</h3>
            <div style="background:#f9f9f9;padding:20px;border-left:4px solid #00CCFF;margin:20px 0;">
                <?php foreach ($submissionData as $fieldName => $fieldValue): ?>
                    <?php 
                    // Handle array values
                    if (is_array($fieldValue)) {
                        $displayValue = implode(', ', $fieldValue);
                    } else {
                        $displayValue = $fieldValue;
                    }
                    ?>
                    <?php if (!empty($displayValue)): ?>
                        <p style="margin:10px 0;"><strong><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $fieldName))) ?>:</strong><br/>
                        <span style="color:#555;"><?= htmlspecialchars($displayValue) ?></span></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top:40px;padding-top:20px;border-top:1px solid #ddd;">
                <p><strong>Your Contact</strong><br/>
                <span style="color:#666;">Job Title, Your Site</span></p>

                <div style="margin-top:20px;padding:15px;background:#f5f5f5;">
                    <p style="margin:0;font-size:14px;color:#666;">
                        <strong>Contact Information:</strong><br/>
                        Phone: 0123 456 789<br/>
                        Email: enquiries@yoursite.com<br/>
                        Website: www.yoursite.com
                    </p>
                </div>
            </div>
            
            <hr style="margin-top:30px;border:none;border-top:1px solid #ddd;"/>
            <p style="font-size:12px;color:#999;text-align:center;">
                This is an automated confirmation email. Please do not reply to this email.<br/>
                If you need immediate assistance, please call us directly on 0123 456 789.
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function sendConfirmationSMS($phoneNumber, $customerName)
    {
        try {
            // Get Twilio credentials
            $accountSid = $this->config->get('katalysis.twilio_sms.account_sid');
            $authToken = $this->config->get('katalysis.twilio_sms.auth_token');
            $fromNumber = $this->config->get('katalysis.twilio_sms.from_number');
            
            if (empty($accountSid) || empty($authToken) || empty($fromNumber)) {
                return;
            }
            
            // Include Twilio autoloader from the SMS package
            $autoloadPath = dirname(__DIR__, 2) . '/katalysis_twilio_sms/vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
                
                // Format phone number (basic UK formatting)
                $cleanPhone = $this->formatPhoneNumber($phoneNumber);
                
                // Send SMS with housing disrepair specific message
                $client = new \Twilio\Rest\Client($accountSid, $authToken);
                $smsMessage = 'Thank you ' . ($customerName ? trim($customerName) . ' ' : '') . 
                             'for your enquiry. We will contact you within 24 hours to discuss your claim. Call 01234 567 890 for urgent matters.';
                
                $client->messages->create(
                    $cleanPhone,
                    [
                        'from' => $fromNumber,
                        'body' => $smsMessage
                    ]
                );
            }
            
        } catch (\Exception $e) {
            error_log('Your Theme SMS Error: ' . $e->getMessage());
        }
    }

    protected function formatPhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If it starts with 0 and is 11 digits, assume UK number
        if (substr($cleaned, 0, 1) === '0' && strlen($cleaned) === 11) {
            $cleaned = '44' . substr($cleaned, 1);
        }
        
        // Add + prefix
        return '+' . $cleaned;
    }
}
