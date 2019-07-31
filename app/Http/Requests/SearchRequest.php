<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;


class SearchRequest extends FormRequest
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
            'name' => 'required_without_all:min_value,max_value',
            'min_value' => 'required_without:keyword|required_with:max_value|integer',
            'max_value' => 'required_without:keyword|required_with:min_value|integer'
        ];
    }

    public function withValidation($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled(['min_value', 'max_value'])) {
                if ($this->input('min_value') >= $this->input('max_value')) {
                    $validator->errors()->add('value', 'max_value must be larger than min_value.');
                }
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

