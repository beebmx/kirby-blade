# Kirby Blade

Kirby Blade use Laravel `illuminate/view` and `jenssegers/blade` packages.

This package enables [Laravel Blade](https://laravel.com/docs/11.x/blade) for your own Kirby applications.

## Installation

### Installation with composer

```ssh
composer require beebmx/kirby-blade
```

## What is Blade?

According to Laravel Blade documentation is:

> Blade is the simple, yet powerful templating engine provided with Laravel. Unlike other popular PHP templating engines, Blade does not restrict you from using plain PHP code in your views. In fact, all Blade views are compiled into plain PHP code and cached until they are modified, meaning Blade adds essentially zero overhead to your application. Blade view files use the .blade.php file extension.

## Usage

You can use the power of Blade like [Layouts](https://laravel.com/docs/11.x/blade#layouts-using-template-inheritance), [Sub-Views](https://laravel.com/docs/11.x/blade#including-subviews), [Directives](#Directives), your Custom [If Statements](#if-statements) and [Blade components](#components).

All the documentation about Laravel Blade is in the [official documentation](https://laravel.com/docs/11.x/blade).

### Conflicts

Since Kirby `3.7.0` it's important to add the helpers from `illuminate/support` to your root `index.php` file in your `public` directory.

```php
const KIRBY_HELPER_E = false;
// or
define('KIRBY_HELPER_DUMP', false);
```

This line should be before your `autoload.php` file. The result file should be like: 

```php
<?php

define('KIRBY_HELPER_DUMP', false);

include '../vendor/autoload.php';

// ...
```

## Options

The default values of the package are:

| Option                        | Default          | Values   | Description                         |
|:------------------------------|:-----------------|:---------|:------------------------------------|
| beebmx.kirby-blade.views      | site/cache/views | (string) | Location of the views cached        |
| beebmx.kirby-blade.directives | []               | (array)  | Array with the custom directives    |
| beebmx.kirby-blade.ifs        | []               | (array)  | Array with the custom if statements |

All the values can be updated in the `config.php` file.

### Views

All the views generated are stored in `site/cache/views` directory or wherever you define your `cache` directory, but you can change this easily:

```php
'beebmx.kirby-blade.views' => '/site/storage/views',
```

### Directives

By default Kirby Blade comes with the follows directives:

```php
@js('js/app.js')
@css('css/app.css')
@kirbytext($page->text())
@kt($page->text())
@kirbytextinline($page->text())
@kti($page->text())
@smartypants($page->text())
@esc($string)
@image($page->image())
@svg($file)
@page($id)
@pages($id)
@markdown($page->text())
@html($page->text())
@h($page->text())
@url($page->url())
@u($page->url())
@go($url)
@asset($page->image())
@translate($translation)
@t($translation)
@tc($translation, $count)
@dump($variable)
@csrf()
@snippet($name, $data)
@twitter($username, $text, $title, $class)
@video($url)
@vimeo($url)
@youtube($url)
@gist($url)
```

But you can create your own:

```php
'beebmx.kirby-blade.directives' => [
    'greeting' => function ($text) {
        return "<?php echo 'Hello: ' . $text ?>";
    }
],
```

### If Statements

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

All the documentation related to [Components](https://laravel.com/docs/11.x/blade#components) is in the Laravel website.
