<section class="image">

    @if (null !== $section['heading'])
        @component('assessmentReports.sections.heading', ['section' => $section])
        @endcomponent
    @endif

    <img alt="{{ $section['heading'] }}"
         src="{{ $section['image']['photo']['url']  }}"
         style="width: {{ $section['image']['desired_width'] }}px;"/>
    <div class="image__description">{{ $section['image']['caption'] }}</div>

</section>
