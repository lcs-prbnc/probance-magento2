<?php

namespace Walkwizus\Probance\Model\Export;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Walkwizus\Probance\Model\Ftp;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Walkwizus\Probance\Model\LogFactory;
use Walkwizus\Probance\Api\LogRepositoryInterface;

class CatalogProductLang extends AbstractCatalogProduct
{
    /**
     * @var array
     */
    private $processedProducts = [];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * CatalogProductLang constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param StoreManager $storeManager
     * @param Iterator $iterator
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param ProductCollection $productCollection
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        ScopeConfigInterface $scopeConfigInterface,
        StoreManager $storeManager,
        Iterator $iterator,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        ProductCollection $productCollection,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        LogFactory $logFactory,
        LogRepositoryInterface $logRepository
    )
    {
        parent::__construct(
            $probanceHelper,
            $directoryList,
            $file,
            $ftp,
            $iterator,
            $productCollection,
            $logFactory,
            $logRepository
        );

        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
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

        }

        if (!isset($parent[0]) && !in_array($product->getId(), $this->processedProducts)) {
            try {
                foreach ($product->getWebsiteIds() as $websiteId) {
                    $website = $this->storeManager->getWebsite($websiteId);
                    foreach ($website->getStores() as $store) {
                        $productStore = $this->productRepository->getById($product->getId(), false, $store->getId());
                        $this->file->filePutCsv(
                            $this->csv,
                            [
                                $productStore->getId(),
                                $this->scopeConfigInterface->getValue('general/locale/code', ScopeInterface::SCOPE_STORES, $store->getId()),
                                $productStore->getName(),
                                $productStore->getDescription(),
                                $productStore->getProductUrl(),
                            ],
                            $this->probanceHelper->getFlowFormatValue('field_separator'),
                            $this->probanceHelper->getFlowFormatValue('enclosure')
                        );

                    }
                }

                if ($this->progressBar) {
                    $this->progressBar->setMessage('Processing: ' . $product->getSku(), 'status');
                    $this->progressBar->advance();
                }

                $this->processedProducts[] = $product->getId();
            } catch (FileSystemException $e) {

            } catch (\Exception $e) {
                $this->errors[] = [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTrace(),
                ];
            }
        }
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

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->probanceHelper->getCatalogFlowValue('filename_product_lang');
    }
}