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

    /**
     * ชื่อฟังก์ชัน: openForm
     * Http request: get
     * คำอธิบาย: ฟังก์ชันนี้ใช้สำหรับเปิดหน้าฟอร์มตอบกลับผ่านการตรวจสอบ URL ที่เข้ารหัส
     * Input: string $encryptURL
     * Output: View
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 4 มีนาคม 2569
     */
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

    /**
     * ชื่อฟังก์ชัน: show
     * Http request: get
     * คำอธิบาย: ฟังก์ชันนี้ใช้สำหรับตรวจสอบและดึงรายละเอียดข้อมูลพนักงานรวมถึงกิจกรรมเพื่อมาแสดงผล
     * Input: string $encryptURL
     * Output: JSON Response
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 4 มีนาคม 2569
     */
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

    /**
     * ชื่อฟังก์ชัน: store
     * Http request: post
     * คำอธิบาย: ฟังก์ชันนี้ใช้สำหรับบันทึกการตอบกลับเข้าร่วมกิจกรรมลงในฐานข้อมูล
     * Input: Request $req
     * Output: JSON Response
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 4 มีนาคม 2569
     */
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