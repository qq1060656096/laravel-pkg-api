<?php

namespace Zwei\LaravelPkgApi\Exception;

use App\Http\ApiResponse;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\Exception\OAuthServerException;
use PhpParser\Error;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class ExceptionHandler extends Handler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }
        parent::report($exception);
        switch (true) {
            case $exception instanceof AuthenticationException:
            case $exception instanceof OAuthServerException:
            case $exception instanceof OAuth2Exception:
                $response = $this->unauthenticatedNew(request(), $exception);
                $response->send();
                break;
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        switch (true) {
            case $exception instanceof MicroServiceException:
                return $this->microServiceException($request, $exception);
                break;
            case $exception instanceof AuthenticationException:
            // 401
            case $exception instanceof OAuthServerException:
            case $exception instanceof OAuth2Exception:
                return $this->unauthenticatedNew($request, $exception);
                break;
            // 404
            case $exception instanceof NotFoundException:
                return $this->notFound($request, $exception);
                break;
            // 422
            case $exception instanceof UnprocessableEntityHttp:
                return $this->unprocessableEntity($request, $exception);
                break;
            // 429
            case $exception instanceof TooManyRequestsHttpException:
                return $this->tooManyRequests($request, $exception);
                break;
            case $exception instanceof HttpException:
                return $this->httpException($request, $exception);
                break;

        }
        return parent::render($request, $exception);
    }

    /**
     * 微服务异常
     * @param $request
     * @param  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function microServiceException($request, MicroServiceException $exception)
    {
        if (!$request->expectsJson()) {
            return parent::render($request, $exception);
        }
        $code = $exception->getServiceApiResult()->getJsonContentCode();
        $message = $exception->getServiceApiResult()->getJsonContentErrorsFirstMessage();
        $message = $message ? $message : $exception->getServiceApiResult()->getJsonContentMessage();
        $parentException = $exception->getServiceApiResult()->getException();

        if ($code === null && $message === null && $parentException) {
            $code = $parentException->getCode();
            $message = $parentException->getMessage();
        }

        $jsonData = [
            'status'    => 'F',
            'code'      => $code ? $code : AppErrorCodeException::PARAM_INVALID,
            'message'   => $message ? $message : trans("validation_user.param_invalid"),
            'data'      => [],
            'errors'    => $exception->getServiceApiResult()->getJsonContentErrors(),
        ];
        $jsonData['exception'] = $this->getExceptionData($exception);
        $jsonData = ApiResponse::convertArray($jsonData);
        return response()->json($jsonData, 422);
    }

    /**
     * 401 Unauthorized 未授权
     * @param \Illuminate\Http\Request $request
     * @param  $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function unauthenticatedNew($request, $exception)
    {
        if (!$request->expectsJson()) {
            return parent::render($request, $exception);
        }
        $message = $exception->getMessage() ? $exception->getMessage() : 'Unauthorized';
        return $this->getExceptionResponseJson(401, 'F', $exception->getCode(), $message, $exception);
    }

    /**
     * 404 not found 异常
     * @param $request
     * @param NotFoundException $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function notFound($request, NotFoundException $exception)
    {
        if (!$request->expectsJson()) {
            return parent::render($request, $exception);
        }
        $message = $exception->getMessage() ? $exception->getMessage() : 'not found';
        return $this->getExceptionResponseJson(404, 'F', $exception->getCode(), $message, $exception);
    }


    /**
     * 404 not found 异常
     * @param $request
     * @param UnprocessableEntityHttp $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function unprocessableEntity($request, UnprocessableEntityHttp $exception)
    {
        if (!$request->expectsJson()) {
            return parent::render($request, $exception);
        }
        $data['errors'] = $exception->getErrors();
        $message = $exception->getMessage() ? $exception->getMessage() : 'Unprocessable Entity';
        return $this->getExceptionResponseJson(422, 'F', AppErrorCodeException::PARAM_INVALID, $message, $exception, $data);
    }

    /**
     * 429 太多的请求次数
     * @param $request
     * @param TooManyRequestsHttpException $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function tooManyRequests($request, TooManyRequestsHttpException $exception)
    {
        if (!$request->expectsJson()) {
            return parent::render($request, $exception);
        }
        $message = $exception->getMessage() ? $exception->getMessage() : 'Too Many Requests';
        return $this->getExceptionResponseJson(429, 'F', 429, $message, $exception);
    }

    /**
     * http异常
     * @param $request
     * @param HttpException $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function httpException($request, HttpException $exception)
    {
        if (!$request->expectsJson()) {
            return parent::render($request, $exception);
        }
        $message = $exception->getMessage() ? $exception->getMessage() : 'httpException';
        return $this->getExceptionResponseJson($exception->getStatusCode(), 'F', $exception->getCode(), $message, $exception);
    }

    /**
     * mo
     * @param $request
     * @param Error|Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function defaultException($request, $exception)
    {
        if (!$request->expectsJson()) {
            return parent::render($request, $exception);
        }
        $message = $exception->getMessage() ? $exception->getMessage() : 'exception';
        $code = $exception->getCode() == 0 ? -1 : $exception->getCode();
        $httpStatusCode = 500;
        if (method_exists($exception,'getStatusCode')) {
            $httpStatusCode = $exception->getStatusCode();
        }
        return $this->getExceptionResponseJson($httpStatusCode, 'F', $code, $message, $exception);
    }
    /**
     * 获取异常响应Json
     * @param integer $httpStatus
     * @param string $status
     * @param string $code
     * @param string $message
     * @param \Exception $exception
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExceptionResponseJson($httpStatus, $status, $code, $message, $exception, $data = [])
    {
        $jsonData = [
            'status'    => $status,
            'code'      => $code,
            'message'   => $message,
            'data'      => $data,
        ];
        $jsonData['exception'] = $this->getExceptionData($exception);

        if (env('APP_DEBUG') != true) {
            unset($jsonData['exception']['trace']);
        }
        $jsonData = ApiResponse::convertArray($jsonData);
        return response()->json($jsonData, $httpStatus);
    }

    /**
     * 获取异常data
     *
     * @param Exception $exception
     * @return array
     */
    public function getExceptionData(\Exception $exception)
    {
        return [
            'class'     => get_class($exception),
            'code'      => $exception->getCode(),
            'message'   => $exception->getMessage(),
            'file'      => $exception->getFile(),
            'line'      => $exception->getLine(),
            'trace'     => $exception->getTraceAsString(),
        ];
    }
}
