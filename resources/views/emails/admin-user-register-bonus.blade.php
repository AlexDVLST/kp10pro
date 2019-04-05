@component('mail::message')
# Клиент подтвердил бонус для новых клиентов


@component('mail::table')
| Имя Фамилия | Host         | Компания  | Телефон      | Почта    |
|:------------|:-------------|:----------|:-------------|:---------|
| {{$firstName.' '.$lastName}}| {{$url}} | {{$company}} | {{$phone}} | {{$email}} |
@endcomponent

@endcomponent
