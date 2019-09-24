<section class="numerical-list">

    @if (null !== $section['heading'])
        @component('assessmentReports.sections.heading', ['section' => $section])
        @endcomponent
    @endif

    <ol class="numerical-list__list">
        @foreach($section['text_blocks'] as $item)
            <li>{{ $item['text'] }}</li>
        @endforeach
    </ol>

</section>
