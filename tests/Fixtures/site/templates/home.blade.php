<h1>{{ $page->title() }}</h1>
@snippet('plain', ['label' => 'php-snippet'])
@include('partials.greeting')
<x-badge label="component"/>
@kt($page->text())
@auth member @endauth
@guest guest @endguest
