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
        $this->exportList[] = $catalogProduct;
        $this->exportList[] = $catalogProductTierPrice;
        $this->exportList[] = $catalogProductLang;
        $this->exportList[] = $catalogArticle;
        $this->exportList[] = $catalogArticleTierPrice;
        $this->exportList[] = $catalogArticleLang;
    }
}
