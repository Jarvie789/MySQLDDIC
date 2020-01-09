<?php

require_once __Dir__ . '/vendor/autoload.php';

if (PHP_SAPI !== 'cli') {
	fwrite(STDOUT, '请使用CLI模式执行脚本！');
	exit;
}

(new QiuTianJia\MySQLDDIC\CreateTable());