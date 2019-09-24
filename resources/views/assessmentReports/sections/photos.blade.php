<div class="photos">

    @if (null !== $section['heading'])
        @component('assessmentReports.sections.heading', ['section' => $section])
        @endcomponent
    @endif

    <div class="photos__content clearfix">

        @foreach ($section['photos'] as $photo)
            <div class="photos__item">

                <img alt="{{ $photo['caption'] }}" src="{{ $photo['photo']['url'] }}" class="photos__img" />
                <div class="photos_description">{{ $photo['caption'] }}</div>

            </div>
        @endforeach

    </div>

</div>
