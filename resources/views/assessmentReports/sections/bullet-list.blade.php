<section>

    @if (null !== $section['heading'])
        @component('assessmentReports.sections.heading', ['section' => $section])
        @endcomponent
    @endif

    <ul class="bullet-list__list">
        @foreach($section['text_blocks'] as $item)
            <li>{{ $item['text'] }}</li>
        @endforeach
    </ul>

</section>
