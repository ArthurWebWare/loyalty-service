# Loyalty Service

В репозитории находится реализация микро-сервиса, который отвечает за работу 
системы лояльности и может использоваться в розничной торговле или сфере услуг,
везде, где за какую либо операцию (продажу) можно начислить определённое 
количество баллов лояльности, а потом потратить эти баллы на оплату товаров/услуг.

Доступные операции:
- Начисление баллов лояльности за операцию (loyaltyPoints/deposit).
- Отмена начисления баллов за операцию (loyaltyPoints/cancel).
- Оплата покупки баллами лояльности (loyaltyPoints/withdraw).
- Получение текущего баланса по карте лояльности (накопленное количество баллов лояльности) (account/balance).

При начислении баллов лояльности необходимо указать правило начисления.

Правило начисления может быть:
- Относительным — количество начисляемых баллов лояльности рассчитывается как процент от суммы операции.
- Абсолютным — в независимости от суммы операции начисляется фиксированное количество баллов лояльности.

Задача:
1. Архив репозитория распаковать и опубликовать в публичный GIT-репозиторий.
2. В отдельной ветке необходимо провести рефакторинг приложения в соответствии с Best Practices, Вашими предпочтениями и опытом.
3. Оформить Pull/Merge Request на ветку с исходным заданием и передать ссылку рекрутеру.

Рекомендации:
1. Предоставить краткое описание выбранных архитектурных решений.
2. Детализировать историю изменений вместо одного итогового коммита.
3. Соблюдать общепринятые правила оформления кода, документации и истории изменений.
4. Не углубляться в детали реализации непринципиальных моментов.
5. Не тратить на задание больше 1-2 дней.

## Installation

- Windows
```bash
cd test-task-loyalty-service; docker-compose up
docker run -it --user www -v ${pwd}:/var/www test-task-loyalty-service /bin/sh -lc "composer install && cp .env.example .env && php artisan key:generate && php artisan migrate"
```

- Linux
```bash
cd test-task-loyalty-service && docker-compose up
docker run -it --user www -v $PWD:/var/www test-task-loyalty-service /bin/sh -lc "composer install && cp .env.example .env && php artisan key:generate && php artisan migrate"
```

## Usage

- Auth
```
POST /api/v1/auth/register

``` 
Param Json 
```
name
email
password
```

- Login
```
POST /api/v1/auth/login
```
Param Json
```
email
password
```
- Logout
```
GET /api/v1/auth/logout
```

- User data
```
GET /api/v1/user
```
Need to be authenticaded with token bearer


- Accounts

Auth status required

Accounts list
```
GET /api/v1/accounts
```

Account data
```
GET /api/v1/accounts/{type}/{id}
```

Account create
```
POST /api/v1/accounts/
```
Param Json
```
phone
email
card
```

Account activate
```
PATCH /api/v1/accounts/activate/{type}/{id}
```

Account deactivate
```
PATCH /api/v1/accounts/deactivate/{type}/{id}
```

Account balance
```
GET /api/v1/accounts/balance/{type}/{id}
```

-Transactions

Auth status required

Deposit
```
POST /api/v1/transactions/{type}/{id}
```
Param Json
```
points_rule
points_amount
description
payment_id
payment_amount
payment_time
```

Withdraw
```
POST /api/v1/transactions/{type}/{id}/withdraw
```
Param Json
```
points_amount
description
```

Cancel
```
POST /api/v1/transactions/cancel
```
Param Json
```
points_amount
description
```
