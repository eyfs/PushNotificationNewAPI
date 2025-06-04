<?php

namespace Edujugon\PushNotification\Channels;

class FcmTap3Channel extends GcmChannel
{
    /**
     * {@inheritdoc}
     */
    protected function pushServiceName()
    {
        return 'fcm-tap-3';
    }
}
