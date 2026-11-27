<section class="slotted">
    <h2>{{ $title }}</h2>
    {!! $slot !!}
    @if ($slots->footer)
        <footer>{!! $slots->footer !!}</footer>
    @endif
</section>
