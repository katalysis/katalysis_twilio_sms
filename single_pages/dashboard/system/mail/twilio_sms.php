<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<?php if (isset($error) && $error->has()): ?>
    <div class="alert alert-danger">
        <?= $error->output() ?>
    </div>
<?php endif; ?>
<div class="row justify-content-between">
    <div class="col col-lg-6">
        <form method="post" action="<?= $view->action('save') ?>">
            <?= $token->output('save_twilio_settings') ?>

            <fieldset>
                <legend><?= t('Twilio SMS Configuration') ?></legend>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1"
                            <?= $enabled ? 'checked' : '' ?>>
                        <label class="form-check-label" for="enabled">
                            <?= t('Enable SMS Notifications') ?>
                        </label>
                    </div>
                    <div class="help-block">
                        <?= t('Check this box to enable SMS notifications for form submissions.') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="account_sid" class="control-label"><?= t('Twilio Account SID') ?></label>
                    <input type="text" class="form-control" id="account_sid" name="account_sid"
                        value="<?= h($account_sid) ?>" placeholder="AC...">
                    <div class="help-block">
                        <?= t('Your Twilio Account SID from the Console Dashboard') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="auth_token" class="control-label"><?= t('Twilio Auth Token') ?></label>
                    <input type="password" class="form-control" id="auth_token" name="auth_token"
                        value="<?= h($auth_token) ?>" placeholder="Enter your auth token" autocomplete="new-password"
                        data-lpignore="true">
                    <div class="help-block">
                        <?= t('Your Twilio Auth Token (keep this secure)') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="from_number" class="control-label"><?= t('From Phone Number') ?></label>
                    <input type="tel" class="form-control" id="from_number" name="from_number"
                        value="<?= h($from_number) ?>" placeholder="+441234567890">
                    <div class="help-block">
                        <?= t('Your Twilio phone number in E.164 format (e.g., +441234567890)') ?>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend><?= t('Test SMS') ?></legend>

                <div class="form-group">
                    <label for="test_number" class="control-label"><?= t('Test Phone Number') ?></label>
                    <div class="input-group">
                        <input type="tel" class="form-control" id="test_number" name="test_number"
                            placeholder="+441234567890 or 07123456789">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-secondary" formaction="<?= $view->action('test') ?>"
                                name="ccm-submit-button" value="test">
                                <?= $token->output('test_twilio_sms') ?>
                                <?= t('Send Test SMS') ?>
                            </button>
                        </div>
                    </div>
                    <div class="help-block">
                        <?= t('Enter a phone number to test your SMS configuration.') ?>
                    </div>
                </div>
            </fieldset>



            <div class="ccm-dashboard-form-actions-wrapper">
                <div class="ccm-dashboard-form-actions">
                    <button type="submit" class="btn btn-primary float-end"><?= t('Save Settings') ?></button>
                </div>
            </div>
        </form>
    </div>
    <div class="col col-lg-5">
        <div class="alert alert-info mt-4">
            <h5><i class="fa fa-cogs"></i> <?= t('Integration Options') ?></h5>
            <p><strong><?= t('This package supports two types of form integration:') ?></strong></p>

            <div class="mt-3">
                <strong><?= t('1. Express Forms (Manual):') ?></strong>
                <ol class="mt-2">
                    <li><?= t('Create a form mail template in your theme package e.g') ?><br>
                        <code>packages/your_theme/mail/block_express_form_submission.php</code><br>
                        <?= t('Sample code available here:') ?><br>
                        <code>packages/katalysis_twillio_sms/examples/block_express_form_submission.php</code>
                    </li>
                    <li><?= t('Add the SMS integration code to the template and update messages etc. as required.') ?></li>
                    <li><?= t('Configure the settings here.') ?></li>
                    <li><?= t('Test the integration.') ?></li>
                </ol>
            </div>

            <div class="mt-3">
                <strong><?= t('Express Form Field Pattern Detection:') ?></strong>
                <ul class="mt-2">
                    <li><?= t('SMS code should look for form fields: <code>phone_X</code>, <code>name_X</code>, <code>email_X</code>') ?>
                    </li>
                    <li><?= t('Supports multiple forms: <code>phone_1</code>, <code>phone_2</code>, <code>phone_3</code>, etc.') ?>
                    </li>
                    <li><?= t('Extract customer name for personalization') ?></li>
                    <li><?= t('Sends confirmation SMS when valid phone number is provided') ?></li>
                </ul>
            </div>

            <div class="mt-3">
                <strong><?= t('2. Form Reform (Automatic):') ?></strong>
                <ul class="mt-2">
                    <li><?= t('Create a Form Reform listener in your theme package e.g') ?><br>
                        <code>packages/your_theme/src/FormReformListener.php</code><br>
                        <?= t('Sample code available here:') ?><br>
                        <code>packages/katalysis_twillio_sms/examples/FormReformListener.php</code>
                    </li>                   
                    <li><?= t('Configure Form Reform to trigger <code>on_form_reform_event</code>') ?></li>
                    <li><?= t('Automatically sends emails and SMS') ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Toggle field requirements based on enabled status
        $('#enabled').change(function () {
            var isEnabled = $(this).is(':checked');
            $('#account_sid, #auth_token, #from_number').each(function () {
                if (isEnabled) {
                    $(this).attr('required', true);
                } else {
                    $(this).removeAttr('required');
                }
            });
        }).trigger('change');
    });
</script>
