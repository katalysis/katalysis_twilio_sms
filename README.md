# Katalysis Twilio SMS Package

A Concrete CMS package for integrating Twilio SMS notifications with form submissions.

## Features

- Send SMS notifications when forms are submitted
- Automatic phone number detection from form fields
- Configurable message templates
- Dashboard interface for configuration
- SMS activity logging
- Test SMS functionality
- Graceful error handling

## Installation

1. **Install the package:**
   ```bash
   cd httpdocs/packages/katalysis_twilio_sms
   composer install
   ```

2. **Install via Concrete CMS Dashboard:**
   - Go to Dashboard > Extend Concrete CMS > Add Functionality
   - Find "Katalysis Twilio SMS" and click Install

3. **Configure Twilio Settings:**
   - Go to Dashboard > System & Settings > Mail > Twilio SMS Settings
   - Enter your Twilio Account SID, Auth Token, and From Number
   - Enable SMS notifications
   - Customize the message template if desired

## Integration with Existing Forms

To integrate with your existing form controller, add the SMS notification to your form's notifier:

```php
// In your FormController's getNotifier method
use Concrete\Package\KatalysisTwilioSms\Src\KatalysisTwilioSms\Express\Entry\Notifier\Notification\FormSubmissionSmsNotification;

if ($notificationNumber == 1) {
    $notifier->getNotificationList()->addNotification(new MyFormSubmissionEmailNotification($this->app, $provider));
    
    // Add SMS notification
    $notifier->getNotificationList()->addNotification(new FormSubmissionSmsNotification($this->app, $provider));
}
```

## Configuration

### Twilio Settings

- **Account SID**: Your Twilio Account SID from the Console Dashboard
- **Auth Token**: Your Twilio Auth Token
- **From Number**: Your Twilio phone number in E.164 format (e.g., +441234567890)

### Message Templates

The SMS template supports the following placeholders:

- `{site_name}` - Site name (defaults to "Sheldon Davidson Solicitors")
- `{date}` - Current date and time
- `{field_handle}` - Any form field handle (e.g., `{name}`, `{email}`, `{phone}`)

Example template:
```
Thank you {name} for your enquiry to {site_name}. We have received your submission and will contact you soon on {phone}.
```

## Phone Number Detection

The package automatically detects phone numbers from form fields based on:

- Field handles containing "phone", "tel", or "mobile"
- Attribute type "telephone"
- Values that look like phone numbers (10-15 digits)

## Phone Number Formatting

Phone numbers are automatically formatted to E.164 format:

- UK numbers starting with 0 are converted (e.g., 07123456789 → +447123456789)
- International numbers are preserved
- Invalid formats are rejected

## Error Handling

The package is designed to fail gracefully:

- SMS errors won't break form submission
- Errors are logged to PHP error log
- Dashboard shows SMS delivery status
- Invalid phone numbers are skipped

## Testing

Use the "Test SMS" feature in the dashboard to:

- Verify your Twilio configuration
- Test phone number formatting
- Check message delivery

## Database Tables

The package creates the following tables:

### TwilioSmsLog
Stores SMS activity and delivery status:
- `recipient` - Phone number
- `message` - SMS content
- `status` - sent/failed
- `twilio_sid` - Twilio message identifier
- `error_message` - Error details if failed
- `form_entry_id` - Related form submission
- `created_at` - Timestamp

## Requirements

- Concrete CMS 8.5+
- PHP 7.4+
- Twilio account with SMS capability
- Composer

## Dependencies

- `twilio/sdk` - Twilio PHP SDK

## Support

For issues or questions, contact the Katalysis development team.

## License

This package is proprietary software developed by Katalysis for client use.
