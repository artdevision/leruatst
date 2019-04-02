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

Равзорачиваем тестовые данные данные
```bash
root@container$ ./bin/console doctrine:fixtures:load
```

### Роуты

```bash
root@container$ ./bin/console debug:router
 ---------------------- ----------------- -------- ------ ---------------------------- 
  Name                   Method            Scheme   Host   Path                        
 ---------------------- ----------------- -------- ------ ---------------------------- 
  _twig_error_test       ANY               ANY      ANY    /_error/{code}.{_format}    
  app_category_index     GET               ANY      ANY    /api/category               
  app_category_view      GET               ANY      ANY    /api/category/{id}          
  app_category_create    POST|PUT          ANY      ANY    /api/category/create        
  app_category_update    POST|PUT          ANY      ANY    /api/category/update/{id}   
  app_category_destroy   GET|POST|DELETE   ANY      ANY    /api/category/destroy/{id}  
  app_post_index         GET|POST          ANY      ANY    /api/post                   
  app_post_view          GET               ANY      ANY    /api/post/{id}              
  app_post_create        POST|PUT          ANY      ANY    /api/post/create            
  app_post_update        POST|PUT          ANY      ANY    /api/post/update/{id}       
  app_post_destroy       GET|POST|DELETE   ANY      ANY    /api/post/destroy/{id}      
  fos_js_routing_js      GET               ANY      ANY    /js/routing.{_format}       
 ---------------------- ----------------- -------- ------ ---------------------------- 
```

#### /api/post GET|POST

Параметры:

- `p` - номер страницы
- `perpage` - количество элементов в выборке

Методом `POST` можно передать JSON или параметры:
```json
{
  "category" :{ 
    "id": 1
   }
}
``` 
или 
```json
{
  "category" :{ 
    "id": [1, 2]
   }
}
```
С данными параметрами будет применен фильтр постов по указанным ID категорий

Пример ответа:
```json
{
    "total": 59,
    "page": 1,
    "perpage": 50,
    "items": [
        {
            "id": 101,
            "title": "categories",
            "author": null,
            "published": true,
            "published_at": 1554136047,
            "created_at": 1554136047,
            "updated_at": 1554136047,
            "preview_text": null,
            "text": "samlpe text",
            "categories": [
                {
                  "id": 13,
                  "name": "Title"
                }
            ]
        }
    ]
 }
```

#### /api/post/{id} GET|POST

Параметры:
- `id` - ID поста.

Ответ:
```json
{
    "id": 83,
    "title": "Title",
    "author": "Janet Bianka Wolf",
    "published": true,
    "published_at": 1541154127,
    "created_at": 1554000134,
    "updated_at": 1554000134,
    "preview_text": "Preview text",
    "text": "Text",
    "categories": [
        {
            "id": 13,
            "name": "Title"
        },
        {
            "id": 25,
            "name": "Title"
        },
        {
            "id": 26,
            "name": "Title"
        }
    ]
}
```

#### /api/post/create POST|PUT

Создаем пост

Параметры POST:
- `title`(string) - required
- `published`(bool) - required
- `categories[]`(array) - идентификаторы категорий
- `text`(string) - required 

или JSON в теле запроса
```json
{
  "title":"categories", 
  "text":"samlpe text", 
  "categories" : [10,12], 
  "published":true
}
```

Ответ в случае успеха аналогичен роуту /api/post/{id}

Ошибка валидации:
```json
{
    "error": true,
    "type": "validation",
    "errors": [
        {
            "published": "Поле обязательно для заполнения"
        }
    ]
}
```
[jwilder/nginx-proxy]: https://github.com/jwilder/nginx-proxy