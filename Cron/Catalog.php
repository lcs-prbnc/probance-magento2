<?php

namespace Walkwizus\Probance\Cron;

use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Export\CatalogArticle;
use Walkwizus\Probance\Model\Export\CatalogArticleLang;
use Walkwizus\Probance\Model\Export\CatalogArticleTierPrice;
use Walkwizus\Probance\Model\Export\CatalogProduct;
use Walkwizus\Probance\Model\Export\CatalogProductLang;
use Walkwizus\Probance\Model\Export\CatalogProductTierPrice;

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
