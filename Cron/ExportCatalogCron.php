<?php

namespace Probance\M2connector\Cron;

use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\CatalogProduct\Proxy as CatalogProduct;
use Probance\M2connector\Model\Export\CatalogProductLang\Proxy as CatalogProductLang;
use Probance\M2connector\Model\Export\CatalogProductTierPrice\Proxy as CatalogProductTierPrice;
use Probance\M2connector\Model\Export\CatalogArticle\Proxy as CatalogArticle;
use Probance\M2connector\Model\Export\CatalogArticleLang\Proxy as CatalogArticleLang;
use Probance\M2connector\Model\Export\CatalogArticleTierPrice\Proxy as CatalogArticleTierPrice;

use Magento\Framework\Shell;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Console\Output\ConsoleOutput;

class ExportCatalogCron extends AbstractFlowExportCron
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'catalog';

    /**
     *  ExportCatalogCron constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param Shell $shell
     * @param PhpExecutableFinder $phpExecutableFinder
     * @param ConsoleOutput $output
     * @param CatalogProduct $catalogProduct
     * @param CatalogProductTierPrice $catalogProductTierPrice
     * @param CatalogProductLang $catalogProductLang
     * @param CatalogArticle $catalogArticle
     * @param CatalogArticleTierPrice $catalogArticleTierPrice
     * @param CatalogArticleLang $catalogArticleLang
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        Shell $shell,
        PhpExecutableFinder $phpExecutableFinder,
        ConsoleOutput $output,
        CatalogProduct $catalogProduct,
        CatalogProductTierPrice $catalogProductTierPrice,
        CatalogProductLang $catalogProductLang,
        CatalogArticle $catalogArticle,
        CatalogArticleTierPrice $catalogArticleTierPrice,
        CatalogArticleLang $catalogArticleLang
    )
    {
        parent::__construct($probanceHelper, $shell, $phpExecutableFinder, $output);
        if ($probanceHelper->getGivenFlowValue('catalog', 'flow_product_enabled')) {
            $this->exportList[] = array(
                'title' => __('Preparing to export catalog products...'),
                'job'   => $catalogProduct
            );
        } else {
            $this->exportList[] = array(
                'title' => __('Catalog products flow is diabled.')
            );
        }
        if ($probanceHelper->getGivenFlowValue('catalog', 'flow_article_enabled')) {
            $this->exportList[] = array(
                'title' => __('Preparing to export catalog articles...'),
                'job'   => $catalogArticle
            );
        } else {
            $this->exportList[] = array(
                'title' => __('Catalog articles flow is diabled.')
            );
        }
        if ($probanceHelper->getGivenFlowValue('catalog', 'flow_product_tier_price_enabled')) {
            $this->exportList[] = array(
                'title' => __('Preparing to export catalog products tier price...'),
                'job'   => $catalogProductTierPrice
            );
        } else {
            $this->exportList[] = array(
                'title' => __('Catalog products tier price flow is diabled.')
            );
        }
        if ($probanceHelper->getGivenFlowValue('catalog', 'flow_article_tier_price_enabled')) {
            $this->exportList[] = array(
                'title' => __('Preparing to export catalog articles tier price...'),
                'job'   => $catalogArticleTierPrice
            );
        } else {
            $this->exportList[] = array(
                'title' => __('Catalog articles tier price flow is diabled.')
            );
        }
        if ($probanceHelper->getGivenFlowValue('catalog', 'flow_product_lang_enabled')) {
            $this->exportList[] = array(
                'title' => __('Preparing to export catalog products lang...'),
                'job'   => $catalogProductLang
            );
        } else {
            $this->exportList[] = array(
                'title' => __('Catalog products lang flow is diabled.')
            );
        }
        if ($probanceHelper->getGivenFlowValue('catalog', 'flow_article_lang_enabled')) {
            $this->exportList[] = array(
                'title' => __('Preparing to export catalog articles lang...'),
                'job'   => $catalogArticleLang
            );
        } else {
            $this->exportList[] = array(
                'title' => __('Catalog articles lang flow is diabled.')
            );
        }
    }
}
