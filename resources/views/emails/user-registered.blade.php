@component('mail::message')
# Поздравляем, Вы успешно зарегистрировались

Сервис позволяет быстро создавать коммерческие предложения и многое другое

Данные для авторизации

@component('mail::table')
| Host       | Email      | Пароль        |
|:-----------|:-----------|:--------------|
| {{$url}} | {{$email}} | {{$password}} |
@endcomponent

@component('mail::button', ['url' => $url])
Перейти на сайт
@endcomponent

С уважением, cервис {{ config('app.name') }}
@endcomponent
