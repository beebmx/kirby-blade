# Kirby Blade

Kirby Blade use Laravel `illuminate/view` and `beebmx/blade` packages.

This package enables [Laravel Blade](https://laravel.com/docs/13.x/blade) for your own Kirby applications.

## Requirements

- PHP `8.4` or higher
- Kirby `4`, `5` or `6`
- `illuminate/view` `12` or `13`

The PHP versions you can use depend on your Kirby version:

| Kirby   | PHP 8.4 | PHP 8.5 | PHP 8.6 |
|---------|:-------:|:-------:|:-------:|
| Kirby 4 |    ✅    |    ❌    |    ❌    |
| Kirby 5 |    ✅    |    ✅    |    ❌    |
| Kirby 6 |    ✅    |    ✅    |    ✅    |

> [!NOTE]
> On PHP `8.6`, use `illuminate/view` `13`. Version `12` works, but triggers `spl_object_hash()` deprecation notices.

### Version support

| Version   | Kirby 3 | Kirby 4 | Kirby 5 | Kirby 6 | PHP  |
|-----------|:-------:|:-------:|:-------:|:-------:|------|
| 7.x       |    ❌    |    ✅    |    ✅    |    ✅    | 8.4+ |
| 6.1 – 6.2 |    ❌    |    ✅    |    ✅    |    ❌    | 8.2+ |
| 6.0       |    ❌    |    ✅    |    ❌    |    ❌    | 8.2+ |
| 5.x       |    ❌    |    ✅    |    ❌    |    ❌    | 8.1+ |
| 4.x       |    ✅    |    ❌    |    ❌    |    ❌    | 8.0+ |
| 1.x – 3.x |    ✅    |    ❌    |    ❌    |    ❌    | 7.1+ |

## Installation

### Installation with composer

```ssh
composer require beebmx/kirby-blade
```

### Upgrading

Upgrading from `6.x`? Check the [upgrade guide](UPGRADING.md).

## What is Blade?

According to Laravel Blade documentation is:

> Blade is the simple, yet powerful templating engine provided with Laravel. Unlike other popular PHP templating engines, Blade does not restrict you from using plain PHP code in your views. In fact, all Blade views are compiled into plain PHP code and cached until they are modified, meaning Blade adds essentially zero overhead to your application. Blade view files use the .blade.php file extension.

## Usage

You can use the power of Blade like [Layouts](https://laravel.com/docs/13.x/blade#layouts-using-template-inheritance), [Sub-Views](https://laravel.com/docs/13.x/blade#including-subviews), [Directives](#Directives), your Custom [If Statements](#if-statements) and [Blade components](#components).

All the documentation about Laravel Blade is in the [official documentation](https://laravel.com/docs/13.x/blade).

## Options

The default values of the package are:

| Option                        | Default          | Values   | Description                                       |
|:------------------------------|:-----------------|:---------|:--------------------------------------------------|
| beebmx.kirby-blade.bootstrap  | true             | (bool)   | Enable blade facade after the plugin is available |
| beebmx.kirby-blade.views      | site/cache/views | (string) | Location of the views cached                      |
| beebmx.kirby-blade.directives | []               | (array)  | Array with the custom directives                  |
| beebmx.kirby-blade.ifs        | []               | (array)  | Array with the custom if statements               |

All the values can be updated in the `config.php` file.

### Views

All the views generated are stored in `site/cache/views` directory or wherever you define your `cache` directory, but you can change this easily:

```php
'beebmx.kirby-blade.views' => '/site/storage/views',
```

### Directives

Kirby Blade comes with the following directives. They call Kirby's classes directly (`Kirby\Cms\Html`, `Kirby\Toolkit\I18n`, ...) instead of Kirby's global helpers, so they keep working even if you disable a helper.

#### Text

| Directive                                   | Description                                               |
|:--------------------------------------------|:----------------------------------------------------------|
| `@kirbytext($text)`, `@kt($text)`           | Renders KirbyText and Markdown                            |
| `@kirbytextinline($text)`, `@kti($text)`    | Renders KirbyText and Markdown without the `<p>` wrapper  |
| `@markdown($text)`                          | Renders Markdown                                          |
| `@smartypants($text)`                       | Applies SmartyPants typography                            |
| `@esc($string)`                             | Escapes a string for HTML (`Str::esc()`)                  |
| `@html($text)`, `@h($text)`                 | Encodes HTML entities (`Html::encode()`)                  |

#### Translations

| Directive                                                   | Description                                                       |
|:------------------------------------------------------------|:------------------------------------------------------------------|
| `@translate($key, $fallback)`, `@t($key, $fallback)`        | Prints a translation                                              |
| `@lang($key, $fallback)`                                    | Alias of `@t`                                                     |
| `@tc($key, $count)`                                         | Prints a translation for a count, like `{{ count }} apples`       |
| `@choice($key, $count)`                                     | Alias of `@tc`                                                    |
| `@tt($key, $replace)`                                       | Prints a translation with placeholders, like `Hello {name}`       |

#### URLs, assets and HTML

| Directive                                                   | Description                                                       |
|:------------------------------------------------------------|:------------------------------------------------------------------|
| `@url($path)`, `@u($path)`                                  | Prints the absolute URL for a path                                |
| `@asset($path)`                                             | Prints the URL of a file in your site, like `assets/logo.svg`     |
| `@css($url, $options)`                                      | Renders a `<link rel="stylesheet">` tag                           |
| `@js($url, $options)`                                       | Renders a `<script>` tag                                          |
| `@vite($entrypoints, $buildDirectory)`                      | Renders the Vite tags from the manifest, or from the dev server   |
| `@viteReactRefresh`                                         | Renders the React refresh script while the Vite dev server runs   |
| `@image($path)`                                             | Renders an `<img>` tag, for the first image of the page if no path is given |
| `@svg($file)`                                               | Renders the content of an SVG file inline                         |
| `@attr($attributes)`                                        | Renders HTML attributes, like `@attr(['data-id' => 7, 'hidden' => true])` |
| `@video($url, $options, $attr)`                             | Embeds a YouTube or Vimeo video, or a video file                  |
| `@youtube($url, $options, $attr)`                           | Embeds a YouTube video                                            |
| `@vimeo($url, $options, $attr)`                             | Embeds a Vimeo video                                              |
| `@gist($url, $file)`                                        | Embeds a GitHub gist, or a single file of it                      |

#### Pages and snippets

| Directive                                                   | Description                                                       |
|:------------------------------------------------------------|:------------------------------------------------------------------|
| `@page($id)`                                                | Prints the id of a page, or of the current page if no id is given |
| `@pages($ids)`                                              | Prints the ids of several pages                                   |
| `@snippet($name, $data)`                                    | Renders a PHP or Blade snippet, see [Snippets](#snippets)         |
| `@snippet($name, $data, slots: true)` ... `@endsnippet`     | Renders a snippet with slots, see [Snippets](#snippets)           |

#### Others

| Directive                                                   | Description                                                       |
|:------------------------------------------------------------|:------------------------------------------------------------------|
| `@csrf()`                                                   | Prints Kirby's CSRF token                                         |
| `@dump($variable)`                                          | Dumps a variable                                                  |
| `@go($url, $code)`                                          | Redirects to the URL, with a `302` status code by default         |

But you can create your own:

```php
'beebmx.kirby-blade.directives' => [
    'greeting' => function ($text) {
        return "<?php echo 'Hello: ' . $text ?>";
    }
],
```

### If Statements

Kirby Blade comes with these if statements:

```php
@auth
    Welcome back {{ $kirby->user()->name() }}
@endauth

@guest
    Please Log In
@endguest
```

Like directives, you can create your own if statements:

```php
'beebmx.kirby-blade.ifs' => [
    'logged' => function () {
        return !!kirby()->user();
    },
],
```

After declaration, you can use it like:

```php
@logged
    Welcome back {{ $kirby->user()->name() }}
@else
    Please Log In
@endlogged
```

### Snippets

Blade snippets are regular [Kirby snippets](https://getkirby.com/docs/guide/templates/snippets) with the `.blade.php` extension. Place them in `site/snippets`, and they can use everything Blade offers:

```php
<!-- ../snippets/header.blade.php -->

<header>
    <h1>{{ $title ?? $page->title() }}</h1>
    <nav>
        @foreach ($site->children()->listed() as $item)
            <a href="{{ $item->url() }}" @class(['active' => $item->isOpen()])>{{ $item->title() }}</a>
        @endforeach
    </nav>
</header>
```

Like any Kirby snippet, they receive `$kirby`, `$site` and `$page`, plus the data you pass to them.

#### Rendering snippets

Render a snippet from a Blade template with the `@snippet` directive, and from a PHP template with Kirby's `snippet()` helper. Blade and PHP snippets can be mixed in both kinds of templates:

```php
<!-- ../templates/default.blade.php -->

@snippet('header', ['title' => 'Welcome'])

<!-- ../templates/default.php -->

<?php snippet('header', ['title' => 'Welcome']) ?>
```

Snippets in subfolders use the path of the folder, and a list of names renders the first snippet found:

```php
@snippet('blocks/image', ['block' => $block])

@snippet(['articles/' . $page->intendedTemplate(), 'articles/default'])
```

To get the HTML as a string instead of printing it, use `$kirby->snippet()`:

```php
@php($header = $kirby->snippet('header', ['title' => 'Welcome']))
```

Since the `site/snippets` directory is also a Blade view location, you can include a Blade snippet with `@include('header')` too. In that case it's a regular Blade view: it receives the variables of the parent view, not Kirby's snippet data.

> [!NOTE]
> If a snippet exists both as `header.php` and `header.blade.php`, the Blade snippet is rendered. A snippet and a template with the same name don't conflict: `@snippet('card')` always renders `site/snippets/card.blade.php`.

#### Snippets with slots

Blade snippets support [slots](https://getkirby.com/docs/guide/templates/snippets#passing-slots-to-snippets), from Blade and PHP templates:

```php
<!-- ../snippets/card.blade.php -->

<article class="card">
    <h2>{{ $title }}</h2>
    {!! $slot !!}
    @if ($slots->footer)
        <footer>{!! $slots->footer !!}</footer>
    @endif
</article>

<!-- ../templates/default.blade.php -->

@snippet('card', ['title' => $page->title()], slots: true)
    <p>The default slot</p>
@endsnippet

<!-- ../templates/default.php -->

<?php snippet('card', ['title' => $page->title()], slots: true) ?>
    <?php slot() ?><p>The default slot</p><?php endslot() ?>
    <?php slot('footer') ?>The footer slot<?php endslot() ?>
<?php endsnippet() ?>
```

> [!NOTE]
> Kirby's slots contain HTML, so print them with `{!! $slot !!}`; `{{ $slot }}` escapes them. Blade's own `@slot` directive belongs to Blade components, so named slots in Blade templates use Kirby's `slot()` and `endslot()` helpers.

#### Snippets from plugins

Plugins can register Blade snippets like any other snippet, and they are rendered with Blade:

```php
Kirby::plugin('acme/cards', [
    'snippets' => [
        'cards/product' => __DIR__ . '/snippets/product.blade.php',
    ],
]);
```

### Components

Now you can use Blade components natively in Kirby 3.
To display a component its required to place your component in
`templates/components` and then you can call it with the prefix `x-` in kebab case.

```php

<!-- ../templates/components/alert.blade.php -->

<x-alert/>


<!-- ../templates/components/button.blade.php -->

<x-button></x-button>

```

If your component is nested deeper inside the `components` directory, you can use the `.` character to indicate the place:

```php

<!-- ../templates/components/inputs/button.blade.php -->

<x-inputs.button/>

```

You can also send data to the components via "slots" and attributes:

````php

<x-alert title="Danger">Message</x-alert>

<!-- ../templates/components/alert.blade.php -->

<div class="alert">
    <div>{{$title}}</div>
    <div>{{ $slot }}</div
</div>

````

All the documentation related to [Components](https://laravel.com/docs/13.x/blade#components) is in the Laravel website.

## Conflicts

Kirby declares some global helpers whose names clash with helpers from Laravel and other packages. The most common one is `e()`, which Laravel's `illuminate/support` package declares too, which ends with a `Cannot redeclare function e()` error.

When Kirby Blade is installed with Composer, it disables Kirby's `e()`, `dump()` and `go()` helpers for you by defining these constants:

```php
KIRBY_HELPER_E = false
KIRBY_HELPER_DUMP = false
KIRBY_HELPER_GO = false
```

As a result:

- `e()` is Laravel's helper, which escapes HTML and returns the result instead of echoing it. Blade's `{{ }}` echoes don't depend on it.
- `dump()` and `go()` aren't available unless another package declares them, for example `symfony/var-dumper` for `dump()`. Otherwise, use the Kirby classes behind them:

```php
use Kirby\Cms\Helpers;
use Kirby\Cms\Response;

Helpers::dump($variable); // instead of dump($variable)

Response::go('/blog'); // instead of go('/blog')
```

### Defining the constants yourself

Kirby Blade only defines a constant that isn't defined yet, so you can still define them yourself. You need to when Kirby Blade isn't loaded through Composer's autoloader, or when Composer loads Kirby's helpers before Kirby Blade's. Define them in the `index.php` file of your `public` directory, before including `autoload.php`:

```php
<?php

const KIRBY_HELPER_E = false;
const KIRBY_HELPER_DUMP = false;
const KIRBY_HELPER_GO = false;

include '../vendor/autoload.php';

// ...
```

To keep one of Kirby's helpers, define its constant as `true` instead, for example `const KIRBY_HELPER_GO = true;`. Don't do this for `e()`, because Laravel's `e()` would clash with it.
