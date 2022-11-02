<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\Product\Attribute\Repository as EavRepository;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Probance\M2connector\Api\LogRepositoryInterface;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\LogFactory;
use Probance\M2connector\Model\Flow\Renderer\Factory as RendererFactory;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Probance\M2connector\Model\Flow\Formater\CatalogArticleFormater;
use Probance\M2connector\Model\ResourceModel\MappingArticle\CollectionFactory as ArticleMappingCollectionFactory;
use Psr\Log\LoggerInterface;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;

class CatalogArticleLang extends CatalogArticle
{
    /**
     * Suffix use for filename defined configuration path
     */
    const EXPORT_CONF_FILENAME_SUFFIX = '_article_lang';

    /**
     * @var array
     */
    protected $processedProducts = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManager
     */
    protected $storeManager;
    
    /**
     * CatalogArticleLang constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     * @param LoggerInterface $logger

     * @param ArticleMappingCollectionFactory $articleMappingCollectionFactory
     * @param ProductCollection $productCollection
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param CatalogArticleFormater $catalogArticleFormater
     * @param RendererFactory $rendererFactory
     * @param TypeFactory $typeFactory
     * @param EavRepository $eavRepository
     * @param ProductFactory $productFactory

     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManager $storeManager
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        Iterator $iterator,
        LogFactory $logFactory,
        LogRepositoryInterface $logRepository,
        LoggerInterface $logger,

        ArticleMappingCollectionFactory $articleMappingCollectionFactory,
        ProductCollection $productCollection,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        CatalogArticleFormater $catalogArticleFormater,
        RendererFactory $rendererFactory,
        TypeFactory $typeFactory,
        EavRepository $eavRepository,

        ScopeConfigInterface $scopeConfigInterface,
        StoreManager $storeManager,
        ProductFactory $productFactory
    )
    {
        parent::__construct(
            $probanceHelper,
            $directoryList,
            $file,
            $ftp,
            $iterator,
            $logFactory,
            $logRepository,
            $logger,

            $articleMappingCollectionFactory,
            $productCollection,
            $productRepository,
            $configurable,
            $catalogArticleFormater,
            $rendererFactory,
            $typeFactory,
            $eavRepository,
            $productFactory
        );

        $this->scopeConfig = $scopeConfigInterface;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $args
     */
    public function iterateCallback($args)
    {
        try {
            $product = $this->productRepository->getById($args['row']['entity_id']);
            $parent = $this->configurable->getParentIdsByChild($product->getId());
        } catch (NoSuchEntityException $e) {
            return;
        }

        if (!isset($parent[0]) && !in_array($product->getId(), $this->processedProducts)) {
            if ($this->progressBar) {
                $this->progressBar->setMessage('Processing: ' . $product->getSku(), 'status');
            }

            foreach ($product->getWebsiteIds() as $websiteId) {
                $website = $this->storeManager->getWebsite($websiteId);
                foreach ($website->getStores() as $store) {
                    try {
                        $productStore = $this->productFactory->create()->setStoreId($store->getId())->load($product->getId());

                        $textFactory = $this->typeFactory->getInstance('text');
                        $escaper = [
                            '~'.$this->probanceHelper->getFlowFormatValue('enclosure').'~'
                            => $this->probanceHelper->getFlowFormatValue('escape').$this->probanceHelper->getFlowFormatValue('enclosure')
                        ];

                        $data = [
                            $productStore->getId(),
                            $this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORES, $store->getId()),
                            $textFactory->render($productStore->getName(), false, $escaper),
                            $textFactory->render($productStore->getDescription(), false, $escaper),
                            $productStore->getProductUrl()
                        ];

                        $this->file->filePutCsv(
                            $this->csv,
                            $data,
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
            }

            if ($this->progressBar) {
                $this->progressBar->advance();
            }

            $this->processedProducts[] = $product->getId();
        }
        unset($product);
        unset($parent);
    }

    /**
     * @return array
     */
    public function getHeaderData()
    {
        return [
            'article_id',
            'article_area',
            'nom_article',
            'description_article',
            'url_article'
        ];
    }
}
