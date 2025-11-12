<?php
require 'vendor/autoload.php';
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client;

try {
    $config = Configuration::getDefaultConfiguration()
        ->setApiKey('api-key', 'YOUR_XKEYSIB_KEY_HERE');
    $api = new TransactionalEmailsApi(new Client(), $config);

    $email = new \SendinBlue\Client\Model\SendSmtpEmail([
        'sender' => ['email' => 'YOUR_VERIFIED_SENDER@example.com', 'name' => 'Test Sender'],
        'to' => [['email' => 'YOUR_PERSONAL_EMAIL@example.com']],
        'subject' => 'Brevo Test',
        'htmlContent' => '<h3>This is a Brevo test email</h3>'
    ]);

    $result = $api->sendTransacEmail($email);
    echo "Success! " . json_encode($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
