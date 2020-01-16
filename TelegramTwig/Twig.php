<?php

namespace TelegramTwig;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class Twig
{
    /** @var Environment $oTwig */
    private static $oTwig;

    private static function init()
    {
        $oLoader = new FilesystemLoader(dirname(__DIR__) . '/views');
        self::$oTwig = new Environment($oLoader);
    }

    private static function _render($sTemplate, $aData)
    {
        if (!self::$oTwig) {
            self::init();
        }

        try {
            return self::$oTwig->render($sTemplate, $aData);
        } catch (\Exception $e) {
            echo 'Twig Error: ' . $e->getMessage();
            die;
        }
    }

    public static function render($sTemplate, $aData = [])
    {
        echo self::_render($sTemplate, $aData);
    }

    public static function parse($sTemplate, $aData = [])
    {
        return self::_render($sTemplate, $aData);
    }
}