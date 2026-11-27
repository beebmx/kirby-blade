# Upgrade Guide

## Upgrading to 7.1.0 from 7.0.x

### High impact changes

#### Kirby's `e()`, `dump()` and `go()` helpers are disabled

Kirby Blade now defines the `KIRBY_HELPER_E`, `KIRBY_HELPER_DUMP` and `KIRBY_HELPER_GO` constants as `false` when Composer loads it. You no longer need to define them yourself to avoid the `Cannot redeclare function e()` error, which also fixes running `vendor/bin/pest` directly in a site that uses Kirby Blade.

As a result, Kirby doesn't declare these helpers anymore:

- `e()` is Laravel's helper, which escapes HTML and returns the result. Kirby's `e($condition, $value)`, which echoes `$value` when `$condition` is true, is no longer available.
- `dump()` and `go()` aren't available unless another package declares them, for example `symfony/var-dumper` for `dump()`.

If your templates, snippets or controllers call `dump()` or `go()`, use the Kirby classes behind them:

```php
use Kirby\Cms\Helpers;
use Kirby\Cms\Response;

Helpers::dump($variable); // instead of dump($variable)

Response::go('/blog'); // instead of go('/blog')
```

Or keep Kirby's helper by defining its constant as `true` in the `index.php` file of your `public` directory, before including `autoload.php`:

```php
const KIRBY_HELPER_DUMP = true;
const KIRBY_HELPER_GO = true;
```

If you already define these constants as `false` in your `index.php`, nothing changes: Kirby Blade only defines a constant that isn't defined yet.

## Upgrading to 7.x from 6.x

Kirby Blade `7.x` adds support for Kirby `6` while keeping support for Kirby `4` and `5`.

### Requirements

| Dependency        | 6.x            | 7.x                 |
|:------------------|:---------------|:--------------------|
| PHP               | `8.2+`         | `8.4+`              |
| Kirby             | `4`, `5`       | `4`, `5`, `6`       |
| `illuminate/view` | `10` – `13`    | `12`, `13`          |

> [!NOTE]
> The PHP versions you can use depend on your Kirby version: Kirby `4` supports PHP up to `8.4`, so a Kirby `4` site needs exactly PHP `8.4`. Kirby `5` supports PHP `8.4` and `8.5`, and Kirby `6` supports PHP `8.4` to `8.6`.
>
> On PHP `8.6`, use `illuminate/view` `13`. Version `12` works, but triggers `spl_object_hash()` deprecation notices.

### Updating the dependency

Update the version constraint in your `composer.json`:

```json
"beebmx/kirby-blade": "^7.0"
```

Then run:

```ssh
composer update beebmx/kirby-blade
```

After updating, clear the compiled views, since they may reference removed directives:

```ssh
kirby view:clear
```

### High impact changes

#### The `bootstrap` option is enabled by default

`beebmx.kirby-blade.bootstrap` now defaults to `true`, so Blade is ready as soon as the plugins are loaded.

If you want the previous behavior, disable it in your `config.php`:

```php
'beebmx.kirby-blade.bootstrap' => false,
```

Even with `bootstrap` disabled, the `Blade` facade, the `view()` helper, Blade snippets and the `view:clear` and `make:component` commands now boot Blade on demand.

#### The `@twitter` directive was removed

The directive called a `twitter()` helper that no longer exists in Kirby, so it failed whenever it was used.

If you need it, register your own directive:

```php
'beebmx.kirby-blade.directives' => [
    'twitter' => function (string $username) {
        return "<?php echo Kirby\Cms\Html::a('https://x.com/' . $username, '@' . $username) ?>";
    },
],
```

### Medium impact changes

#### Bound attributes of dynamic components are escaped

Attributes bound with `:` on a `<x-dynamic-component>` that renders a class component were printed without escaping, unlike the same attributes on a regular component tag:

```php
<x-dynamic-component component="profile" :title="$userInput" />
```

They are now escaped like on any other component. If you relied on passing HTML through such an attribute, pass it as component data or use `{!! !!}` in the component view instead.

#### `{{ }}` accepts null and enums

Printing `null` with `{{ }}` threw a `TypeError` and broke the whole page; it now prints nothing. Backed enums print their value, and strings with invalid UTF-8 characters print with a replacement character (`�`) instead of an empty string.

#### Anonymous components work without an application namespace

Anonymous components no longer need an `App\` PSR-4 entry in your `composer.json`. Before, a site without it, like Kirby's Plainkit and Starterkit, failed with `Unable to detect application namespace` on any `<x-...>` tag. The namespace is still needed for class components and the `make:component` command.

#### Blade snippets always render from the snippets directory

Before, if `site/templates/card.blade.php` and `site/snippets/card.blade.php` both existed, `snippet('card')` rendered the template. Now the snippet in `site/snippets` is always rendered.

The snippets directory is still available as a lookup location, so `@include('card')` keeps working as before.

#### Blade snippets and templates registered by plugins

Blade snippets now follow the same lookup order as Kirby: for each name, the `site/snippets` directory first and then the snippets registered by plugins. This means:

- A list of fallback names, like `snippet(['missing', 'card'])`, now finds Blade snippets too.
- Blade snippets registered by plugins (`'snippets' => ['card' => __DIR__ . '/snippets/card.blade.php']`) are rendered with Blade, instead of as plain PHP.
- Blade templates registered by plugins are rendered with Blade, instead of failing with a "View not found" error.

#### Blade templates and snippets return rendered strings

`Beebmx\KirbyBlade\Template::render()` and `Beebmx\KirbyBlade\Snippet::factory()` now return the rendered HTML as a `string`, instead of an `Illuminate\View\View` object.

If you call `snippet('name', return: true)` for a Blade snippet, you now receive a string.

#### Errors show the real exception

Exceptions thrown inside a Blade view are no longer hidden behind the following error:

```
Return value must be of type string, Illuminate\View\View returned
```

Now you see the original exception, wrapped in an `Illuminate\View\ViewException` that names the view file.

#### Vite asset URLs no longer contain a double slash

The `@vite` directive and the `vite()` helper generated asset URLs with a double slash between the site URL and the build directory:

```html
<!-- 6.x -->
<link rel="stylesheet" href="https://example.com//build/assets/app-C_Q8FkwN.css" />
<script type="module" src="https://example.com//build/assets/app-CxBqiQ7E.js"></script>

