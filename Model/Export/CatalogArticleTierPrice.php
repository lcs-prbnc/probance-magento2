<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Probance\M2connector\Model\BatchIterator as Iterator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Repository as EavRepository;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\Flow\Renderer\Factory as RendererFactory;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Probance\M2connector\Model\Flow\Formater\CatalogArticleFormater;
use Probance\M2connector\Model\ResourceModel\MappingArticle\CollectionFactory as ArticleMappingCollectionFactory;
use Probance\M2connector\Model\ResourceModel\MappingArticleTierPrice\CollectionFactory as ArticleTierPriceMappingCollectionFactory;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CatalogArticleTierPrice extends CatalogArticle
{
    /**
     * Suffix use for filename defined configuration path
     */
    const EXPORT_CONF_FILENAME_SUFFIX = '_article_tier_price';

    /**
     * @var array
     */
    protected $processedProducts = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * CatalogArticleTierPrice constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator

     * @param ArticleMappingCollectionFactory $articleMappingCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param CatalogArticleFormater $catalogArticleFormater
     * @param RendererFactory $rendererFactory
     * @param TypeFactory $typeFactory
     * @param EavRepository $eavRepository

     * @param ArticleTierPriceMappingCollectionFactory $articleTierPriceMappingCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        Iterator $iterator,

        ArticleMappingCollectionFactory $articleMappingCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        CatalogArticleFormater $catalogArticleFormater,
        RendererFactory $rendererFactory,
        TypeFactory $typeFactory,
        EavRepository $eavRepository,
        ProductFactory $productFactory,

        ArticleTierPriceMappingCollectionFactory $articleTierPriceMappingCollectionFactory,
        ScopeConfigInterface $scopeConfig
    )
    {
        parent::__construct(
            $probanceHelper,
            $directoryList,
            $file,
            $ftp,
            $iterator,

            $articleMappingCollectionFactory,
            $productCollectionFactory,
            $productRepository,
            $configurable,
            $catalogArticleFormater,
            $rendererFactory,
            $typeFactory,
            $eavRepository,
            $productFactory
        );

        $this->flowMappingCollectionFactory = $articleTierPriceMappingCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $entity
     */
    public function iterateCallback($entity)
    {
        try {
            $product = $this->productRepository->getById($entity->getId());

            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                $childs = $this->configurable->getUsedProducts($product);
            } else {
                $childs = [$product];
            }
        } catch (NoSuchEntityException $e) {
            return;
        }

        foreach ($childs as $child) {
            if (!in_array($child->getId(), $this->processedProducts)) {
                if ($this->progressBar) {
                    $this->progressBar->setMessage('Processing: ' . $child->getSku(), 'status');
                }
                $tierPrices = $child->getTierPrices();
                if (!$tierPrices) $tierPrices = [];
                foreach ($tierPrices as $tierPrice) {
                    try {
                        $this->catalogArticleFormater->setFlowTierPrice($tierPrice);

                        foreach ($this->mapping['items'] as $mappingItem) {
                            $key = $mappingItem['magento_attribute'];
                            $dataKey = $key . '-' . $mappingItem['position'];
                            $method = 'get' . $this->catalogArticleFormater->convertToCamelCase($key);

                            $data[$dataKey] = '';

                            if (!empty($mappingItem['user_value'])) {
                                $data[$dataKey] = $mappingItem['user_value'];
                                continue;
                            }
                            if (method_exists($this->catalogArticleFormater, $method)) {
                                $data[$dataKey] = $this->catalogArticleFormater->$method($product);
                            } else if (method_exists($product, $method)) {
                                $data[$dataKey] = $product->$method();
                            } else {
                                $customAttribute = $product->getCustomAttribute($key);
                                if ($customAttribute) {
                                    $data[$dataKey] = $this->formatValueWithRenderer($key, $product);
                                }
                            }

                            $escaper = [
                                '~'.$this->probanceHelper->getFlowFormatValue('enclosure').'~'
                                => $this->probanceHelper->getFlowFormatValue('escape').$this->probanceHelper->getFlowFormatValue('enclosure')
                            ];

                            $data[$dataKey] = $this->typeFactory
                                ->getInstance($mappingItem['field_type'])
                                ->render($data[$dataKey], $mappingItem['field_limit'], $escaper);
                        }

                        $this->file->filePutCsv(
                            $this->csv,
                            $this->probanceHelper->postProcessData($data),
                            $this->probanceHelper->getFlowFormatValue('field_separator'),
                            $this->probanceHelper->getFlowFormatValue('enclosure')
                        );
                    } catch (\Exception $e) {
                        $this->errors[] = [
                            'message' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ];
                    }
                }

                $this->processedProducts[] = $product->getId();
            }
        }
        unset($product);
        unset($childs);
                
        if ($this->progressBar) {
            $this->progressBar->advance();
        }
    }

}
