<?php

namespace Probance\M2connector\Model;

use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;
use Probance\M2connector\Helper\Data;

class Api
{
    /**
     * Endpoint API
     */
    const ENDPOINT = '%s/webtrax/data/rtflow.action?site=%s.%s&flow=%s';

    /**
     * @var Data
     */
    protected $probanceHelper;

    /**
     * @var Curl
     */
    protected $curl;

    protected $logger;

    /**
     * Api constructor.
     *
     * @param Data $data
     * @param Curl $curl
     */
    public function __construct(
        Data $data,
        Curl $curl,
        LoggerInterface $logger
    )
    {
        $this->probanceHelper = $data;
        $this->curl = $curl;
        $this->logger = $logger;
    }

    /**
     * Call Probance API
     *
     * @param $flow
     * @param array $params
     * @return string
     */
    public function call($flow, $params = [])
    {
        $url = sprintf(
            self::ENDPOINT,
            $this->probanceHelper->getApiValue('url'),
            $this->probanceHelper->getApiValue('client'),
            $this->probanceHelper->getApiValue('token'),
            $flow
        );

        $this->curl->setHeaders([
            'Content-Type' => 'application/json'
        ]);
        $this->curl->post($url, json_encode($params));

        if ($this->curl->getStatus() != 200) {

        }

        return $this->curl->getBody();
    }
}