<!-- 7.x -->
<link rel="stylesheet" href="https://example.com/build/assets/app-C_Q8FkwN.css" />
<script type="module" src="https://example.com/build/assets/app-CxBqiQ7E.js"></script>
```

Browsers usually load both, so no change is needed in your templates. If you match these URLs somewhere else, update them; for example in tests, a Content Security Policy, cache rules or a CDN configuration.

The development server URLs (when the `hot` file exists) are not affected.

#### Directive fixes

Some directives didn't receive their second argument, because Blade passes the whole expression to a directive as a single string:

- `@gist($url)` failed with a `ParseError`. It now renders the gist script, and `@gist($url, $file)` embeds a single file.
- `@dump($variable)` printed the variable twice. It now prints it once.
- `@go($url, $code)` and `@vite($entrypoints, $buildDirectory)` now pass exactly the arguments you write. Before, the default `302` and `'build'` were appended after them.

#### New directives

- `@attr($attributes)` renders HTML attributes with `Kirby\Cms\Html::attr()`, for example `<div @attr(['data-id' => $page->id(), 'hidden' => true])>`.
- `@endsnippet` closes a snippet opened with `@snippet($name, $data, slots: true)`.
- `@viteReactRefresh` renders the React refresh script while the Vite dev server is running.
- `@lang` and `@choice` are aliases of `@t` and `@tc`.

If you registered your own `attr`, `lang`, `choice`, `endsnippet` or `viteReactRefresh` directive with the `beebmx.kirby-blade.directives` option, yours still wins, since custom directives are registered last.

#### Blade snippets support slots

Before, using a Blade snippet with slots dropped the snippet and only printed the content between `snippet()` and `endsnippet()`. Now Blade snippets go through Kirby's snippet flow, so `$slot` and `$slots` work:

```php
@snippet('card', ['title' => $page->title()], slots: true)
    <p>The default slot</p>
@endsnippet
```

Blade snippets now also receive the same data as regular snippets, like `$kirby`, `$site` and `$page`, and like regular snippets they throw an exception if you pass your own `slot` or `slots` variables.

#### Directives call Kirby's classes instead of the global helpers

The directives no longer depend on Kirby's global helpers (`page()`, `pages()`, `snippet()`, `kirbytext()`, `kirbytextinline()`). They call the classes behind them (`$kirby->site()->find()`, `$kirby->snippet()`, `$kirby->kirbytext()`), so they keep working if a helper is disabled or replaced by another package. The output is the same.

### Low impact changes

#### Directives are registered by `Template::boot()`

`Beebmx\KirbyBlade\Template::setDirectives()` and `Template::setIfStatements()` are now `static` and receive the `Blade` instance:

```php
// 6.x
protected function setDirectives(): void

// 7.x
protected static function setDirectives(Blade $blade): void
```

The new `Template::boot()` method creates the Blade engine and registers the directives and if statements once per request. Use the `beebmx.kirby-blade.directives` and `beebmx.kirby-blade.ifs` options instead of extending the `Template` class whenever possible.

#### The `make:component` command

- The `--force` option is now available, and overwrites an existing component class and view.
- When the class can't be created, because its name is reserved by PHP or it already exists, the command stops without creating the view.

#### The compiled views directory is created when Blade boots

`Beebmx\KirbyBlade\Template::setViewDirectory()` and the `$views` property were removed. The directory from the `beebmx.kirby-blade.views` option is now created once, when Blade boots, instead of every time a template is looked up.

#### Template component signature

The `template` component now receives the same arguments as Kirby's own component:

```php
// 6.x
public function __invoke(Kirby $kirby, string $name, ?string $contentType = null)

// 7.x
public function __invoke(Kirby $kirby, string $name, string $type = 'html', string $defaultType = 'html')
```

#### Template constructor

`Beebmx\KirbyBlade\Template` no longer receives the unused `Kirby` instance, so its constructor matches Kirby's own `Template` class:

```php
// 6.x
new Template($kirby, 'home', 'html', 'html');

// 7.x
new Template('home', 'html', 'html');
```

### Upgrading Kirby to 6

If you upgrade your site to Kirby `6` at the same time, follow the official guides as well:

- [Upgrade to Kirby 6 for site developers](https://getkirby.com/docs/guide/updates/update-to-v6/site-developers)
- [Upgrade to Kirby 6 for plugin developers](https://getkirby.com/docs/guide/updates/update-to-v6/plugin-developers)

In Kirby `6`, regular PHP templates receive the `$slot` and `$slots` variables like snippets, so don't pass your own data with those names.
