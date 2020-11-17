<?php

namespace Walkwizus\Probance\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\Product\Attribute\Repository as EavRepository;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Walkwizus\Probance\Api\LogRepositoryInterface;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Ftp;
use Walkwizus\Probance\Model\LogFactory;
use Walkwizus\Probance\Model\Flow\Renderer\Factory as RendererFactory;
use Walkwizus\Probance\Model\Flow\Type\Factory as TypeFactory;
use Walkwizus\Probance\Model\Flow\Formater\CatalogProductFormater;
use Walkwizus\Probance\Model\ResourceModel\MappingArticle\CollectionFactory as ArticleMappingCollectionFactory;

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
    private $processedProducts = [];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManager
     */
    private $storeManager;

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

     * @param ArticleMappingCollectionFactory $articleMappingCollectionFactory
     * @param ProductCollection $productCollection
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param CatalogProductFormater $catalogProductFormater
     * @param RendererFactory $rendererFactory
     * @param TypeFactory $typeFactory
     * @param EavRepository $eavRepository

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

        ArticleMappingCollectionFactory $articleMappingCollectionFactory,
        ProductCollection $productCollection,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        CatalogProductFormater $catalogProductFormater,
        RendererFactory $rendererFactory,
        TypeFactory $typeFactory,
        EavRepository $eavRepository,

        ScopeConfigInterface $scopeConfigInterface,
        StoreManager $storeManager
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

            $articleMappingCollectionFactory,
            $productCollection,
            $productRepository,
            $configurable,
            $catalogProductFormater,
            $rendererFactory,
            $typeFactory,
            $eavRepository
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
            foreach ($product->getWebsiteIds() as $websiteId) {
                $website = $this->storeManager->getWebsite($websiteId);
                foreach ($website->getStores() as $store) {
                    try {
                        $productStore = $this->productRepository->getById($product->getId(), false, $store->getId());

                        $data = [
                            $productStore->getId(),
                            $this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORES, $store->getId()),
                            $productStore->getName(),
                            $productStore->getDescription(),
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
                            'trace' => $e->getTrace(),
                        ];
                    }
                }
            }

            if ($this->progressBar) {
                $this->progressBar->setMessage('Processing: ' . $product->getSku(), 'status');
                $this->progressBar->advance();
            }

            $this->processedProducts[] = $product->getId();
        }
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
