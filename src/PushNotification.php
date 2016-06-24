<?php
namespace Edujugon\PushNotification;


use Edujugon\PushNotification\Contracts\PushServiceInterface;
use GuzzleHttp\Client;

class PushNotification
{

    /**
     * Client to do the request
     *
     * @var \GuzzleHttp\Client $client
     */
    protected $client;

    /**
     * Push Service Provider
     * @var
     */
    protected $service;

    /**
     * Devices' Token where send the notification
     *
     * @var array
     */
    protected $devices_token = [];

    /**
     * data to be sent.
     *
     * @var array
     */
    protected $message = [];

    /**
     * Return property if exit otherwise null.
     *
     * @param $property
     * @return null
     */
    public function __get($property){

        if(property_exists($this,$property))
        {
            return $this->$property;
        }

        if(property_exists($this->service,$property))
        {
            return $this->service->$property;
        }

        return null;

    }

    /**
     * Set the message of the notification.
     *
     * @param array/string $data
     * @return $this
     */
    public function setMessage($data)
    {
        $this->message = is_array($data)? $data : array('message' => $data);

        return $this;
    }


    /**
     * @param array $devices_token
     * @return $this
     */
    public function setDevicesToken($devices_token)
    {
        $this->devices_token = is_array($devices_token) ? $devices_token : array($devices_token);

        return $this;
    }

    /**
     * @param string $api_key
     * @return $this
     */
    public function setApiKey($api_key)
    {
        $this->service->setApiKey($api_key) ;

        return $this;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->service->setConfig($config);

        return $this;
    }

    /**
     * PushNotification constructor.
     * @param Client $client
     * @param PushServiceInterface $service By default GCM
     * @internal param string $api_key
     */
    public function __construct(Client $client = null, PushServiceInterface $service = null)
    {
        $this->client = $client ?: new Client;
        
        $this->service = $service ?: new Gcm;
    }

    /**
     * Send Push Notification
     * 
     * @param  \GuzzleHttp\Client client
     * @return boolean true|false
     */
    public function send(){

        return $this->service->send($this->client,$this->devices_token,$this->message);

    }
}