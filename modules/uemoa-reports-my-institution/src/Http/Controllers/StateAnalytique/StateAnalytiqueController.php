<?php

namespace Satis2020\UemoaReportsMyInstitution\Http\Controllers\StateAnalytique;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Exports\UemoaReports\StateAnalytiqueReportExcel;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Traits\Metadata;
use Satis2020\ServicePackage\Traits\UemoaReports;

/**
 * Class StateAnalytiqueController
 * @package Satis2020\UemoaReportsMyInstitution\Http\Controllers\StateAnalytique
 */
class StateAnalytiqueController extends ApiController
{
    use UemoaReports,Metadata;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-reporting-claim-my-institution')->only(['index', 'excelExport']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {

        $this->validate($request, $this->rulePeriode());

        $claims = $this->resultatsStateAnalytique($request, true);

        return response()->json($claims, 200);

    }


    /**
     * @param Request $request
     * @return
     * @throws \Illuminate\Validation\ValidationException
     */
    public function excelExport(Request $request)
    {

        $this->validate($request, $this->rulePeriode());

        $claims = $this->resultatsStateAnalytique($request, true);

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        $titleDescription = $this->getMetadataByName(Constants::ANALYTICS_STATE_REPORTING)->title.' : '.$this->getMetadataByName(Constants::ANALYTICS_STATE_REPORTING)->description;

        Excel::store(new StateAnalytiqueReportExcel($claims, true, $libellePeriode,$titleDescription), 'rapport-uemoa-etat-analytique-my-institution.xlsx');

        return response()->json(['file' => 'rapport-uemoa-etat-analytique-my-institution.xlsx'], 200);
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function pdfExport(Request $request)
    {

        $this->validate($request, $this->rulePeriode());

        $claims = $this->resultatsStateAnalytique($request);

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        $data = view('ServicePackage::uemoa.report-analytique', [
            'claims' => $claims,
            'myInstitution' => true,
            'libellePeriode' => $libellePeriode,
            'title' => $this->getMetadataByName(Constants::ANALYTICS_STATE_REPORTING)->title,
            'description' => $this->getMetadataByName(Constants::ANALYTICS_STATE_REPORTING)->description,
            'logo' => $this->logo($this->institution()),
            'colorTableHeader' => $this->colorTableHeader(),
            'logoSatis' => asset('assets/reporting/images/satisLogo.png'),
        ])->render();

        $file = 'rapport-uemoa-etat-analytique-my-institution.pdf';

        $pdf = App::make('dompdf.wrapper');

        $pdf->loadHTML($data);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($file);
    }

}
