<?php

namespace Probance\M2connector\Ui\DataProvider;

use Probance\M2connector\Model\ResourceModel\MappingArticleLang\Collection;
use Probance\M2connector\Model\ResourceModel\MappingArticleLang\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class MappingArticleLang extends AbstractDataProvider
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
     * MappingArticleLang constructor.
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
            $this->loadedData['stores']['mapping_article_lang_container'][] = $item->getData();
        }

        return $this->loadedData;
    }
}
