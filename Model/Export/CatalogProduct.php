<?php

namespace Walkwizus\Probance\Model\Export;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Walkwizus\Probance\Model\Ftp;
use Magento\Framework\Model\ResourceModel\Iterator;
use Walkwizus\Probance\Model\ResourceModel\MappingProduct\CollectionFactory as ProductMappingCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Walkwizus\Probance\Model\Flow\Formater\CatalogProductFormater;
use Walkwizus\Probance\Model\Flow\Renderer\Factory as RendererFactory;
use Walkwizus\Probance\Model\Flow\Type\Factory as TypeFactory;
use Magento\Catalog\Model\Product\Attribute\Repository as EavRepository;
use Walkwizus\Probance\Model\LogFactory;
use Walkwizus\Probance\Api\LogRepositoryInterface;

class CatalogProduct extends AbstractCatalogProduct
{
    /**
     * @var ProductMappingCollectionFactory
     */
    private $productMappingCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var CatalogProductFormater
     */
    private $catalogProductFormater;

    /**
     * @var RendererFactory
     */
    private $rendererFactory;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var EavRepository
     */
    private $eavRepository;

    /**
     * @var array
     */
    private $mapping;

    /**
     * CatalogProduct constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator
     * @param ProductMappingCollectionFactory $productMappingCollectionFactory
     * @param ProductCollection $productCollection
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param CatalogProductFormater $catalogProductFormater
     * @param RendererFactory $rendererFactory
     * @param TypeFactory $typeFactory
     * @param EavRepository $eavRepository
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        Iterator $iterator,
        ProductMappingCollectionFactory $productMappingCollectionFactory,
        ProductCollection $productCollection,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        CatalogProductFormater $catalogProductFormater,
        RendererFactory $rendererFactory,
        TypeFactory $typeFactory,
        EavRepository $eavRepository,
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

        $this->productMappingCollectionFactory = $productMappingCollectionFactory;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->catalogProductFormater = $catalogProductFormater;
        $this->rendererFactory = $rendererFactory;
        $this->typeFactory = $typeFactory;
        $this->eavRepository = $eavRepository;
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

        if (!isset($parent[0])) {
            try {
                foreach ($this->mapping['items'] as $mappingItem) {
                    $key = $mappingItem['magento_attribute'];
                    $dataKey = $key . '-' . $mappingItem['position'];
                    $method = 'get' . $this->catalogProductFormater->convertToCamelCase($key);

                    $data[$dataKey] = '';

                    if (!empty($mappingItem['user_value'])) {
                        $data[$dataKey] = $mappingItem['user_value'];
                        continue;
                    }

                    if (method_exists($this->catalogProductFormater, $method)) {
                        $data[$dataKey] = $this->catalogProductFormater->$method($product);
                    } else if (method_exists($product, $method)) {
                        $data[$dataKey] = $product->$method();
                    } else {
                        $customAttribute = $product->getCustomAttribute($key);
                        if ($customAttribute) {
                            $data[$dataKey] = $this->formatValueWithRenderer($key, $product);
                        }
                    }

                    $data[$dataKey] = $this->typeFactory
                        ->getInstance($mappingItem['field_type'])
                        ->render($data[$dataKey], $mappingItem['field_limit']);
                }

                $this->file->filePutCsv(
                    $this->csv,
                    $data,
                    $this->probanceHelper->getFlowFormatValue('field_separator'),
                    $this->probanceHelper->getFlowFormatValue('enclosure')
                );

                if ($this->progressBar) {
                    $this->progressBar->setMessage('Processing: ' . $product->getSku(), 'status');
                    $this->progressBar->advance();
                }
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
        $this->mapping = $this->productMappingCollectionFactory
            ->create()
            ->setOrder('position', 'ASC')
            ->toArray();

        $header = [];

        foreach ($this->mapping['items'] as $row) {
            $header[] = $row['probance_attribute'];
        }

        return $header;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->probanceHelper->getCatalogFlowValue('filename_product');
    }

    /**
     * Format value
     *
     * @param $code
     * @param $product
     * @return string
     */
    private function formatValueWithRenderer($code, ProductInterface $product)
    {
        $value = '';

        try {
            $eavAttribute = $this->eavRepository->get($code);
            $value = $this->rendererFactory
                ->getInstance($eavAttribute->getFrontendInput())
                ->render($product, $eavAttribute);
        } catch (NoSuchEntityException $e) {

        }

        return $value;
    }
}
