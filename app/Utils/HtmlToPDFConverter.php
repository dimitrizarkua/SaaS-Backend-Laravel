<?php

namespace App\Utils;

use Barryvdh\DomPDF\Facade as PDF;
use App\Contracts\ViewDataInterface;

/**
 * Class HtmlToPDFConverter
 *
 * @package App
 */
class HtmlToPDFConverter
{
    /**
     * @var \App\Contracts\ViewDataInterface
     */
    private $viewData;

    /**
     * @var string
     */
    private $templateName;

    /**
     * HtmlToPDFConverter constructor.
     *
     * @param \App\Contracts\ViewDataInterface $viewData
     * @param string                           $templateName
     */
    public function __construct(ViewDataInterface $viewData, string $templateName)
    {
        $this->viewData     = $viewData;
        $this->templateName = $templateName;
    }

    /**
     * Converts html to pdf and saves content to file.
     *
     * @param string $filename File name where pdf will be saved.
     *
     * @throws \Throwable
     */
    public function convert(string $filename): void
    {
        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = PDF::loadView($this->templateName, $this->viewData->toArray());
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions(['dpi' => 150]);
        $pdf->save($filename);
    }
}
