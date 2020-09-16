<?php

namespace Walkwizus\Probance\Model\Flow\Type;

use Magento\Framework\ObjectManagerInterface;

class Factory
{
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $type
     * @return \Walkwizus\Probance\Model\Flow\Type\TypeInterface
     */
    public function getInstance($type)
    {
        return $this->objectManager->get(self::class . '\\' . ucfirst($type));
    }
}