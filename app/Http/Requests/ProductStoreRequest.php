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
