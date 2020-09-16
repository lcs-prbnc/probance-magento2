<?php

namespace Walkwizus\Probance\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Walkwizus\Probance\Model\Api;

class CreateOrder implements ObserverInterface
{
    /**
     * @var Api
     */
    private $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function execute(Observer $observer)
    {

    }
}