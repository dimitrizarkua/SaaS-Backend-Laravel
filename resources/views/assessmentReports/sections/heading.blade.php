@php
    use App\Components\AssessmentReports\Enums\AssessmentReportHeadingStyles;

    if (AssessmentReportHeadingStyles::BOLD === $section['heading_style']) {
        $style = 'font-weight: bold';
    } elseif (AssessmentReportHeadingStyles::LIGHT === $section['heading_style']) {
        $style = 'font-weight: 300';
    } elseif (AssessmentReportHeadingStyles::ITALIC === $section['heading_style']) {
        $style = 'font-style: italic';
    } else {
        $style = 'font-style: normal; font-weight:500';
    }

    $color = null !== $section['heading_color'] ? '#' . hexdec($section['heading_color']) : '#000';

    $subheading = $subheading ?? true;
@endphp

@if (true === $subheading)
    <h4 class="title" style="{{ $style }}; color: {{ $color }}">
        {{ $section['heading'] }}
    </h4>
@else
    <h3 class="section-title" style="{{ $style }}; color: {{ $color }}">
        {{ $section['heading'] }}
    </h3>
@endif
