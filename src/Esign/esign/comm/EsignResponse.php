<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace esign\comm;

/**
 * esign响应.
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
