<?php

namespace App\Components\AssessmentReports\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class AssessmentReportSectionTypes
 *
 * @package App\Components\AssessmentReports\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Assessment report section type",
 *     enum={
 *         "heading",
 *         "subheading",
 *         "text",
 *         "bullet_list",
 *         "number_list",
 *         "spacer",
 *         "page_break",
 *         "image",
 *         "photos",
 *         "costs",
 *         "room",
 *     },
 * )
 */
class AssessmentReportSectionTypes extends Enum
{
    public const HEADING     = 'heading';
    public const SUBHEADING  = 'subheading';
    public const TEXT        = 'text';
    public const BULLET_LIST = 'bullet_list';
    public const NUMBER_LIST = 'number_list';
    public const SPACER      = 'spacer';
    public const PAGE_BREAK  = 'page_break';
    public const IMAGE       = 'image';
    public const PHOTOS      = 'photos';
    public const COSTS       = 'costs';
    public const ROOM        = 'room';

    protected static $values = [
        'HEADING'     => self::HEADING,
        'SUBHEADING'  => self::SUBHEADING,
        'TEXT'        => self::TEXT,
        'BULLET_LIST' => self::BULLET_LIST,
        'NUMBER_LIST' => self::NUMBER_LIST,
        'SPACER'      => self::SPACER,
        'PAGE_BREAK'  => self::PAGE_BREAK,
        'IMAGE'       => self::IMAGE,
        'PHOTOS'      => self::PHOTOS,
        'COSTS'       => self::COSTS,
        'ROOM'        => self::ROOM,
    ];

    /**
     * List of types for text section.
     *
     * @var array
     */
    public static $textSectionTypes = [
        self::HEADING,
        self::SUBHEADING,
        self::TEXT,
        self::BULLET_LIST,
        self::NUMBER_LIST,
        self::SPACER,
        self::PAGE_BREAK,
    ];
}
