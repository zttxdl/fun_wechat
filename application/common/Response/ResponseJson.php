<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/5/22
 * Time: 4:35 PM
 */

namespace app\common\Response;


trait ResponseJson
{
    /**
     * 当App接口出现业务异常时的返回
     * @param $code
     * @param $msg
     * @param array $data
     * @return false|string
     */
    public function jsonData($code, $msg, $data = [])
    {
        return $this->jsonResponse($code, $msg, $data);
    }


    /**
     * App接口请求成功时的返回
     * @param array $data
     * @return false|string
     */
    public function jsonSuccessData($data = [])
    {
        return $this->jsonResponse(200,'success',$data);
    }


    /**
     * @param $code
     * @param $msg
     * @param $data
     * @return false|string
     */
    private function jsonResponse($code, $msg, $data)
    {
        $content = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];

        return $content;
    }
}