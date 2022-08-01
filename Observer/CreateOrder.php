<?php

namespace Probance\M2connector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Probance\M2connector\Model\Api;

class CreateOrder implements ObserverInterface
{
    /**
     * @var Api
     */
    protected $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function execute(Observer $observer)
    {

    }
}
