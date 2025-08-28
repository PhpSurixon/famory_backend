@component('mail::message')
<h2>Hello Admin,</h2><h3>


    {{$name}} has requested to delete account. Below to see details
    Email : {{$email}}
    Reason for delete : {{$reason}}


Thanks,<br>
{{ config('app.name') }}
@endcomponent
