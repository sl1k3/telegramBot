<?php

namespace TelegramBot;

use TelegramTwig\Twig;

class PsyUpgradeBot extends Bot
{
    private static $sChannelId = -1001493539224;

    public static function getToken()
    {
        return '1032203058:AAGZI9--H8pFQITMONLxF_7xXNLx1oihKpE';
    }

//    public static function sendAppeal(array $aData, $iUserId = null)
//    {
//        $iChatId = ($iUserId === null) ? static::$sChannelId : get_field('telegram_chat_id', $iUserId);
//
//        if ($iChatId) {
//            static::sendRequest('sendMessage', [
//                'chat_id' => $iChatId,
//                'text' => static::renderMessage('appeal', $aData),
//                'parse_mode' => 'html'
//            ]);
//
//            if (static::isSuccessResponse()) return true;
//        }
//
//        return false;
//    }

    public static function commandStart()
    {
        return Twig::parse('start.twig');
    }

    public static function commandCommandList()
    {
        return Twig::parse('commandList.twig');
    }

    public static function unknownCommand()
    {
        return 'Неизвестная команда';
    }
}