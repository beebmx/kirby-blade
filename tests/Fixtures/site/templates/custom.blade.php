@env('testing')
    <div class="env">Testing environment</div>
@endenv
@env('production')
    <div class="env">Production environment</div>
@endenv
@amiguest
    <div class="amiguest">I am a guest</div>
@endamiguest
<div class="upper">@upper('shout')</div>
<x-dashboard::extra />
