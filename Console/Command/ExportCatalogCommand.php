<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\PhpExecutableFinder;
use Magento\Framework\Config\Scope;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DirectoryList;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\CatalogArticle\Proxy as CatalogArticle;
use Probance\M2connector\Model\Export\CatalogArticleLang\Proxy as CatalogArticleLang;
use Probance\M2connector\Model\Export\CatalogArticleTierPrice\Proxy as CatalogArticleTierPrice;
use Probance\M2connector\Model\Export\CatalogProduct\Proxy as CatalogProduct;
use Probance\M2connector\Model\Export\CatalogProductLang\Proxy as CatalogProductLang;
use Probance\M2connector\Model\Export\CatalogProductTierPrice\Proxy as CatalogProductTierPrice;
use Probance\M2connector\Model\Shell;

class ExportCatalogCommand extends AbstractFlowExportCommand
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'catalog';

    /**
     * @var string
     */
    protected $command_line = 'probance:export:catalog';

    /**
     * @var string
     */
    protected $command_desc = 'Export catalog to probance';

    /**
     * ExportCartCommand constructor.
     *
     * @param Scope $scope
     * @param State $state
     * @param DirectoryList $dir
     * @param ProgressBar $progressBar
     * @param ProbanceHelper $probanceHelper
     * @param Shell $shell
     * @param PhpExecutableFinder $phpExecutableFinder
     * @param CatalogProduct $catalogProduct
     * @param CatalogProductTierPrice $catalogProductTierPrice
     * @param CatalogProductLang $catalogProductLang
     * @param CatalogArticle $catalogArticle
     * @param CatalogArticleTierPrice $catalogArticleTierPrice
     * @param CatalogArticleLang $catalogArticleLang
     */
    public function __construct(
        Scope $scope,
        State $state,
        DirectoryList $dir,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper,
        Shell $shell,
        PhpExecutableFinder $phpExecutableFinder,
        CatalogProduct $catalogProduct,
        CatalogProductTierPrice $catalogProductTierPrice,
        CatalogProductLang $catalogProductLang,
        CatalogArticle $catalogArticle,
        CatalogArticleTierPrice $catalogArticleTierPrice,
        CatalogArticleLang $catalogArticleLang
    )
    {
        parent::__construct($scope, $state, $dir, $progressBar, $probanceHelper, $shell, $phpExecutableFinder);
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
