<?php

use App\Enums\Status;
use Illuminate\Support\HtmlString;

it('escapes strings', function () {
    expect(_e('<b>"Tom" & \'Jerry\'</b>'))->toBe('&lt;b&gt;&quot;Tom&quot; &amp; &#039;Jerry&#039;&lt;/b&gt;')
        ->and(_e('&amp;'))->toBe('&amp;amp;')
        ->and(_e('&amp;', false))->toBe('&amp;');
});

it('escapes empty and scalar values', function () {
    expect(_e(null))->toBe('')
        ->and(_e(5))->toBe('5')
        ->and(_e(1.5))->toBe('1.5')
        ->and(_e(true))->toBe('1');
});

it('escapes backed enums by their value', function () {
    expect(_e(Status::Active))->toBe('active');
});

it('does not escape html strings', function () {
    expect(_e(new HtmlString('<b>bold</b>')))->toBe('<b>bold</b>');
});

it('replaces invalid utf-8 instead of returning an empty string', function () {
    expect(_e("Caf\xE9"))->toBe("Caf\u{FFFD}");
});

it('echoes null, enums, numbers and fields in blade templates', function () {
    expect(render(App(), 'echo'))
        ->toContain('<div class="null">[]</div>')
        ->toContain('<div class="enum">[active]</div>')
        ->toContain('<div class="number">[1.5]</div>')
        ->toContain('<div class="field">[Echo &lt;b&gt;&amp;&lt;/b&gt;]</div>');
});
