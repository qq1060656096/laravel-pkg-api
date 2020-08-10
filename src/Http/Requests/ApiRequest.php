<?php
namespace Zwei\LaravelPkgApi\Http\Requests;

use App\Exceptions\AppErrorCodeException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = array();
        return $rules;
    }
    
    /**
     * @inheritdoc
     */
    protected function failedValidation(Validator $validator) {
        if (!$this->expectsJson()) {
            return parent::failedValidation($validator);
        }
        $errors = $validator->errors();
        $errorsNew = [];
        foreach ($errors->getMessages() as $key => $error) {
            $errorsNew[$key] = $error[0];
        }
        $message = $errors->first();
        $jsonData = [
            'code'      => 422,
            'message'   => $message ? $message : trans("validate.unprocessableEntity"),
            'data' => [
                'errorsRaw' => $errors,
                'errors' => $errorsNew,
            ],
        ];
        throw new HttpResponseException(response()->json($jsonData, 422));
    }
}
