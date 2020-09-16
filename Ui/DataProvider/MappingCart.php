<?php

namespace Walkwizus\Probance\Ui\DataProvider;

use Walkwizus\Probance\Model\ResourceModel\MappingCart\Collection;
use Walkwizus\Probance\Model\ResourceModel\MappingCart\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class MappingCart extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var CollectionFactory
     */
    protected $rowCollection;

    /**
     * MappingOrder constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Collection $collection
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $collection;
        $this->rowCollection = $collectionFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Retrieve mapping data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $collection = $this->rowCollection->create()->setOrder('position', 'ASC');
        $items = $collection->getItems();

        foreach ($items as $item) {
            $this->loadedData['stores']['mapping_cart_container'][] = $item->getData();
        }

        return $this->loadedData;
    }
}