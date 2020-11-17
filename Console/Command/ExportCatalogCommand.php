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
        CatalogProduct $catalogProduct,
        CatalogProductTierPrice $catalogProductTierPrice,
        CatalogProductLang $catalogProductLang,
        CatalogArticle $catalogArticle,
        CatalogArticleTierPrice $catalogArticleTierPrice,
        CatalogArticleLang $catalogArticleLang
    )
    {
        parent::__construct($state, $progressBar, $probanceHelper);
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

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName($this->command_line);
        $this->setDescription('Export catalog to probance');

        parent::configure();
    }
}
