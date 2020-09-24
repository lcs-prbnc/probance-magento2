<?php

namespace Walkwizus\Probance\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Walkwizus\Probance\Helper\ProgressBar;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Export\CatalogArticle;
use Walkwizus\Probance\Model\Export\CatalogArticleLang;
use Walkwizus\Probance\Model\Export\CatalogArticleTierPrice;
use Walkwizus\Probance\Model\Export\CatalogProduct;
use Walkwizus\Probance\Model\Export\CatalogProductLang;
use Walkwizus\Probance\Model\Export\CatalogProductTierPrice;

class InitCatalogCommand extends ExportCatalogCommand
{
    /**
     * @var Boolean
     */
    protected $can_use_range = false;

    /**
     * @var string
     */
    protected $command_line = 'probance:init:catalog';
}
