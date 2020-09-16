<?php

namespace Walkwizus\Probance\Model\Export;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Walkwizus\Probance\Model\Ftp;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Walkwizus\Probance\Model\Flow\Renderer\Factory as RendererFactory;
use Walkwizus\Probance\Model\Flow\Type\Factory as TypeFactory;
use Walkwizus\Probance\Model\LogFactory;
use Walkwizus\Probance\Api\LogRepositoryInterface;

class CatalogArticleLang extends AbstractCatalogProduct
{
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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var RendererFactory
     */
    private $rendererFactory;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * CatalogArticleLang constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManager $storeManager
     * @param ProductCollection $productCollection
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param RendererFactory $rendererFactory
     * @param TypeFactory $typeFactory
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        Iterator $iterator,
        ScopeConfigInterface $scopeConfig,
        StoreManager $storeManager,
        ProductCollection $productCollection,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        RendererFactory $rendererFactory,
        TypeFactory $typeFactory,
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

        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->rendererFactory = $rendererFactory;
        $this->typeFactory = $typeFactory;
    }

    /**
     * @param $args
     * @throws LocalizedException
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

                    } catch (FileSystemException $e) {

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

    /**
     * Get Filename
     *
     * @return mixed
     */
    public function getFilename()
    {
        return $this->probanceHelper->getCatalogFlowValue('filename_article_lang');
    }
}