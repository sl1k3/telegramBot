<?php

require_once __DIR__ . '/vendor/autoload.php';

use Telegram\PsyUpgradeBot;

PsyUpgradeBot::init();
PsyUpgradeBot::handle();