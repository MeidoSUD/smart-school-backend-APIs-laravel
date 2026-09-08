<?php

namespace Modules\Academic\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApplyLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $applyDate = $this->input('apply_date')
            ?? $this->input('applyDate')
            ?? $this->get('apply_date')
            ?? date('Y-m-d');

        $fromDate = $this->input('from_date')
            ?? $this->input('fromDate')
            ?? $this->get('from_date');

        $toDate = $this->input('to_date')
            ?? $this->input('toDate')
            ?? $this->get('to_date');

        $reason = $this->input('reason')
            ?? $this->input('message')
            ?? $this->get('message');

        $leaveId = $this->input('leave_id')
            ?? $this->input('leaveId')
            ?? $this->get('leave_id');

        if ((empty($fromDate) || empty($toDate)) && !empty($this->getContent())) {
            $rawContent = $this->getContent();
            $json = json_decode($rawContent, true);
            if (is_array($json)) {
                $applyDate = $json['apply_date'] ?? $json['applyDate'] ?? $applyDate;
                $fromDate = $json['from_date'] ?? $json['fromDate'] ?? $fromDate;
                $toDate = $json['to_date'] ?? $json['toDate'] ?? $toDate;
                $reason = $json['message'] ?? $json['reason'] ?? $reason;
                $leaveId = $json['leave_id'] ?? $json['leaveId'] ?? $leaveId;
            }

            if ((empty($fromDate) || empty($toDate)) && str_contains($rawContent, 'Content-Disposition')) {
                preg_match_all('/name="([^"]+)"[\r\n]+[\r\n]+([^\r\n\-]+)/', $rawContent, $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $key = trim($match[1]);
                    $val = trim($match[2]);
                    if ($key === 'apply_date' || $key === 'applyDate') {
                        $applyDate = $val;
                    } elseif ($key === 'from_date' || $key === 'fromDate') {
                        $fromDate = $val;
                    } elseif ($key === 'to_date' || $key === 'toDate') {
                        $toDate = $val;
                    } elseif ($key === 'message' || $key === 'reason') {
                        $reason = $val;
                    } elseif ($key === 'leave_id' || $key === 'leaveId') {
                        $leaveId = $val;
                    }
                }
            }
        }

        if (empty($fromDate) && isset($_POST['from_date'])) {
            $fromDate = $_POST['from_date'];
        }
        if (empty($toDate) && isset($_POST['to_date'])) {
            $toDate = $_POST['to_date'];
        }
        if (empty($reason) && isset($_POST['message'])) {
            $reason = $_POST['message'];
        }
        if (empty($leaveId) && isset($_POST['leave_id'])) {
            $leaveId = $_POST['leave_id'];
        }

        $this->merge([
            'apply_date' => $applyDate,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'reason' => $reason,
            'leave_id' => $leaveId,
        ]);
    }

    public function rules(): array
    {
        return [
            'apply_date' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'reason' => 'nullable|string|max:1000',
            'leave_id' => 'nullable',
            'docs' => 'nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'fail',
            'error' => $validator->errors(),
            'message' => '',
        ], 200));
    }
}
