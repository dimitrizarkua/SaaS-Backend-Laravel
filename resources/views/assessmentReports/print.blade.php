<!DOCTYPE html>
<html lang="en">
<head>
    <title>Assessment report {{ $assessmentReport['id'] }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body style="min-height: 100vh; margin: 0; padding: 0;">

@component('assessmentReports.styles')
@endcomponent

<div class="document">

    @component('assessmentReports.title-page', [
        'assessmentReport' => $assessmentReport,
        'date'             => $date,
        'customer'         => $customer,
        'job'              => $job,
    ])
    @endcomponent

    @component('assessmentReports.info', [
        'customer'         => $customer,
        'job'              => $job,
    ])
    @endcomponent

    @foreach($assessmentReport['sections'] as $section)
        @if (\App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes::PAGE_BREAK === $section['type'])
            @component('assessmentReports.sections.page-break')
            @endcomponent
        @elseif (\App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes::SPACER === $section['type'])
            @component('assessmentReports.sections.spacer')
            @endcomponent
        @elseif (\App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes::HEADING === $section['type'])
            @component('assessmentReports.sections.heading', ['section' => $section, 'subheading' => false])
            @endcomponent
        @elseif (\App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes::SUBHEADING === $section['type'])
            @component('assessmentReports.sections.heading', ['section' => $section])
            @endcomponent
        @elseif (\App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes::TEXT === $section['type'])
            @component('assessmentReports.sections.text', ['section' => $section])
            @endcomponent
        @elseif (\App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes::BULLET_LIST === $section['type'])
            @component('assessmentReports.sections.bullet-list', ['section' => $section])
            @endcomponent
        @elseif (\App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes::NUMBER_LIST === $section['type'])
            @component('assessmentReports.sections.number-list', ['section' => $section])
            @endcomponent
        @elseif (\App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes::IMAGE === $section['type'])
            @component('assessmentReports.sections.image', ['section' => $section])
            @endcomponent
        @elseif (\App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes::PHOTOS === $section['type'])
            @component('assessmentReports.sections.photos', ['section' => $section])
            @endcomponent
        @elseif (\App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes::ROOM === $section['type'])
            @component('assessmentReports.sections.room', ['section' => $section])
            @endcomponent
        @elseif (\App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes::COSTS === $section['type'])
            @component('assessmentReports.sections.costs', ['section' => $section])
            @endcomponent
        @endif
    @endforeach

    @component('assessmentReports.disclaimer')
    @endcomponent

</div>

</body>
</html>
