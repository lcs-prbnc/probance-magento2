<?php

namespace Probance\M2connector\Model\Config\Source\Attribute;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\Serializer\Json;

class AbstractAttribute
{
    const CACHE_PREFIX = 'Probance_Source_Attribute_';
    const CACHE_LIFETIME = 60;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var Json
     */
    protected $json;

    /**
     * Attributes constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     * @param CacheInterface $cache
     * @param Json $json
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        CacheInterface $cache,
        Json $json
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->cache = $cache;
        $this->json = $json;
    }

    /**
     * Return attribute option array from cache
     * return null || []
     */
    public function loadAttributeArray()
    {
        $attrArray = null;
        $cacheKey = self::CACHE_PREFIX.$this::CACHE_NAME;
        $cacheValue = $this->cache->load($cacheKey);
        if ($cacheValue) {
            $attrArray = $this->json->unserialize($cacheValue);
        }
        return $attrArray;
    }

    /**
     * Save attribute option array in cache
     * return this
     */
    public function saveAttributeArray($attrArray)
    {
        $cacheKey = self::CACHE_PREFIX.$this::CACHE_NAME;
        $cacheValue = $this->json->serialize($attrArray);
        $this->cache->save($cacheValue, $cacheKey, [Attribute::CACHE_TAG], self::CACHE_LIFETIME);
        return $this;
    }
}
