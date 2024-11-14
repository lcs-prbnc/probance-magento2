<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Probance\M2connector\Model\BatchIterator as Iterator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Repository as EavRepository;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\Flow\Renderer\Factory as RendererFactory;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Probance\M2connector\Model\Flow\Formater\CatalogProductFormater;
use Probance\M2connector\Model\ResourceModel\MappingProduct\CollectionFactory as ProductMappingCollectionFactory;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;

class CatalogProductLang extends CatalogProduct
{
    /**
     * Suffix use for filename defined configuration path
     */
    const EXPORT_CONF_FILENAME_SUFFIX = '_product_lang';

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
     * CatalogProductLang constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator

     * @param ProductMappingCollectionFactory $productMappingCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param CatalogProductFormater $catalogProductFormater
     * @param RendererFactory $rendererFactory
     * @param TypeFactory $typeFactory
     * @param EavRepository $eavRepository

     * @param ScopeConfigInterface $scopeConfigInterface
     * @param ProductFactory $productFactory
     * @param StoreManager $storeManager
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        Iterator $iterator,

        ProductMappingCollectionFactory $productMappingCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        CatalogProductFormater $catalogProductFormater,
        RendererFactory $rendererFactory,
        TypeFactory $typeFactory,
        EavRepository $eavRepository,

        ScopeConfigInterface $scopeConfigInterface,
        ProductFactory $productFactory,
        StoreManager $storeManager
    )
    {
        parent::__construct(
            $probanceHelper,
            $directoryList,
            $file,
            $ftp,
            $iterator,

            $productMappingCollectionFactory,
            $productCollectionFactory,
            $productRepository,
            $configurable,
            $catalogProductFormater,
            $rendererFactory,
            $typeFactory,
            $eavRepository,
            $productFactory
        );

        $this->scopeConfig = $scopeConfigInterface;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $entity
     */
    public function iterateCallback($entity)
    {
        try {
            $product = $this->productRepository->getById($entity->getId());
            $parent = $this->configurable->getParentIdsByChild($product->getId());
        } catch (NoSuchEntityException $e) {

        }

        if (!isset($parent[0]) && !in_array($product->getId(), $this->processedProducts)) {
            if ($this->progressBar) {
                $this->progressBar->setMessage('Processing: ' . $product->getSku(), 'status');
            }

            $lang_stores =  $this->probanceHelper->getGivenFlowValue($this->flow,'lang_stores');
            if (!$lang_stores) {
                $flowStore = $this->probanceHelper->getFlowStore();
                $lang_stores = [$flowStore];
                $langs = [$this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORE, $flowStore)];
                // Check if store in same website used for other language
                foreach ($product->getWebsiteIds() as $websiteId) {
                    $website = $this->storeManager->getWebsite($websiteId);
                    foreach ($website->getStores() as $store) {
                        if ($store->isActive() && $store->getId() != $flowStore) {
                            $langStore = $this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORE, $store->getId());
                            if (!in_array($langStore, $langs)) $lang_stores[] = $store->getId();
                        }
                    }
                }
            } else {
                $lang_stores = array_map('trim', explode(',', $lang_stores));
            }

            foreach ($lang_stores as $storeId) {
                try {
                    $productStore = $this->productFactory->create()->setStoreId($storeId)->load($product->getId());
                    $textFactory = $this->typeFactory->getInstance('text');
                    $escaper = [
                        '~'.$this->probanceHelper->getFlowFormatValue('enclosure').'~'
                        => $this->probanceHelper->getFlowFormatValue('escape').$this->probanceHelper->getFlowFormatValue('enclosure')
                    ];

                    $data = [
                        $productStore->getId(),
                        $this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORE, $storeId),
                        $textFactory->render($productStore->getName(), false, $escaper),
                        $textFactory->render($productStore->getDescription(), false, $escaper),
                        $productStore->getProductUrl()
                    ];

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

            if ($this->progressBar) {
                $this->progressBar->advance();
            }

            $this->processedProducts[] = $product->getId();
        }
        unset($product);
        unset($parent);
    }

    /**
     * Get header data
     *
     * @return array
     */
    public function getHeaderData()
    {
        return [
            'product_id',
            'product_area',
            'nom_produit',
            'description_produit',
            'url_product',
        ];
    }
}
