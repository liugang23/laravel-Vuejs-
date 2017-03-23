<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // true 允许每个人都可以发布问题
        return true;
    }

    /**
     * 自定义消息提示
     */
    public function messages()
    {
        return [
            'title.required' => '标题不能为空',
            'title.min' => '标题不能少于6个字',
            'body.required' => '内容不能为空',
            'body.min' => '内容不能少于26个字',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     * 对请求进行验证
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|min:6|max:196',
            'body' => 'required|min:26',
        ];
    }
}
