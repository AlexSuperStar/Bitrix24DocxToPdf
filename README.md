# Bitrix24 Docx в PDF,JPG
## Локальный сервер конвертации Docx в PDF и JPG для Bitrix24

Конвертация производится с посощью LibreOffice в докер контейнере, задание на конвертацию отправляется в RabbitMQ, далее контейнер с LibreOffice забирает задание и запускает процесс конвертации

### Сервер конвертации разбит на 2 части
1. PHP cкрипт отправки задания в RabbitMQ, для коробочной версии может распологаться в директории сайта
2. Контейнер конвертации с LibreOffice

Для работы сервисе необходим менеджер очередей RabbitMQ


Процесс конвертации состоит из нескольких этапов
1. Забор файла для конвертации, адрес должен быть доступен контейнеру
2. Конвертация в выбранные форматы
3. Выгрузка обратно а Bitrix24

docker-compose.yml
```sh
version: '3'
services:
  FileTransformer:
    restart: unless-stopped
    image: alexstar/filetransformer: 
    container_name: FileTransformer
    environment:
     - DEBUG=1
     - AMQP_DEBUG=0
     - RABBITMQ_HOST=rabbit
     - RABBITMQ_PORT=5672
     - RABBITMQ_USER=guest
     - RABBITMQ_PASS=guest
     - QUEUE=documentgenerator_create
    volumes:
      - ./app:/app
  rabbit:
    image: 'rabbitmq:3.6-management-alpine'
    ports:
      - '5672:5672'
      - '15672:15672'
    restart: unless-stopped              
```
## License
MIT
