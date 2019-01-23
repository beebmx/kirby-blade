# Kirby Blade

Kirby Blade use Laravel `illuminate/view` and `jenssegers/blade` packages.

This package enable [Laravel Blade](https://laravel.com/docs/5.7/blade) for your own Kirby applications.

## Installation

### Installation with composer

```ssh
composer require beebmx/kirby-blade
```

## What is Blade?

According to Laravel Blade documentation is:

> Blade is the simple, yet powerful templating engine provided with Laravel. Unlike other popular PHP templating engines, Blade does not restrict you from using plain PHP code in your views. In fact, all Blade views are compiled into plain PHP code and cached until they are modified, meaning Blade adds essentially zero overhead to your application. Blade view files use the .blade.php file extension.

## Usage

You can use the power of Blade like [Layouts](https://laravel.com/docs/5.7/blade#template-inheritance), [Control Structures](https://laravel.com/docs/5.7/blade#control-structures), [Sub-Views](https://laravel.com/docs/5.7/blade#including-sub-views), Directives and your Custom If Statements.

All the documentation about Laravel Blade is in the [official documentation](https://laravel.com/docs/5.7/blade).

## Options

The default values of the package are:

| Option | Default | Values | Description |
|:--|:--|:--|:--|
| beebmx.kirby-blade.views | site/cache/views | (string) | Location of the views cached |
| beebmx.kirby-blade.directives | [] | (array) | Array with the custom directives |
| beebmx.kirby-blade.ifs | [] | (array) | Array with the custom if statements |

All the values can be updated in the `config.php` file.

### Views

All the views generated are stored in `site/cache/views` directory or wherever you define your `cache` directory, but you can change this easily:

```php
'beebmx.kirby-blade.views' => '/site/storage/views',
```

### Directives

By default Kirby Blade comes with 4 directives:

```php
@js('js/app.js')
@css('css/app.css')
@kirbytext($page->text())
@kt($page->text())
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

After declaration you can use it like:

```php
@logged
    Welcome back {{ $kirby->user()->name() }}
@else
    Please Log In
@endlogged
```
