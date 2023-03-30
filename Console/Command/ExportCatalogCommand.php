<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\CatalogArticle\Proxy as CatalogArticle;
use Probance\M2connector\Model\Export\CatalogArticleLang\Proxy as CatalogArticleLang;
use Probance\M2connector\Model\Export\CatalogArticleTierPrice\Proxy as CatalogArticleTierPrice;
use Probance\M2connector\Model\Export\CatalogProduct\Proxy as CatalogProduct;
use Probance\M2connector\Model\Export\CatalogProductLang\Proxy as CatalogProductLang;
use Probance\M2connector\Model\Export\CatalogProductTierPrice\Proxy as CatalogProductTierPrice;
use Psr\Log\LoggerInterface;

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
     * @param State $state
     * @param ProgressBar $progressBar
     * @param Cart $cart
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        State $state,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper,
        LoggerInterface $logger,
        CatalogProduct $catalogProduct,
        CatalogProductTierPrice $catalogProductTierPrice,
        CatalogProductLang $catalogProductLang,
        CatalogArticle $catalogArticle,
        CatalogArticleTierPrice $catalogArticleTierPrice,
        CatalogArticleLang $catalogArticleLang
    )
    {
        parent::__construct($state, $progressBar, $probanceHelper,$logger);
        $this->exportList[] = array(
            'title' => 'Preparing to export catalog products...',
            'job'   => $catalogProduct
        );
        $this->exportList[] = array(
            'title' => 'Preparing to export catalog articles...',
            'job'   => $catalogArticle
        );
        $this->exportList[] = array(
            'title' => 'Preparing to export catalog products tier price...',
            'job'   => $catalogProductTierPrice
        );
        $this->exportList[] = array(
            'title' => 'Preparing to export catalog articles tier price...',
            'job'   => $catalogArticleTierPrice
        );
        $this->exportList[] = array(
            'title' => 'Preparing to export catalog products lang...',
            'job'   => $catalogProductLang
        );
        $this->exportList[] = array(
            'title' => 'Preparing to export catalog articles lang...',
            'job'   => $catalogArticleLang
        );
    }
}
