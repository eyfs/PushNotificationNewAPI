<?php
namespace Edujugon\PushNotification;

use Carbon\Carbon;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\FirebaseCloudMessaging;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Log;

class Fcm extends Gcm
{
    const CACHE_SECONDS = 55 * 60; // 55 minutes

    /**
     * Fcm constructor.
     * Override parent constructor.
     */
    public function __construct()
    {
        $this->config = $this->initializeConfig('fcm');

        $this->url = 'https://fcm.googleapis.com/v1/projects/' . $this->config['projectId'] . '/messages:send';

        $this->client = new Client($this->config['guzzle'] ?? []);
    }

    /**
     * Set the apiKey for the notification
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        throw new Exception('Not available on FCM V1');
    }

    /**
     * Set the projectId for the notification
     * @param string $projectId
     */
    public function setProjectId($projectId)
    {
        $this->config['projectId'] = $projectId;

        $this->url = 'https://fcm.googleapis.com/v1/projects/' . $this->config['projectId'] . '/messages:send';
    }

    /**
     * Set the jsonFile path for the notification
     * @param string $jsonFile
     */
    public function setJsonFile($jsonFile)
    {
        $this->config['jsonFile'] = $jsonFile;
    }

    /**
     * Update the values by key on config array from the passed array. If any key doesn't exist, it's added.
     * @param array $config
     */
    public function setConfig(array $config)
    {
        parent::setConfig($config);

        // Update url
        $this->setProjectId($this->config['projectId']);
        $this->setJsonFile($this->config['jsonFile']);
    }

    /**
     * Set the needed headers for the push notification.
     *
     * @return array
     */
    protected function addRequestHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->getOauthToken(),
            'Content-Type' =>  'application/json',
        ];
    }

    protected function getOauthToken()
    {
        return Cache::remember(
            Str::slug('fcm-v1-oauth-token-' . $this->config['projectId']),
            Carbon::now()->addSeconds(self::CACHE_SECONDS),
            function () {
                $jsonFilePath = $this->config['jsonFile'];

                $googleClient = new GoogleClient();

                $googleClient->setAuthConfig($jsonFilePath);
                $googleClient->addScope(FirebaseCloudMessaging::FIREBASE_MESSAGING);

                $accessToken = $googleClient->fetchAccessTokenWithAssertion();

                $oauthToken = $accessToken['access_token'];
                Log::info("GetOauthToken - oauthToken: {$oauthToken}");

                return $oauthToken;
            }
        );
    }

    /**
     * Send Push Notification
     *
     * @param  array $deviceTokens
     * @param array $message
     *
     * @return \stdClass  GCM Response
     */
    public function send(array $deviceTokens, array $message)
    {

        $jsonData = $message;
        $headers = $this->addRequestHeaders();
        Log::info("FCM HEADERS: " . print_r($headers, true));

        $feedbacks = [];

        foreach ($deviceTokens as $deviceToken) {
            Log::info("DeviceToken: {$deviceToken}");
            try {
                $jsonData['message']['token'] = $deviceToken;
                Log::info("FCM MESSAGE: " . print_r($jsonData, true));

                Log::info("URL: " . $this->url);
                $result = $this->client->post(
                    $this->url,
                    [
                        'headers' => $headers,
                        'json' => $jsonData,
                    ]
                );
                Log::info("POST RESPONSE: " . print_r($result, true));

                $json = $result->getBody();

                $feedbacks[] = json_decode($json, false, 512, JSON_BIGINT_AS_STRING);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                Log::info("CLIENTEXCEPTION RESPONSE: " . print_r($e->getResponse()->getBody()->getContents(), true));
                $response = ['success' => false, 'error' => $e->getResponse()->getBody()->getContents()];
                $feedbacks[] = json_decode(json_encode($response));
            } catch (\Exception $e) {
                Log::info("ERROR RESPONSE: " . print_r($e, true));
                $response = ['success' => false, 'error' => $e->getMessage()];
                $feedbacks[] = json_decode(json_encode($response));
			}

            $this->feedback = $feedbacks;
        }
    }
}