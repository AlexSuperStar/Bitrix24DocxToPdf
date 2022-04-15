<?php
include __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

/*
 * Конвертер битрикс https://transformer-de.bitrix.info/json/add_queue.php
 * Свой http://bitrix24.host.ru/secret_url/add_queue.php
 * */

define('HOST', getenv('RABBITMQ_HOST') ? getenv('RABBITMQ_HOST') : '127.0.0.1');
define('PORT', getenv('RABBITMQ_PORT') ? getenv('RABBITMQ_PORT') : 5672);
define('USER', getenv('RABBITMQ_USER') ? getenv('RABBITMQ_USER') : 'guest');
define('PASS', getenv('RABBITMQ_PASS') ? getenv('RABBITMQ_PASS') : 'guest');
define('VHOST', '/');
define('AMQP_DEBUG', getenv('AMQP_DEBUG') !== false ? (bool)getenv('AMQP_DEBUG') : false);
define('DEBUG', getenv('DEBUG') !== false ? (bool)getenv('DEBUG') : false);

try {
	$exchange = 'bitrix';
	$queue = '';

	file_put_contents('/tmp/abb_traqnsform.log', date('d.m.y H:i:s') . PHP_EOL, FILE_APPEND);
	file_put_contents('/tmp/abb_traqnsform.log', json_encode($_POST) . PHP_EOL, FILE_APPEND);
	file_put_contents('/tmp/abb_traqnsform.log', json_encode($_SERVER) . PHP_EOL, FILE_APPEND);
	# получаем задание
	// {"command":"Bitrix\\TransformerController\\Document","params":{"documentId":"9","queue":"documentgenerator_create","file":"https:\/\/bitrix24.ru\/upload\/documentgenerator\/920\/5ln4gt0wbnzh8h8035gkpk0wobvo2s2s\/Schet_faktura_Rossiya_1.docx","fileSize":"29232","formats":{"jpg":"jpg","pdf":"pdf"},"back_url":"https:\/\/bitrix24.ru\/bitrix\/tools\/transformer_result.php?id=XWeti6XidT625428cb0978c"},"QUEUE":"documentgenerator_create","BX_LICENCE":"6b78d816e3","BX_DOMAIN":"https:\/\/bitrix24.ru","BX_TYPE":"BOX","BX_VERSION":"1","BX_HASH":"a31ff1c2c72f10b20d9bf814d0b7"}
	$in = &$_POST;
	$ret = [
		'success' => true,
		'result' => [
			'code' => 400,
		]
	];

	if ($in['command'] == 'Bitrix\\TransformerController\\Document') {
		$queue=$in['QUEUE'];
		$connection = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
		$channel = $connection->channel();
		$channel->queue_declare($queue, false, true, false, false, false, ['x-message-ttl' => ['I', 86400000]]);
		$channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);
		$channel->queue_bind($queue, $exchange);
		$messageBody = json_encode($in['params'], 256);
		$message = new AMQPMessage($messageBody, array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
		$channel->basic_publish($message, $exchange);
		$channel->close();
		$connection->close();
		# Контейнер с конвертором забираем файл url в $in['params']
		# конвертируем в запрошенные форматы
		# echo exec('docker run --rm -it -v '.__DIR__.':/tmp --name libreoffice-headless ipunktbs/docker-libreoffice-headless:latest --convert-to jpg "Доверенность (Россия) 1.docx"');
		# запрашиваем  параметры файла для выгрузки обратно на сайт и отправляем сконвертированные файлы по 1 му, столько раз сколько форматов
		# отправляем подтверждение завершения работы		
	} else {
		throw new Exception('Функция не поддерживается');
	}
} catch (Exception $e) {
	$ret['success'] = false;
	$ret['result']['code'] = 1000;
	$ret['result']['msg'] = $e->getMessage();
}
echo json_encode($ret);