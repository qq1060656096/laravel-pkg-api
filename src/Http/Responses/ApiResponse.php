<?php
namespace Zwei\LaravelPkgApi\Http\Responses;

use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use EllipseSynergie\ApiResponse\Laravel\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Item;

class ApiResponse extends Response
{
    /**
     * 转换array
     * @param $array
     * @return mixed
     */
    public static function convertArray($array)
    {
        if (key_exists('data', $array) && ($array['data'] === [] || $array['data'] === null)) {
            $array['data'] = new \stdClass();
        }

        if (key_exists('errors', $array)  && ($array['errors'] === [] || $array['errors'] === null) ) {
            $array['errors'] = new \stdClass();
        }
        if (key_exists('code', $array)) {
            $array['code'] = intval($array['code']);
        }

        return $array;
    }

    /**
     * @param array $array
     * @param array $headers
     * @param int $json_options @link http://php.net/manual/en/function.json-encode.php
     * @return ResponseFactory
     */
    public function withArray(array $array, array $headers = [], $json_options = 0)
    {
        $array = self::convertArray($array);
        return response()->json($array, $this->statusCode, $headers, $json_options);
    }

    /**
     * 响应数据
     *
     * @param array $data
     * @param array $headers
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function withData($data , array $headers = [])
    {

        if($this->statusCode == '200'){
            $jsonData = [
                'status'    => 'T',
                'code'      => $this->getStatusCode(),
                'message'   => '成功',
                'data'      => $data,
            ];
        }else{
            $jsonData = [
                'status'    => 'F',
                'code'      => $this->getStatusCode(),
                'message'   => '',
                'data'      => $data,
            ];
        }
        return $this->withArray($jsonData, $headers);
    }

    public function withItem($data, $transformer, $resourceKey = null, $meta = [], array $headers = [])
    {
        $resource = new Item($data, $transformer, $resourceKey);

        foreach ($meta as $metaKey => $metaValue) {
            $resource->setMetaValue($metaKey, $metaValue);
        }

        $rootScope = $this->manager->createData($resource);
        $response = $rootScope->toArray();
        if($this->statusCode == '200'){
            $jsonData = [
                'status'    => 'T',
                'code'      => $this->getStatusCode(),
                'message'   => '',
                'data'      => $response['data'],
            ];
        }else{
            $jsonData = [
                'status'    => 'F',
                'code'      => $this->getStatusCode(),
                'message'   => '',
                'data'      => $response['data'],
            ];
        }
        return $this->withArray($jsonData, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function withCollection($data, $transformer, $resourceKey = null, Cursor $cursor = null, $meta = [], array $headers = []){
//         var_dump( get_class($data), $data->toArray());exit;
        $resource = new Collection($data, $transformer, $resourceKey);

        foreach ($meta as $metaKey => $metaValue) {
            $resource->setMetaValue($metaKey, $metaValue);
        }

        if (!is_null($cursor)) {
            $resource->setCursor($cursor);
        }

        $rootScope = $this->manager->createData($resource);

        $response = $rootScope->toArray();
        if($data instanceof LengthAwarePaginator){
            $response['pagination'] = [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ];
        }
        if($this->statusCode == '200'){
            $jsonData = [
                'status'    => 'T',
                'code'      => $this->getStatusCode(),
                'message'   => '',
                'data'      => $response['data'],
            ];
        }else{
            $jsonData = [
                'status'    => 'F',
                'code'      => $this->getStatusCode(),
                'message'   => '',
                'data'      => $response['data'],
            ];
        }
        return $this->withArray($jsonData, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function withCollectionDataLists($data, $transformer, $resourceKey = null, Cursor $cursor = null, $meta = [], array $headers = []){
        $resource = new Collection($data, $transformer, $resourceKey);
        foreach ($meta as $metaKey => $metaValue) {
            $resource->setMetaValue($metaKey, $metaValue);
        }
        if (!is_null($cursor)) {
            $resource->setCursor($cursor);
        }
        $rootScope = $this->manager->createData($resource);
        $responseData = $rootScope->toArray();
        $response = [];

        if($this->statusCode == '200'){
            $jsonData = [
                'status'    => 'T',
                'code'      => $this->getStatusCode(),
                'message'   => '',
                'data'      => [],
            ];
        }else{
            $jsonData = [
                'status'    => 'F',
                'code'      => $this->getStatusCode(),
                'message'   => '',
                'data'      => [],
            ];
        }

        if($data instanceof LengthAwarePaginator){
            $dataLists['data'] = [
                "pageCount" => $data->lastPage(),
                "count" => $data->total(),
                "page" => $data->currentPage(),
                "pageSize" => $data->perPage(),
                'lists' => $responseData['data'],
            ];
            $jsonData = array_merge($jsonData, $dataLists);
        } else {
            $data = [];
            $jsonData['data']['list'] =  $responseData['data'];
        }
        return $this->withArray($jsonData, $headers);
    }
}
