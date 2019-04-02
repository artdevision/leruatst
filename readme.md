# Тестовое задание

## Задача
Требуется 
Сделать реализацию CRUD на апи, с использованием serializer.
Пусть это будет простой блог с сущностями Post и Category
То есть нужно развернуть Симфони 4.1. Установить нужные бандлы под симфони (как минимум Guzzle)
Наполнить тестовыми данными базу. Кстати как сделать это тоже вопрос с подковыркой - подумай. Строго судить не буду если не правильно сделаешь, но если сделаешь по феншую (или докажешь что сделанное тобой - сделано по феншую), то будет плюсом.
У любого Апи есть особенности, у нашего (по данному ТЗ) тоже:
Нужно дополнительно реализовать следующее:

- Если в теле запроса передается:`{"category": {"id": 1}}`
- нужно чтоб твоя реализация искала сущность по переданному идентификатору и заменять ее.

Для сущностей нужно реализовать валидацию. 

## Установка

### Docker-compose

#### Настройка
Докер настроен локально работать через [https://github.com/jwilder/nginx-proxy][jwilder/nginx-proxy]
Для того чтобы завести на 80 порту в файле `docker/docker-compose.yml` раскомментировать следующие строки: 

```yml
#    ports:
#      - "80:80"
#      - "443:443"
#    expose:
#      - "9001"

```
Закомментировать или удалить:
```yml
nginx-proxy:
    external:
      name: nginx_proxy_nginx-proxy
```

В секции сервиса `nginx_srv` убрать в секции `networks` сеть `nginx-proxy`:

```yml
networks:
    - srv_local
    - nginx-proxy
```

#### Запускаем Docker
- в папке `docker` переименовываем файл `.env-example` в `.env`
```bash
cd docker && docker-compose up -d --build
```

Поднимается все окружение: `nginx` `php-fpm 7.2` `postgresql 11.2` 
Ну и бонусом там мемкэш и `pgadmin-4` Я с настройкой заморачиваться не стал, мне хватает DataGrip

### Установка приложения

Как поднялись все контейнеры:

```bash
docker-compose run php_srv /bin/bash
root@container$ cd /var/www/html
root@container$ composer install
root@container$ ./bin/console doctrine:database:create
root@container$ ./bin/console doctrine:migrations:migrate
```

[jwilder/nginx-proxy]: https://github.com/jwilder/nginx-proxy