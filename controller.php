<?php

namespace Concrete\Package\KatalysisTwilioSms;

use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single as SinglePage;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends Package
{
    protected $pkgHandle = 'katalysis_twilio_sms';
    protected $appVersionRequired = '8.5';
    protected $pkgVersion = '1.1.0';

    public function getPackageName()
    {
        return t('Katalysis Twilio SMS');
    }

    public function getPackageDescription()
    {
        return t('Twilio SMS integration for Concrete CMS form submissions and notifications.');
    }

    public function on_start()
    {
        // Include Twilio autoloader when package starts
        $autoloadPath = $this->getPackagePath() . '/vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }
    }

    public function install()
    {
        $pkg = parent::install();
        
        // Install dashboard page
        $dashboardPage = SinglePage::add('/dashboard/system/mail/twilio_sms', $this);
        if (is_object($dashboardPage)) {
            $dashboardPage->update([
                'cName' => t('Twilio SMS Settings'),
                'cDescription' => t('Configure Twilio SMS integration settings.')
            ]);
        }
        
        return $pkg;
    }

    public function upgrade()
    {
        parent::upgrade();
        
        // Install dashboard page if it doesn't exist
        $dashboardPage = Page::getByPath('/dashboard/system/mail/twilio_sms');
        if (!is_object($dashboardPage) || $dashboardPage->isError()) {
            $dashboardPage = SinglePage::add('/dashboard/system/mail/twilio_sms', $this);
            if (is_object($dashboardPage)) {
                $dashboardPage->update([
                    'cName' => t('Twilio SMS Settings'),
                    'cDescription' => t('Configure Twilio SMS integration settings.')
                ]);
            }
        }
    }

    public function uninstall()
    {
        // Remove dashboard page
        $dashboardPage = Page::getByPath('/dashboard/system/mail/twilio_sms');
        if (is_object($dashboardPage) && !$dashboardPage->isError()) {
            $dashboardPage->delete();
        }
        
        parent::uninstall();
    }
}
