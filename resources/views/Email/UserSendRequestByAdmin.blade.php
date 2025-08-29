@component('mail::message')
    <h2>Hello User,</h2>
    <h3>Your delete account request has been sent to the Admin.</h3>
    
    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
