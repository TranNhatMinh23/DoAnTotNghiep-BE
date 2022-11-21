<?php

namespace App\Http\Controllers\ExamQuestionScore;

use Excel;
use App\ExamQuestionScore;
use App\Http\Controllers\ApiController;
use App\ScoreMapping;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamQuestionScoreController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('adminOrManager');
    }

    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        // get company_id from Auth =>  get list exam questions has company_id
        $companyId = Auth::user()->company_id;
        $examQuestionScores = ExamQuestionScore::where('company_id', '=', $companyId)
            ->orderBy('id')
            ->get();

        return $this->showAll($examQuestionScores);
    }


    /**
     * Store a newly created resource in storage. 
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:exam_question_score'
        ];

        $this->validate($request, $rules);

        $data = $request->all();
        $data['company_id'] = Auth::user()->company_id;
        $examQuestionScore = ExamQuestionScore::create($data);

        // auto create score-mapping
        $this->createScoreMapping(100, $examQuestionScore);

        return $this->showOne($examQuestionScore, 201);
    }

    public function createScoreMapping(int $amount, ExamQuestionScore $examQuestionScore)
    {
        for ($i = 0; $i <= $amount; $i++) {
            $scoreMapping['num_of_question'] = $i;
            $scoreMapping['exam_question_score_id'] = $examQuestionScore->id;

            ScoreMapping::create($scoreMapping);
        }
    }

    /**
     * Display the specified resource. 
     */
    public function show($id)
    {
        $examQuestionScore = ExamQuestionScore::findOrFail($id);

        if ($examQuestionScore->company_id !== Auth::user()->company_id) {
            return $this->errorResponse("You can only get your company's exam question score", 403);
        }

        $detailScore = $examQuestionScore->score_mappings()->orderBy('num_of_question')->get();

        $examQuestionScore['detail'] = $detailScore;
        return $this->showOne($examQuestionScore);
    }


    /**
     * Update the specified resource in storage. 
     */
    public function update(Request $request, $id)
    {
        $examQuestionScore = ExamQuestionScore::findOrFail($id);

        if ($examQuestionScore->company_id !== Auth::user()->company_id) {
            return $this->errorResponse("You can only get your company's exam question score", 403);
        }

        $rules = [
            'name' => 'required'
        ];

        $this->validate($request, $rules);
        $examQuestionScore->name = $request->name;
        $examQuestionScore->description = $request->description;
        $examQuestionScore->save();

        return $this->showOne($examQuestionScore);
    }

    /**
     * Remove the specified resource from storage. 
     */
    public function destroy($id)
    {
        $examQuestionScore = ExamQuestionScore::findOrFail($id);

        if ($examQuestionScore->company_id !== Auth::user()->company_id) {
            return $this->errorResponse("You can only delete your company's exam question score", 403);
        }

        try {
            $examQuestionScore->score_mappings()->each(function ($score_mapping) {
                $score_mapping->delete();
            });
            $examQuestionScore->delete();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
        return $this->successResponse(["status" => "Delete successfully!"], 200);
    }

    public function updateDetailScoreMapping(Request $request, $id)
    {
        $examQuestionScore = ExamQuestionScore::findOrFail($id);

        if ($examQuestionScore->company_id !== Auth::user()->company_id) {
            return $this->errorResponse("You can only update your company's exam question score", 403);
        }

        $detailScore = $examQuestionScore->score_mappings()->orderBy('num_of_question')->get();
        $dataFromClient = $request->data;
        foreach ($detailScore as $item) {
            $scoreItem = ScoreMapping::findOrFail($item->id);
            $scoreItemClient = $this->getItemFromArray($dataFromClient, $scoreItem->num_of_question);
            $scoreItem->listening_score = $scoreItemClient['listening_score'];
            $scoreItem->reading_score = $scoreItemClient['reading_score'];

            $scoreItem->save();
        }
        $detailScoreAfterUpdated = $examQuestionScore->score_mappings()->orderBy('num_of_question')->get();
        return $this->showAll($detailScoreAfterUpdated);
    }

    public function getItemFromArray($arrInput = [], $num_of_question)
    {
        for ($i = 0; $i < count($arrInput); $i++) {
            $item = $arrInput[$i];
            if ($item['num_of_question'] === $num_of_question) return $item;
        }
    }

    public function exportDetailScoreMapping($idExamquestionScore)
    {
        $examQuestionScore = ExamQuestionScore::findOrFail($idExamquestionScore);

        if ($examQuestionScore->company_id !== Auth::user()->company_id) {
            return $this->errorResponse("You can only get your company's exam question score", 403);
        }

        $detailScore = $examQuestionScore->score_mappings()->orderBy('num_of_question')->get()->toArray();
        $dataToExcel = [];
        foreach ($detailScore as $key => $value) {
            $dataToExcel[$key] = ["number_of_correct_answer" => $value['num_of_question'], "listening_score" => $value['listening_score'], "reading_score" => $value['reading_score']];
        } 
        // ob_end_clean();
        // ob_start();

        return Excel::create('DETAIL_SCORE_MAPPING', function ($excel) use ($dataToExcel) {
            $excel->sheet('mySheet', function ($sheet) use ($dataToExcel) {
                $sheet->fromArray($dataToExcel, null, 'A1', true);
            });
        })->export('xlsx', ['Access-Control-Allow-Origin'=>'*'])->withHeadings('#', 'Name', 'E-mail');
    }

    public function importDetailScoreMapping($idExamquestionScore, Request $request)
    {
        if (!$request->file("files")) {
            return response()->json(["error" => "Error, No File!"], 400);
        }

        $examQuestionScore = ExamQuestionScore::findOrFail($idExamquestionScore);

        if ($examQuestionScore->company_id !== Auth::user()->company_id) {
            return $this->errorResponse("You can only update your company's exam question score", 403);
        }

        $extensions = array("xls", "xlsx");
        $resultCheckType = array($request->file('files')->getClientOriginalExtension());
        if (in_array($resultCheckType[0], $extensions)) {
            $path = $request->file('files')->getRealPath();
            $data = Excel::load($path)->get();

            $torarray = $data->toArray();
            $line0 = $torarray[0];
            $headers = array_keys($line0);
            $excel_header = $headers;

            if (!$this->checkHeaderExcelFile($excel_header)) {
                return response()->json(["error" => "Wrong score mapping file content format!"], 400);
            }

            if ($data->count()) {
                foreach ($data as $key => $value) {
                    $scoreItem = ScoreMapping::where('num_of_question', $value->number_of_correct_answer)
                        ->where('exam_question_score_id', $idExamquestionScore)
                        ->first();
                    $scoreItem->num_of_question = $value->number_of_correct_answer;
                    $scoreItem->listening_score = $value->listening_score;
                    $scoreItem->reading_score = $value->reading_score;
                    $scoreItem->save();
                }
                return $this->show($idExamquestionScore);
            } else {
                return response()->json(["error" => "File error!"], 400);
            }
        } else {
            return response()->json(["error" => "Must be excel file, it has .xls or .xlsx extensions!"], 400);
        }
    }

    public function checkHeaderExcelFile($listHeaderFromFile)
    {
        $checkHeaderCollumns = array("number_of_correct_answer", "listening_score", "reading_score");
        if (count($listHeaderFromFile) !== 3) { 
            return false;
        } else {
            for ($i = 0; $i < 3; $i++) {
                if (!in_array($listHeaderFromFile[$i], $checkHeaderCollumns)) return false;
            }
            return true;
        }
    }
}
