
     <!-- ชื่อไฟล์: ReplyController
     * คำอธิบาย: Controller สำหรับจัดการการตอบกลับของผู้ใช้
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 4 มีนาคม 2569 -->

<?php

namespace App\Http\Controllers;

use App\Services\ReplyService;
use Exception;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    protected ReplyService $replyService;

    public function __construct(ReplyService $replyService)
    {
        $this->replyService = $replyService;
    }


    public function openForm(string $encryptURL)
    {
        try {
            $this->replyService->decryptUrlData($encryptURL);
            $message = response()->json([
                'success' => true,
                'message' => 'Form can Open'
            ]);
        } catch (Exception $e) {
            $message = response()->json([
                'success' => false,
                'message' => 'Invalid URL'
            ]);
        }

        return view('reply', ['message' => $message]);
    }

    public function show(string $encryptURL)
    {
        try {
            $ids = $this->replyService->decryptUrlData($encryptURL);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid URL'
            ]);
        }

        $eveId = (int)$ids[0];
        $empId = (int)$ids[1];

        $details = $this->replyService->getReplyDetails($eveId, $empId);

        return response()->json([
            'success' => true,
            'employee' => $details['employee'],
            'event' => $details['event'],
            'connect' => $details['connect'],
        ]);
    }


    public function store(Request $req)
    {
        $data = $req->validate([
            'evnID' => 'required|integer',
            'empID' => 'required|integer',
            'attend' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $this->replyService->storeReplyData($data);

        return response()->json(['ok' => true], 201);
    }
}