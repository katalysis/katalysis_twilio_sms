<?php

namespace Concrete\Package\KatalysisTwilioSms\Controller\SinglePage\Dashboard\System\Mail;

use Concrete\Core\Page\Controller\DashboardPageController;
use Config;
use Concrete\Core\Routing\Redirect;

class TwilioSms extends DashboardPageController
{
    public $helpers = array('form');
    protected $error;
    
    public function on_start()
    {
        parent::on_start();
        $this->error = $this->app->make('helper/validation/error');
    }

    public function view($status = '')
    {
        if ($status == 'updated') {
            $this->set("success", t("Twilio SMS settings have been saved."));
        }

        $this->set('form', $this->app->make('helper/form'));
        $this->set('token', $this->app->make('token'));

        // Get settings
        $this->set('enabled', Config::get('katalysis.twilio_sms.enabled', false));
        $this->set('account_sid', Config::get('katalysis.twilio_sms.account_sid', ''));
        $this->set('auth_token', Config::get('katalysis.twilio_sms.auth_token', ''));
        $this->set('from_number', Config::get('katalysis.twilio_sms.from_number', ''));
    }



    public function save()
    {
        $enabled = isset($_POST['enabled']) ? true : false;
        $account_sid = trim($_POST['account_sid']);
        $auth_token = trim($_POST['auth_token']);
        $from_number = trim($_POST['from_number']);
        
        // If no errors, save these values
        if (!$this->error->has()) {

            Config::save('katalysis.twilio_sms.enabled', $enabled);
            Config::save('katalysis.twilio_sms.account_sid', $account_sid);
            Config::save('katalysis.twilio_sms.auth_token', $auth_token);
            Config::save('katalysis.twilio_sms.from_number', $from_number);

            // Reload the view
            $this->redirect('/dashboard/system/mail/twilio_sms', 'updated');

        } else {

            // Use temp settings
            $this->set('enabled', $enabled);
            $this->set('account_sid', $account_sid);
            $this->set('auth_token', $auth_token);
            $this->set('from_number', $from_number);

        }
    }

    public function test()
    {
        $this->error = $this->app->make('helper/validation/error');
        $token = $this->app->make('token');
        
        if (!$token->validate('test_twilio_sms')) {
            $this->error->add($token->getErrorMessage());
            $this->view();
            return;
        }

        $test_number = trim($this->request->request->get('test_number', ''));
        
        if (empty($test_number)) {
            $this->error->add(t('Please enter a phone number to test.'));
            $this->view();
            return;
        }

        try {
            // Include Twilio autoloader
            $autoloadPath = $this->getPackageHandle() . '/vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
            }
            
            // Create a simple SMS service for testing
            $accountSid = Config::get('katalysis.twilio_sms.account_sid');
            $authToken = Config::get('katalysis.twilio_sms.auth_token');
            $fromNumber = Config::get('katalysis.twilio_sms.from_number');

            if (empty($accountSid) || empty($authToken) || empty($fromNumber)) {
                $this->error->add(t('Please configure your Twilio credentials first.'));
                $this->view();
                return;
            }

            $client = new \Twilio\Rest\Client($accountSid, $authToken);
            
            // Format phone number
            $testNumber = $this->formatPhoneNumber($test_number);
            
            $message = $client->messages->create(
                $testNumber,
                [
                    'from' => $fromNumber,
                    'body' => 'Test message from Katalysis Twillio SMS. Configuration is working correctly!'
                ]
            );

            $this->flash('success', t('Test SMS sent successfully! Message SID: %s', $message->sid));
            $this->redirect('/dashboard/system/mail/twilio_sms');

        } catch (\Exception $e) {
            $this->error->add(t('SMS test failed: %s', $e->getMessage()));
            $this->view();
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

    protected function getPackageHandle()
    {
        return 'katalysis_twilio_sms';
    }
}
