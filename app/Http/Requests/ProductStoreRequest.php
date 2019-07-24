<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductStoreRequest extends FormRequest
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
            'image' => 'required',
            'name' => 'bail|required|unique:products|max:100',
            'desc' => 'required|max:500',
            'value' => 'required|integer'
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if ( base64_encode(base64_decode($this->input('image'), true)) !== $this->input('image')) {
                $validator->errors()->add('image', 'Image must be encoded with BASE64');
            }
            $img = base64_decode($this->input('image'));
            $type = finfo_buffer(finfo_open(), $img,FILEINFO_MIME_TYPE);
            switch ($type) {
                case 'image/jpeg':
                    $ext='jpg';
                    break;
                case 'image/png':
                    $ext='png';
                    break;
                case 'image/gif':
                    $ext='gif';
                    break;
                default:
                    $validator->errors()->add('image', 'extension must be jpg/png/gif');
            }
        });
    }

    protected function failedValidation( Validator $validator )
    {
        $response['data']    = [];
        $response['status']  = 'NG';
        $response['summary'] = 'Failed validation.';
        $response['errors']  = $validator->errors()->toArray();

        throw new HttpResponseException(
            response()->json( $response, 422 )
        );
    }
}
