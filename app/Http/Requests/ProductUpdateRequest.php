<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
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
            'image' => 'required_without_all:name,desc,value',
            'name' => 'required_without_all:image,desc,value|unique:products|max:100',
            'desc' => 'required_without_all:name,image,value|max:500',
            'value' => 'required_without_all:name,desc,image|integer'
        ];
    }
    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if($this->filled('image')) {
                if (base64_encode(base64_decode($this->input('image'), true)) !== $this->input('image')) {
                    $validator->errors()->add('image', 'Image must be encoded with BASE64');
                }
                $img = base64_decode($this->input('image'));
                $type = finfo_buffer(finfo_open(), $img, FILEINFO_MIME_TYPE);
                switch ($type) {
                    case 'image/jpeg':
                        $ext = 'jpg';
                        break;
                    case 'image/png':
                        $ext = 'png';
                        break;
                    case 'image/gif':
                        $ext = 'gif';
                        break;
                    default:
                        $validator->errors()->add('image', 'extension must be jpg/png/gif');
                }
            }
        });
    }
}
