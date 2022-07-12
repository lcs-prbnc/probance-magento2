<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\CatalogArticle;
use Probance\M2connector\Model\Export\CatalogArticleLang;
use Probance\M2connector\Model\Export\CatalogArticleTierPrice;
use Probance\M2connector\Model\Export\CatalogProduct;
use Probance\M2connector\Model\Export\CatalogProductLang;
use Probance\M2connector\Model\Export\CatalogProductTierPrice;

class InitCatalogCommand extends ExportCatalogCommand
{
    /**
     * @var Boolean
     */
    protected $can_use_range = false;

    /**
     * @var Boolean
     */
    protected $is_init = true;

    /**
     * @var string
     */
    protected $command_line = 'probance:init:catalog';
}
