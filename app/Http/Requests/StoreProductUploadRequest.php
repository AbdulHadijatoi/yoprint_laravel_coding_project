<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => [
                'required',
                'file',
                'max:51200',
                'mimetypes:text/plain,text/csv,text/tab-separated-values,application/vnd.ms-excel',
                'mimes:csv,txt,tsv',
            ],
        ];
    }

    public function messages()
    {
        return [
            'file.mimes' => 'The uploaded file must be a CSV or TSV file.',
            'file.mimetypes' => 'The uploaded file must be a valid CSV or TSV file.',
        ];
    }
}
