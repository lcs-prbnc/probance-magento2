<?php

namespace Probance\M2connector\Cron;

use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\CatalogArticle;
use Probance\M2connector\Model\Export\CatalogArticleLang;
use Probance\M2connector\Model\Export\CatalogArticleTierPrice;
use Probance\M2connector\Model\Export\CatalogProduct;
use Probance\M2connector\Model\Export\CatalogProductLang;
use Probance\M2connector\Model\Export\CatalogProductTierPrice;

class Catalog extends AbstractFlow
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'catalog';

    /**
     * Coupon constructor.
     *
     * @param CouponExport $couponExport
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        CatalogProduct $catalogProduct,
        CatalogProductTierPrice $catalogProductTierPrice,
        CatalogProductLang $catalogProductLang,
        CatalogArticle $catalogArticle,
        CatalogArticleTierPrice $catalogArticleTierPrice,
        CatalogArticleLang $catalogArticleLang
    )
    {
        parent::__construct($probanceHelper);
        if ($probanceHelper->getGivenFlowValue('catalog', 'enabled')) {
            if ($probanceHelper->getGivenFlowValue('catalog', 'flow_product_enabled')) {
                $this->exportList[] = $catalogProduct;
            }
            if ($probanceHelper->getGivenFlowValue('catalog', 'flow_article_enabled')) {
                $this->exportList[] = $catalogArticle;
            }
            if ($probanceHelper->getGivenFlowValue('catalog', 'flow_product_tier_price_enabled')) {
                $this->exportList[] = $catalogProductTierPrice;
            }
            if ($probanceHelper->getGivenFlowValue('catalog', 'flow_article_tier_price_enabled')) {
                $this->exportList[] = $catalogArticleTierPrice;
            }
            if ($probanceHelper->getGivenFlowValue('catalog', 'flow_product_lang_enabled')) {
                $this->exportList[] = $catalogProductLang;
            }
            if ($probanceHelper->getGivenFlowValue('catalog', 'flow_article_lang_enabled')) {
                $this->exportList[] = $catalogArticleLang;
            }
        }
    }
}
