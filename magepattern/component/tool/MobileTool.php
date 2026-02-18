<?php

namespace Magepattern\Component\Tool;

use Detection\MobileDetect;
use Magepattern\Bootstrap;

class MobileTool
{
    private static ?MobileDetect $instance = null;

    /**
     * Singleton pour MobileDetect
     */
    public static function getInstance(): MobileDetect
    {
        if (self::$instance === null) {
            Bootstrap::getInstance()->load('mobiledetect');
            self::$instance = new MobileDetect();
        }

        return self::$instance;
    }

    /**
     * Helper rapide pour savoir si on est sur mobile
     */
    public static function isMobile(): bool
    {
        return self::getInstance()->isMobile();
    }

    /**
     * Helper rapide pour savoir si on est sur tablette
     */
    public static function isTablet(): bool
    {
        return self::getInstance()->isTablet();
    }
}