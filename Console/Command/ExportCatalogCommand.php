<?php

namespace Walkwizus\Probance\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Walkwizus\Probance\Helper\ProgressBar;
use Walkwizus\Probance\Model\Config\Source\ExportType;
use Walkwizus\Probance\Model\Export\CatalogArticle;
use Walkwizus\Probance\Model\Export\CatalogArticleLang;
use Walkwizus\Probance\Model\Export\CatalogArticleTierPrice;
use Walkwizus\Probance\Model\Export\CatalogProduct;
use Walkwizus\Probance\Model\Export\CatalogProductLang;
use Walkwizus\Probance\Model\Export\CatalogProductTierPrice;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;

class ExportCatalogCommand extends Command
{
    /**
     * @var State
     */
    protected $state;

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
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var ProbanceHelper
     */
    private $probanceHelper;

    /**
     * InitCatalogCommand constructor.
     *
     * @param State $state
     * @param CatalogProduct $catalogProduct
     * @param CatalogProductTierPrice $catalogProductTierPrice
     * @param CatalogProductLang $catalogProductLang
     * @param CatalogArticle $catalogArticle
     * @param CatalogArticleTierPrice $catalogArticleTierPrice
     * @param CatalogArticleLang $catalogArticleLang
     * @param ProgressBar $progressBar
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        State $state,
        CatalogProduct $catalogProduct,
        CatalogProductTierPrice $catalogProductTierPrice,
        CatalogProductLang $catalogProductLang,
        CatalogArticle $catalogArticle,
        CatalogArticleTierPrice $catalogArticleTierPrice,
        CatalogArticleLang $catalogArticleLang,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper
    )
    {
        $this->state = $state;
        $this->catalogProduct = $catalogProduct;
        $this->catalogArticle = $catalogArticle;
        $this->catalogProductTierPrice = $catalogProductTierPrice;
        $this->catalogArticleTierPrice = $catalogArticleTierPrice;
        $this->catalogProductLang = $catalogProductLang;
        $this->catalogArticleLang = $catalogArticleLang;
        $this->progressBar = $progressBar;
        $this->probanceHelper = $probanceHelper;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('probance:export:catalog');
        $this->setDescription('Export catalog to probance');

        parent::configure();
    }

    /**
     * Execute product export
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_CRONTAB);

        try {
            $range = $this->probanceHelper->getExportRangeDate();

            $output->writeln($this->progressBar->getLogo());
            $output->writeln('<info>Preparing to export catalog products...</info>');

            $this->catalogProduct->setProgressBar($this->progressBar->getProgressBar($output));

            if ($this->probanceHelper->getCatalogFlowValue('export_type') == ExportType::EXPORT_TYPE_UPDATED) {
                $this->catalogProduct->setRange($range['from'], $range['to']);
            }

            $this->catalogProduct->export();

            $output->writeln("");
            $output->writeln('<info>Preparing to export catalog articles...</info>');

            $this->catalogArticle->setProgressBar($this->progressBar->getProgressBar($output));

            if ($this->probanceHelper->getCatalogFlowValue('export_type') == ExportType::EXPORT_TYPE_UPDATED) {
                $this->catalogArticle->setRange($range['from'], $range['to']);
            }

            $this->catalogArticle->export();

            $output->writeln("");
            $output->writeln('<info>Preparing to export catalog products tier price...</info>');

            $this->catalogProductTierPrice->setProgressBar($this->progressBar->getProgressBar($output));

            if ($this->probanceHelper->getCatalogFlowValue('export_type') == ExportType::EXPORT_TYPE_UPDATED) {
                $this->catalogProductTierPrice->setRange($range['from'], $range['to']);
            }

            $this->catalogProductTierPrice->export();

            $output->writeln("");
            $output->writeln('<info>Preparing to export catalog articles tier price...</info>');

            $this->catalogArticleTierPrice->setProgressBar($this->progressBar->getProgressBar($output));

            if ($this->probanceHelper->getCatalogFlowValue('export_type') == ExportType::EXPORT_TYPE_UPDATED) {
                $this->catalogArticleTierPrice->setRange($range['from'], $range['to']);
            }

            $this->catalogArticleTierPrice->export();

            $output->writeln("");
            $output->writeln('<info>Preparing to export catalog products lang...</info>');

            $this->catalogProductLang->setProgressBar($this->progressBar->getProgressBar($output));

            if ($this->probanceHelper->getCatalogFlowValue('export_type') == ExportType::EXPORT_TYPE_UPDATED) {
                $this->catalogProductLang->setRange($range['from'], $range['to']);
            }

            $this->catalogProductLang->export();

            $output->writeln("");
            $output->writeln('<info>Preparing to export catalog articles lang...</info>');

            $this->catalogArticleLang->setProgressBar($this->progressBar->getProgressBar($output));

            if ($this->probanceHelper->getCatalogFlowValue('export_type') == ExportType::EXPORT_TYPE_UPDATED) {
                $this->catalogArticleLang->setRange($range['from'], $range['to']);
            }

            $this->catalogArticleLang->export();

            $output->writeln("");
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}