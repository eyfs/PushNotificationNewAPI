<?php

/**
 * @see https://github.com/Edujugon/PushNotification
 */

return [
    'gcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'apiKey' => 'My_ApiKey',
        // Optional: Default Guzzle request options for each GCM request
        // See https://docs.guzzlephp.org/en/stable/request-options.html
        'guzzle' => [],
    ],
    'fcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'projectId' => 'my-project-id',
        'jsonFile' => __DIR__ . '/fcmCredentials/file.json',
        // Optional: Default Guzzle request options for each FCM request
        // See https://docs.guzzlephp.org/en/stable/request-options.html
        'guzzle' => [],
    ],
    'apn' => [
        'certificate' => __DIR__ . '/iosCertificates/apns-dev-cert.pem',
        'passPhrase' => 'secret', //Optional
        'passFile' => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
        'dry_run' => true,
    ],
];
