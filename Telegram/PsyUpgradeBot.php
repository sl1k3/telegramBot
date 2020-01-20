<?php

namespace Telegram;

use TelegramTwig\Twig;

class PsyUpgradeBot extends Bot
{
    protected static $sProxy = '191.101.106.123:11846';

    protected static $sUserPwd = 'user38467:lj5kh7';

    private static $sChannelId = -1001493539224;

    private static $sSiteApuUrl = 'https://psyupgrade.com/wp-json';

    public static function getToken()
    {
        return '1032203058:AAGZI9--H8pFQITMONLxF_7xXNLx1oihKpE';
    }

    public static function commandStart()
    {
        return Twig::parse('start.twig');
    }

    public static function commandCmdlist()
    {
        return Twig::parse('commandList.twig');
    }

    public static function commandPaydate()
    {
        $aRequest = self::getRequest();
        $iChatId = $aRequest['message']['chat']['id'];

        $aResponse = self::sendSiteRequest('getPayDate', [
            'chat_id' => $iChatId,
        ]);

        if ($aResponse['ok']) {
            $sMessage = 'Дата истечения платежа: ' . $aResponse['result'];
        } else {
            $sMessage = 'Ошибка выполнения запроса';
        }

        return $sMessage;
    }

    public static function sendSiteRequest($sMethod, array $aParams)
    {
        $sUrl = self::$sSiteApuUrl . '/telegram/bot' . static::getToken() . '/' . $sMethod;
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
            return [];
        }

        return $aResponse;
    }

    public static function unknownHandler()
    {
        return 'Неизвестная команда';
    }
}