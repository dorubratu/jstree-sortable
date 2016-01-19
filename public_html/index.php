<?php
require __DIR__ . '/../protected/config/settings.php';
require __DIR__ . '/../dependencies/yiisoft/yii/framework/yii.php';

Yii::$classMap = require __DIR__  . '/../dependencies/composer/autoload_classmap.php';
Yii::createWebApplication(__DIR__ . '/../protected/config/application.php')->run();