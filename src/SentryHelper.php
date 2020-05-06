<?php

namespace Helpcrunch\Helper;

use Raven_Client;
use Throwable;

class SentryHelper
{
    /**
     * List of excluded exceptions from sentry
     */
    const EXCLUDED_EXCEPTIONS = [
        'GuzzleHttp\Exception\ConnectException',
        'GuzzleHttp\Exception\ClientException',
        'Doctrine\DBAL\Exception\ConstraintViolationException',
        'Helpcrunch\Exception\HelpcrunchException',
        'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
        'Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException'
    ];

    /**
     * @var Raven_Client
     */
    private static $ravenClient;

    public static function install(string $ravenUrl = null): void
    {
        if (!$ravenUrl) {
            if (!empty($_SERVER['RAVEN_URL'])) {
                $ravenUrl = $_SERVER['RAVEN_URL'];
            } else {
                throw new \InvalidArgumentException('No raven url provided');
            }
        }

        self::$ravenClient = new Raven_Client($ravenUrl);
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
        self::$ravenClient->captureMessage($message, ['log'], ['level' => Raven_Client::DEBUG]);
    }

    protected static function isExcluded(Throwable $exception): bool
    {
        foreach (self::EXCLUDED_EXCEPTIONS as $excluded) {
            if($exception instanceof $excluded) {
                return true;
            }
        }

        return  false;
    }

    public static function logException($exception): void
    {
        if (!self::$ravenClient || self::isExcluded($exception)) {
            return;
        }

        self::$ravenClient->captureException($exception);
    }
}
