<?php

use App\Components\AssessmentReports\AssessmentReportPrintVersion;
use App\Components\AssessmentReports\Interfaces\AssessmentReportsServiceInterface;
use Illuminate\Http\Request;
use App\Components\Finance\ViewData\InvoicePrintVersion;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\ViewData\PurchaseOrderPrintVersion;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\ViewData\CreditNotesPrintVersion;

Route::get('/welcome/{user}', function (\App\Models\User $user) {
    return new \App\Mail\Welcome($user);
});

Route::get('/password/forgot/{user}', function (\App\Models\User $user) {
    $link = app(\App\Components\Auth\Services\ForgotPasswordService::class)
        ->generateResetPasswordLink($user);

    return new \App\Mail\ForgotPassword($user, $link);
});

Route::get('/office356/{user}', function (\App\Models\User $user) {
    return new \App\Mail\NewOffice365User($user);
});

Route::get('/password/changed/{user}', function (\App\Models\User $user) {
    return new \App\Mail\PasswordChanged($user);
});

//Test route that allows to view print version of an invoice
//URL: /emails/invoice/pdf/{invoice_id}?pdf=true
Route::get('/invoices/pdf/{invoice}', function (Invoice $invoice, Request $request) {
    $classname = InvoicePrintVersion::class;

    return printVersion(
        $invoice,
        $classname,
        'finance.invoices.print',
        $request->has('pdf')
    );
});

Route::get('/purchase-orders/pdf/{purchase_order}', function (PurchaseOrder $purchaseOrder, Request $request) {
    $classname = PurchaseOrderPrintVersion::class;

    return printVersion(
        $purchaseOrder,
        $classname,
        'finance.purchaseOrders.print',
        $request->has('pdf')
    );
});

Route::get('/credit-notes/pdf/{credit_note}', function (CreditNote $creditNote, Request $request) {
    $classname = CreditNotesPrintVersion::class;

    return printVersion(
        $creditNote,
        $classname,
        'finance.creditNotes.print',
        $request->has('pdf')
    );
});

Route::get(
    '/assessment-reports/pdf/{assessment_report}',
    function (int $assessmentReportId, Request $request) {
        $assessmentReport = app()->make(AssessmentReportsServiceInterface::class)
            ->getFullAssessmentReport($assessmentReportId);
        $classname        = AssessmentReportPrintVersion::class;

        return printVersion(
            $assessmentReport,
            $classname,
            'assessmentReports.print',
            $request->has('pdf')
        );
    }
);

//Test routes that allow to view print version (PDF) of uploaded html file.
//URL: /emails/generate-pdf
Route::get('/generate-pdf', function () {
    return view('upload-html-file');
});
Route::post('/generate-pdf', function (Request $request) {
    $file = $request->file('file');
    if (!$file) {
        throw new \App\Exceptions\Api\NotFoundException('File not uploaded.');
    }
    if ('text/html' !== $file->getMimeType()) {
        throw new \App\Exceptions\Api\NotAllowedException('Uploaded file must be a html file.');
    }

    $html = file_get_contents($file->getRealPath());
    /** @var \Barryvdh\DomPDF\PDF $pdf */
    $pdf = Barryvdh\DomPDF\Facade::loadHTML($html);
    $pdf->setPaper('a4', 'portrait');
    $pdf->setOptions(['dpi' => 150]);

    return $pdf->stream();
})->name('generate-pdf');

function printVersion($entity, $viewDataClass, $template, bool $asPdf)
{
    $viewData = new $viewDataClass($entity);
    if ($asPdf) {
        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = Barryvdh\DomPDF\Facade::loadView($template, $viewData->toArray());
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions(['dpi' => 150]);

        return $pdf->stream();
    }

    return view($template, $viewData->toArray())->render();
}
