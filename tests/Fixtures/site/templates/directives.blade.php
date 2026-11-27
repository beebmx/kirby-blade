<div class="kt">@kt('**Kirbytext**')</div>
<div class="kti">@kti('*inline*')</div>
<div class="esc">@esc('<b>escaped</b>')</div>
<div class="markdown">@markdown('# Markdown')</div>
<div class="html">@html('<i>encoded</i>')</div>
<div class="url">@url('about')</div>
<div class="css">@css('assets/app.css')</div>
<div class="csrf">@csrf()</div>
<div class="vite">@vite(['resources/css/app.css', 'resources/js/app.js'])</div>
<div class="snippet">@snippet('greeting', ['name' => 'Directive'])</div>
<div class="str">{{ str('STR()->lower()')->lower() }}</div>
@foreach ([1, 2, 3] as $item)
    @once
        <div class="once">Hello Once directive</div>
    @endonce
@endforeach
@guest
    <div class="guest">Guest</div>
@endguest
<div class="page">@page('about')</div>
<div class="current">@page()</div>
<div class="pages">@pages('about', 'home')</div>
<div class="pages-array">@pages(['about', 'home'])</div>
<div class="gist">@gist('https://gist.github.com/beebmx/1')</div>
<div class="gist-file">@gist('https://gist.github.com/beebmx/1', 'example.php')</div>
<div class="attr" @attr(['data-id' => 7, 'hidden' => true])></div>
<div class="attr-pair" @attr('aria-label', 'Close')></div>
<div class="lang">@lang('greeting')</div>
<div class="lang-fallback">@lang('missing', 'Fallback')</div>
<div class="choice">@choice('apples', 1) / @choice('apples', 3)</div>
<div class="dump">@dump(['dumped' => true])</div>
<div class="kti-breaks">@kti("One\nTwo")</div>
<div class="kti-options">@kti("One\nTwo", ['markdown' => ['breaks' => false]])</div>
<div class="react">@viteReactRefresh</div>
