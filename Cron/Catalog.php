<?php

namespace Walkwizus\Probance\Cron;

use Walkwizus\Probance\Model\Config\Source\ExportType;
use Walkwizus\Probance\Model\Config\Source\Sync\Mode;
use Walkwizus\Probance\Model\Export\CatalogArticle;
use Walkwizus\Probance\Model\Export\CatalogArticleLang;
use Walkwizus\Probance\Model\Export\CatalogArticleTierPrice;
use Walkwizus\Probance\Model\Export\CatalogProduct;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Export\CatalogProductLang;
use Walkwizus\Probance\Model\Export\CatalogProductTierPrice;

class Catalog
{
    /**
     * @var CatalogProduct
     */
    private $catalogProduct;

    /**
     * @var CatalogArticle
     */
    private $catalogArticle;

    /**
     * @var CatalogProductTierPrice
     */
    private $catalogProductTierPrice;

    /**
     * @var CatalogArticleTierPrice
     */
    private $catalogArticleTierPrice;

    /**
     * @var CatalogProductLang
     */
    private $catalogProductLang;

    /**
     * @var CatalogArticleLang
     */
    private $catalogArticleLang;

    /**
     * @var ProbanceHelper
     */
    private $probanceHelper;

    /**
     * Catalog constructor.
     *
     * @param CatalogProduct $catalogProduct
     * @param CatalogArticle $catalogArticle
     * @param CatalogProductTierPrice $catalogProductTierPrice
     * @param CatalogArticleTierPrice $catalogArticleTierPrice
     * @param CatalogProductLang $catalogProductLang
     * @param CatalogArticleLang $catalogArticleLang
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        CatalogProduct $catalogProduct,
        CatalogProductTierPrice $catalogProductTierPrice,
        CatalogProductLang $catalogProductLang,
        CatalogArticle $catalogArticle,
        CatalogArticleTierPrice $catalogArticleTierPrice,
        CatalogArticleLang $catalogArticleLang,
        ProbanceHelper $probanceHelper
    )
    {
        $this->catalogProduct = $catalogProduct;
        $this->catalogProductTierPrice = $catalogProductTierPrice;
        $this->catalogProductLang = $catalogProductLang;
        $this->catalogArticle = $catalogArticle;
        $this->catalogArticleTierPrice = $catalogArticleTierPrice;
        $this->catalogArticleLang = $catalogArticleLang;
        $this->probanceHelper = $probanceHelper;
    }

    /**
     * Export catalog product
     */
    public function execute()
    {
        if ($this->probanceHelper->getCustomerFlowValue('sync_mode') != Mode::SYNC_MODE_SCHEDULED_TASK) {
            return;
        }

        $range = $this->probanceHelper->getExportRangeDate();

        if ($this->probanceHelper->getCatalogFlowValue('export_type') == ExportType::EXPORT_TYPE_UPDATED) {
            $this->catalogProduct->setRange($range['from'], $range['to']);
            $this->catalogArticle->setRange($range['from'], $range['to']);
            $this->catalogProductTierPrice->setRange($range['from'], $range['to']);
            $this->catalogArticleTierPrice->setRange($range['from'], $range['to']);
            $this->catalogProductLang->setRange($range['from'], $range['to']);
            $this->catalogArticleLang->setRange($range['from'], $range['to']);
        }

        $this->catalogProduct->export();
        $this->catalogArticle->export();
        $this->catalogProductTierPrice->export();
        $this->catalogArticleTierPrice->export();
        $this->catalogProductLang->export();
        $this->catalogArticleLang->export();

        return;
    }
}