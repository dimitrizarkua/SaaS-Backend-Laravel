@php
    $isCarpet = null !== $section['room']['flooring_type'] && 'Carpet' === $section['room']['flooring_type']['name']
@endphp

<section>

    <h4 style="box-sizing: inherit; margin: 10px 0; font-size: 12pt; font-weight: 300;">
        <strong>Room: </strong><span>{{ $section['room']['name'] }}</span>
    </h4>

    <div class="divTable" style="width: 100%;">
        <div class="divTableBody">
            <div class="divTableRow">
                @if ($section['room']['dimensions_length'] || $section['room']['dimensions_width'] || $section['room']['dimensions_height'])
                    <div class="divTableCell">
                        <table>
                            <tr>
                                <td>Total Area (LxWxH):</td>
                                <td>{{ $section['room']['dimensions_length'] }}m x {{ $section['room']['dimensions_width'] }}m x {{ $section['room']['dimensions_height'] }}m</td>
                            </tr>
                        </table>
                    </div>
                @endif
                @if ($section['room']['dimensions_affected_length'] || $section['room']['dimensions_affected_width'])
                    <div class="divTableCell">
                        <table>
                            <tr>
                                <td>Affected Area (LxW):</td>
                                <td>{{ $section['room']['dimensions_affected_length'] }}m x {{ $section['room']['dimensions_affected_width'] }}m</td>
                            </tr>
                        </table>
                    </div>
                @endif
            </div>
            @if (null !== $section['room']['flooring_type'])
                <div class="divTableRow">
                    <div class="divTableCell">
                        <table>
                            <tr>
                                <td>Floor Type:</td>
                                <td>
                                    {{ $section['room']['flooring_type']['name'] }}
                                    @if ($isCarpet)
                                        @if (null !== $section['room']['carpet_type'])
                                            : {{ $section['room']['carpet_type']['name'] }}
                                        @endif
                                        @if (null !== $section['room']['carpet_age'])
                                            ({{ $section['room']['carpet_age']['name'] }})
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    @if (null !== $section['room']['flooring_subtype'])
                        <div class="divTableCell">
                            <table>
                                <tr>
                                    <td>Floor Subtype:</td>
                                    <td>{{ $section['room']['flooring_subtype']['name'] }}</td>
                                </tr>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
            @if ($isCarpet)
                <div class="divTableRow">
                    @if (null !== $section['room']['carpet_construction_type'])
                        <div class="divTableCell">
                            <table>
                                <tr>
                                    <td>Carpet Construction:</td>
                                    <td>{{ $section['room']['carpet_construction_type']['name'] }}</td>
                                </tr>
                            </table>
                        </div>
                    @endif
                    @if (null !== $section['room']['carpet_face_fibre'])
                        <div class="divTableCell">
                            <table>
                                <tr>
                                    <td>Carpet Face-Type:</td>
                                    <td>{{ $section['room']['carpet_face_fibre']['name'] }}</td>
                                </tr>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
            @if (true === $section['room']['underlay_required'])
                <div class="divTableRow">
                    @if (null !== $section['room']['underlay_type'])
                        <div class="divTableCell">
                            <table>
                                <tr>
                                    <td>Underlay:</td>
                                    <td>{{ $section['room']['underlay_type']['name'] }}</td>
                                </tr>
                            </table>
                        </div>
                    @endif
                    @if ($section['room']['dimensions_underlay_length'] || $section['room']['dimensions_underlay_width'])
                        <div class="divTableCell">
                            <table>
                                <tr>
                                    <td>Underlay Area (LxW):</td>
                                    <td>{{ $section['room']['dimensions_underlay_length'] }}m x {{ $section['room']['dimensions_underlay_width'] }}m</td>
                                </tr>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="divTableRow">
                    <div class="divTableCell">
                        <table>
                            <tr>
                                <td>Underlay note:</td>
                                <td>{{ $section['room']['underlay_type_note'] }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endif
            @if (true === $section['room']['trims_required'] && null !== $section['room']['trim_type'])
                <div class="divTableRow">
                    <div class="divTableCell">
                        <table>
                            <tr>
                                <td>Trim Type:</td>
                                <td>{{ $section['room']['trim_type'] }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endif
            <div class="divTableRow">
                <div class="divTableCell">
                    <table>
                        <tr>
                            <td>Restorable:</td>
                            <td>{{ false === $section['room']['restorable'] ? 'No' : 'Yes' }}</td>
                        </tr>
                    </table>
                </div>
                @if (false === $section['room']['restorable'] && null !== $section['room']['non_restorable_reason']['name'])
                    <div class="divTableCell">
                        <table>
                            <tr>
                                <td class="bold">Non-restorable reason:</td>
                                <td>{{ $section['room']['non_restorable_reason']['name'] }}</td>
                            </tr>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

</section>
