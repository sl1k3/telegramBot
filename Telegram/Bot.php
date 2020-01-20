<?php

namespace Telegram;

abstract class Bot
{
    /** @var string Telegram API URL */
    private const API_URL = 'https://api.telegram.org/bot';

    /** @var string Proxy IP */
    protected static $sProxy;

    /** @var string Proxy login and password in format "login:pass" */
    protected static $sUserPwd;

    /** @var array Telegram API request body */
    protected static $aRequest = [];

    /** @var array Telegram API response body */
    protected static $aResponse = [];

    abstract public static function getToken();

    public static function init()
    {
    }

    final public static function sendRequest($sMethod, array $aParams = [])
    {
        $sUrl = self::API_URL . static::getToken() . '/' . $sMethod;
        if ($aParams !== []) {
            $sUrl .= '?' . http_build_query($aParams);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $sUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (static::$sProxy && static::$sUserPwd) {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($ch, CURLOPT_PROXY, static::$sProxy);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, static::$sUserPwd);
        }

        $aResponse = json_decode(curl_exec($ch), true);

        curl_close($ch);

        if ($aResponse === null) {
            static::$aResponse = [];
            return [];
        }

        static::$aResponse = $aResponse;
        return $aResponse;
    }

    public static function sendMessage($sText, $iChatId, array $aParams = ['parse_mode' => 'html'])
    {
        $aParams = array_replace(['text' => $sText, 'chat_id' => $iChatId], $aParams);
        self::sendRequest('sendMessage', $aParams);
    }

    public static function setWebhook($sUrl)
    {
        self::sendRequest('setWebhook', ['url' => $sUrl]);

        return self::isOk();
    }

    public static function isOk()
    {
        if (isset(static::$aResponse['ok']) && static::$aResponse['ok'] === true) {
            return true;
        }

        return false;
    }

    public static function getResult()
    {
        if (isset(static::$aResponse['result'])) {
            return static::$aResponse['result'];
        }

        return [];
    }

    public static function getRequest()
    {
        $mRequest = file_get_contents('php://input');
        $mRequest = json_decode($mRequest, true);

        if ($mRequest === null) {
            return [];
        }

        return $mRequest;
    }

    public static function getDescription()
    {
        if (isset(static::$aResponse['description'])) {
            return static::$aResponse['description'];
        }

        return '';
    }

    public static function isCommand()
    {
        $aRequest = static::getRequest();

        if (isset($aRequest['message']) && isset($aRequest['message']['entities'][0])) {
            $aEntity = $aRequest['message']['entities'][0];

            if ($aEntity['type'] == 'bot_command') {
                return true;
            }
        }

        return false;
    }

    public static function handle()
    {
        $aRequest = static::getRequest();

        if ($aRequest !== []) {
            $aMessage = $aRequest['message'];

            if (static::isCommand()) {
                static::executeCommand();
            } elseif (static::unknownHandler() !== null) {
                static::sendMessage(static::unknownHandler(), $aMessage['chat']['id']);
            }
        }
    }


    public static function executeCommand()
    {
        if (self::isCommand()) {
            $aRequest = static::getRequest();
            $aMessage = $aRequest['message'];
            $aEntiny = $aMessage['entities'][0];

            $sMethod = mb_substr($aMessage['text'], $aEntiny['offset'] + 1, $aEntiny['length'] - 1);
            $sMethod = 'command' . ucfirst($sMethod);
            $sParam = trim(mb_substr($aMessage['text'], $aEntiny['offset'] + $aEntiny['length']));

            if (method_exists(static::class, $sMethod)) {
                (static::class)::$sMethod($sParam);
            }
        }
    }

    public static function unknownHandler()
    {
        return null;
    }
}