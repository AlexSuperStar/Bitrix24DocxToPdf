<?php
define('HOST', getenv('RABBITMQ_HOST') ? getenv('RABBITMQ_HOST') : 'localhost');
define('PORT', getenv('RABBITMQ_PORT') ? getenv('RABBITMQ_PORT') : 5672);
define('USER', getenv('RABBITMQ_USER') ? getenv('RABBITMQ_USER') : 'guest');
define('PASS', getenv('RABBITMQ_PASS') ? getenv('RABBITMQ_PASS') : 'guest');
define('QUEUE', getenv('QUEUE') ? getenv('QUEUE') : 'documentgenerator_create');
define('VHOST', '/');
define('AMQP_DEBUG', getenv('AMQP_DEBUG') !== false ? (bool)getenv('AMQP_DEBUG') : false);
define('DEBUG', getenv('DEBUG') !== false ? (bool)getenv('DEBUG') : false);

include __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class App{
	# TODO убрать лишнее, вывести красивый лог, потом  выложить
}

$rabbit = new App();
$rabbit->run();