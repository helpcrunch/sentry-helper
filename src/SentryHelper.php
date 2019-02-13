<?php

namespace HelpcrunchSentry;

class SentryHelper
{
    /**
     * @var \Raven_Client
     */
    private static $ravenClient;

    /**
     * @param string|null $ravenUrl
     * @throws \Raven_Exception
     */
    public static function install(string $ravenUrl = null): void
    {
        if (!$ravenUrl) {
            if (!empty($_SERVER['RAVEN_URL'])) {
                $ravenUrl = $_SERVER['RAVEN_URL'];
            } elseif (defined('RAVEN_URL')) {
                $ravenUrl = RAVEN_URL;
            } else {
                throw new \InvalidArgumentException('No raven url provided');
            }
        }

        self::$ravenClient = new \Raven_Client($ravenUrl);
        self::$ravenClient->install($ravenUrl);
    }

    public static function log($message): void
    {
        if (!self::$ravenClient) {
            return;
        }

        if (is_array($message) || is_object($message)) {
            $message = json_encode($message);
        }

        self::$ravenClient->captureMessage($message, ['log'], ['level' => \Raven_Client::DEBUG]);
    }
}
