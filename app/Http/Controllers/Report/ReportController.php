<?php

namespace App\Http\Controllers\Report;

use App\Exam;
use Excel;
use App\Http\Controllers\ApiController;
use App\Report;
use App\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('adminOrManager');
    }

    /** Get all reports in company */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $reports = Exam::select('exam.*', DB::raw('(SELECT count(*) AS participants FROM report WHERE report.exam_id = exam.id)'))
            ->where('company_id', $companyId)
            ->get()
            ->makeHidden(['company_id', 'exam_question_id', 'is_shuffle_answer', 'is_allow_view_answer']);

        return $this->showAll($reports);
    }

    /** Get report detail in company by id */
    public function show($examId)
    {
        $exam = Exam::findOrFail($examId);

        if ($exam->company_id != Auth::user()->company_id) {
            return $this->errorResponse("You cannot access other company reports", 403);
        }

        $reports = Report::where('exam_id', $examId)
            ->get();

        foreach ($reports as $report) {
            $user = User::findOrFail($report->user_id);
            $report['total_score'] = $report->listening_score + $report->reading_score;
            $report['name'] = $user->name;
            $report['email'] = $user->email;
        }

        //respose data
        $data['exam_id'] = $examId;
        $data['exam'] = $exam->name;
        $data['exam_status'] = $exam->status;
        $data['details'] = $reports;

        return response()->json($data, 200);
    }

    public function exportDetailResult(Request $request)
    {
        $examId = $request->examId;
        try {
            $exam = Exam::findOrFail($examId);
            if ($exam->company_id != Auth::user()->company_id) {
                return $this->errorResponse("You cannot access other company reports", 403);
            }

            $reports = Report::where('exam_id', $examId)->with('user')->get()->toArray();
            $dataToExcel = [];
            foreach ($reports as $key => $value) {
                $dataToExcel[$key] = $this->formatRowItem($key, $value);
            }
            // ob_end_clean();
            // ob_start();  

            $exportExcelFileName = 'B??o c??o chi ti???t ??i???m - ' . $exam->name;

            return Excel::create($exportExcelFileName, function ($excel) use ($dataToExcel, $exam) {
                $excel->sheet('Chi ti???t ??i???m b??i thi', function ($sheet) use ($dataToExcel, $exam) {
                    /** Title for sheet */
                    $sheet->mergeCells('A2:K2');
                    $sheet->setCellValue('A2', $exam->name);
                    $sheet->cells('A2', function ($cells) {
                        $cells->setValignment('center');
                        $cells->setFontSize(16);
                        $cells->setFontWeight('bold');
                    });
                    $sheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => 'center')
                    );
                    $sheet->getStyle('A2')->getAlignment()->applyFromArray(
                        array('horizontal' => 'center')
                    );

                    /** Display time */
                    $dataTimeDisplay = 'T??? ng??y: ' . $this->formatDate($exam->from_date) . ' - ?????n ng??y: ' . $this->formatDate($exam->to_date);
                    $sheet->mergeCells('A3:K3');
                    $sheet->setCellValue('A3', $dataTimeDisplay);
                    $sheet->cells('A3', function ($cells) {
                        $cells->setValignment('center');
                        $cells->setFontSize(12);
                    });
                    $sheet->getStyle('A3')->getAlignment()->applyFromArray(
                        array('horizontal' => 'center')
                    );

                    /**main Content */
                    $sheet->fromArray($dataToExcel, null, 'A5', true);
                    $rangeHeader = "A5:K5";
                    $sheet->cells($rangeHeader, function ($cells) {
                        $cells->setFontSize(12);
                        $cells->setFontWeight(600);
                        $cells->setBackground('#86CEF7');
                    });
                });
            })->export('xlsx', ['Access-Control-Allow-Origin' => '*']);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function formatRowItem($stt, $item)
    {
        $itemUser = $item['user'];
        return [
            "STT" => $stt + 1,
            "H??? v?? t??n" => $itemUser['name'],
            "Email" => $itemUser['email'],
            "Ng??y sinh" => $itemUser['birthday'],
            "Gi???i t??nh" => $itemUser['gender'] === "male" ? "Nam" : ($itemUser['gender'] === "female" ? "N???" : ""),
            "S??? c??u nghe ????ng" => $item['num_listening'],
            "S??? c??u ?????c ????ng" =>  $item['num_reading'],
            "??i???m nghe" => $item['listening_score'],
            "??i???m ?????c" => $item['reading_score'],
            "T???ng ??i???m" => $item['listening_score'] + $item['reading_score'],
            "Th???i gian n???p b??i" => $item["created_at"]
        ];
    }

    public function formatDate($date)
    {
        $dateTime = new DateTime($date);
        return date_format($dateTime, "d/m/Y");
    }
}
