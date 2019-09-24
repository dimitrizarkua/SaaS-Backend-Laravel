<section>

    @if (null !== $section['heading'])
        @component('assessmentReports.sections.heading', ['section' => $section])
        @endcomponent
    @endif

    <div style="white-space: pre-wrap">{!! $section['text'] !!}</div>

</section>
