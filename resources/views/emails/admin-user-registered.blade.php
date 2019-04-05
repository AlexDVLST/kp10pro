@component('mail::message')
# Поздравляем, новая регистрация


@component('mail::table')
| Имя Фамилия | Host       | Email      | Телефон      |
|:-----------|:-----------|:-----------|:--------------|
| {{$name.' '.$surname}} | {{$url}} | {{$email}} | {{$phone}} |
@endcomponent

@endcomponent
