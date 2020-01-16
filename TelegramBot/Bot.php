<?php

namespace TelegramBot;

abstract class Bot
{
    private const API_URL = 'https://api.telegram.org/bot';

    abstract public static function getToken();

    protected static function sendRequest($sMethod, array $aParams = [], array $aPostFields = [])
    {
        $sUrl = self::API_URL . static::getToken() . '/' . $sMethod;
        if ($aParams !== []) {
            $sUrl .= '?' . http_build_query($aParams);
        }

        $ch = curl_init();

        $aOpt = [
            CURLOPT_URL => $sUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $aPostFields,
            CURLOPT_RETURNTRANSFER => true
        ];

        curl_setopt_array($ch, $aOpt);
        $aResponse = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if ($aResponse === null) {
            return [];
        }

        return $aResponse;
    }

    public static function handleRequest()
    {
        $aUpdate = json_decode(file_get_contents('php://input'), true);

        if ($aUpdate) {
            $aMessage = $aUpdate['message'];

            if (isset($aMessage['entities']) && $aMessage['entities'][0]['type'] == 'bot_command') {
                static::executeCommand($aUpdate);
            } elseif (static::unknownCommand()) {
                static::sendRequest('sendMessage', [
                    'chat_id' => $aMessage['chat']['id'],
                    'text' => static::unknownCommand(),
                    'parse_mode' => 'html'
                ]);
            }
        }
    }

    public static function executeCommand(array $aUpdate)
    {
        $aMessage = $aUpdate['message'];
        $aEntiny = $aMessage['entities'][0];

        if (isset($aEntiny) && $aEntiny['type'] == 'bot_command') {
            $sMethod = mb_substr($aMessage['text'], $aEntiny['offset'] + 1, $aEntiny['length'] - 1);
            $sMethod = 'command' . ucfirst($sMethod);
            $sParam = trim(mb_substr($aMessage['text'], $aEntiny['offset'] + $aEntiny['length']));

            if (method_exists(static::class, $sMethod)) {
                $sResponse = (static::class)::$sMethod($aUpdate, $sParam);

                if ($sResponse) {
                    static::sendRequest('sendMessage', [
                        'chat_id' => $aMessage['chat']['id'],
                        'text' => $sResponse,
                        'parse_mode' => 'html'
                    ]);
                }
            }
        }
    }

    public static function unknownCommand()
    {
        return false;
    }

//    public static function executeHandleCommand(array $aResult)
//    {
//        $iChatId = $aResult['message']['chat']['id'];
//        $oSpecialist = getSpecialistByChatId($iChatId);
//
//        if ($oSpecialist) {
//            $sMethod = 'handleCommand' . ucfirst($oSpecialist->meta('handle_command'));
//
//            if (method_exists(static::class, $sMethod)) {
//                $sResponse = (static::class)::$sMethod;
//
//                if ($sResponse !== null) {
//                    static::sendRequest('sendMessage', [
//                        'chat_id' => $iChatId,
//                        'text' => $sResponse,
//                        'parse_mode' => 'html'
//                    ]);
//                }
//            }
//        }
//    }
}