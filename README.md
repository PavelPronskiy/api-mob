API-Mob

## News Collection
------------------

Работа со списками новостей.
`GET api.project/news?since_id={since_id}&max_id={max_id}&count={count}&important={important}`

Получение экземпляра новости по id
`GET api.project/news/{id}`

Получение тела новости с заданным id
`GET api.project/news/{id}/content`


## Articles
-----------

Список всех разделов статей
`GET api.project/article_types`

Получение списков статей для заданного раздела articleType
`GET api.project/articles/{articleType}?since_id={since_id}&max_id={max_id}&count={count}`

Получение экземпляра статьи по id
`GET api.project/articles/{id}`

Получение тела статьи с заданным id
`GET api.project/articles/{id}/content`


## Clinics
----------

Список всех регионов
`GET api.project/regions`

Список всех клиник
`GET api.project/clinics?count={count}&since_id={since_id}&since_hits={since_hits}`

Получение экземпляра клиники по id
`GET api.project/clinics/{id}`

Получение "О клинике" по id
`GET api.project/clinics/{id}/about`

Получение коллекции отзывов для клиники с заданным id
`GET api.project/clinics/{id}/feedbacks?since_id={since_id}&max_id={max_id}&count={count}`


## Webinars
-----------

Список всех вебинаров
`GET api.project/webinars?since_id={since_id}&max_id={max_id}&count={count}`

Получение вебинара по id
`GET api.project/webinars/{id}`