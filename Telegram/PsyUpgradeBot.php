<?php

namespace Telegram;

use TelegramTwig\Twig;

class PsyUpgradeBot extends Bot
{
    protected static $sProxy = '191.101.106.123:11846';

    protected static $sUserPwd = 'user38467:lj5kh7';

    private static $sChannelId = -1001457250558;

    private static $sSiteApuUrl = 'https://psyupgrade.com/wp-json';

    public static function getToken()
    {
        return '1032203058:AAGZI9--H8pFQITMONLxF_7xXNLx1oihKpE';
    }

    public static function handle()
    {
        $aRequest = self::getRequest();

        if (isset($aRequest['action']) && $aRequest['action'] == 'sendAppeal') {
            $iChatId = (isset($aRequest['chat_id'])) ? $aRequest['chat_id'] : self::$sChannelId;

            if ($iChatId !== self::$sChannelId) {
                unset($aRequest['data']['specialty']);
            }

            $sAppeal = Twig::parse('appeal.twig', $aRequest['data']);

            self::sendMessage($sAppeal, $iChatId);
        } else {
            if ($aRequest['message']['chat']['id'] !== self::$sChannelId) {
                parent::handle();
            }
        }
    }

    public static function handlePhoto()
    {
        $aRequest = self::getRequest();
        $aPhotos = $aRequest['message']['photo'];
        $aPhoto = $aPhotos[count($aPhotos) - 1];

        $sFilePath = self::downloadPhoto($aPhoto);

        if ($sFilePath) {
            $sMessage = self::handleCommand('updatePhoto', [
                'url' => $sFilePath,
                'width' => $aPhoto['width'],
                'height' => $aPhoto['height'],
                'size' => $aPhoto['file_size']
            ]);

            self::sendMessage($sMessage, $aRequest['message']['chat']['id']);
            unlink(dirname(__DIR__) . "/$sFilePath");
        }
    }

    public static function downloadPhoto($aPhoto)
    {
        self::sendRequest('getFile', ['file_id' => $aPhoto['file_id']]);
        if (self::isOk()) {
            $sFilePath = self::getResult()['file_path'];
            $sUrl = 'https://api.telegram.org/file/bot' . self::getToken() . "/$sFilePath";

            $ch = curl_init($sUrl);
            $fd = fopen(dirname(__DIR__) . "/$sFilePath", 'w');
            curl_setopt($ch, CURLOPT_FILE, $fd);

            if (static::$sProxy && static::$sUserPwd) {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                curl_setopt($ch, CURLOPT_PROXY, static::$sProxy);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, static::$sUserPwd);
            }

            $bCurlExec = curl_exec($ch);
            curl_close($ch);
            fclose($fd);

            if ($bCurlExec) {
                $sProtocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
                $sUrl = $sProtocol . $_SERVER['HTTP_HOST'];
                return $sUrl . "/$sFilePath";
            }
        }

        return false;
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
        return self::handleCommand('getPayDate');
    }

    public static function commandChprice($sPrice)
    {
        return self::handleCommand('updateFields', [
            'field' => 'price',
            'value' => $sPrice
        ]);
    }

    public static function commandChdesc($sDescription)
    {
        return self::handleCommand('updateFields', [
            'field' => 'content',
            'value' => $sDescription
        ]);
    }

    public static function commandChmode($sMode)
    {
        return self::handleCommand('updateFields', [
            'field' => 'work_mode',
            'value' => $sMode
        ]);
    }

    public static function commandChspec($sSpecialty)
    {
        return self::handleCommand('updateFields', [
            'field' => 'specialty',
            'value' => $sSpecialty
        ]);
    }

    public static function commandSync($sPhone)
    {
        return self::handleCommand('sync', [
            'phone' => $sPhone
        ]);
    }

    public static function handleCommand($sMethod, array $aParams = [])
    {
        $aRequest = self::getRequest();
        $iChatId = $aRequest['message']['chat']['id'];

        $aResponse = self::sendSiteRequest($sMethod, $aParams + ['chat_id' => $iChatId]);

        return $aResponse['result'];
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

    public static function sendAppeal()
    {

    }

    public static function unknownHandler()
    {
        return 'Неизвестная команда';
    }
}