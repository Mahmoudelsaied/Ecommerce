@component('mail::message')
Reset Account<br>
welcome  {{ $data['data']->name }}<br>
The body of your message.

@component('mail::button', ['url' => aurl('reset/password/'.$data['token'])])
Button Text
@endcomponent<br>
copy this link
<a href="{{aurl('reset/password/'.$data['token'])}}">{{aurl('reset/password/'.$data['token'])}}</a>
Thanks,<br>
{{ config('app.name') }}
@endcomponent
