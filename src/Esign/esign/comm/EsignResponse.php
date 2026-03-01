<?php


namespace esign\comm;
/**
 * esign响应
 * @author  澄泓
 * @date  2022/08/18 9:51
 */

class EsignResponse
{
    private $status;
    private $body;

    /**
     * EsignResponse constructor.
     * @param $status
     * @param $body
     */
    public function __construct($status, $body)
    {
        $this->status = $status;
        $this->body = $body;
    }



    /**
     * @return mixed
     */

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

}
