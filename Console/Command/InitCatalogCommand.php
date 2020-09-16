<?php

namespace Walkwizus\Probance\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Walkwizus\Probance\Helper\ProgressBar;
use Walkwizus\Probance\Model\Export\CatalogArticle;
use Walkwizus\Probance\Model\Export\CatalogArticleLang;
use Walkwizus\Probance\Model\Export\CatalogArticleTierPrice;
use Walkwizus\Probance\Model\Export\CatalogProduct;
use Walkwizus\Probance\Model\Export\CatalogProductLang;
use Walkwizus\Probance\Model\Export\CatalogProductTierPrice;

class InitCatalogCommand extends Command
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
     * InitCatalogCommand constructor.
     *
     * @param State $state
     * @param ProgressBar $progressBar
     * @param CatalogProduct $catalogProduct
     * @param CatalogArticle $catalogArticle
     * @param CatalogProductTierPrice $catalogProductTierPrice
     * @param CatalogArticleTierPrice $catalogArticleTierPrice
     * @param CatalogProductLang $catalogProductLang
     * @param CatalogArticleLang $catalogArticleLang
     */
    public function __construct(
        State $state,
        ProgressBar $progressBar,
        CatalogProduct $catalogProduct,
        CatalogProductTierPrice $catalogProductTierPrice,
        CatalogProductLang $catalogProductLang,
        CatalogArticle $catalogArticle,
        CatalogArticleTierPrice $catalogArticleTierPrice,
        CatalogArticleLang $catalogArticleLang
    )
    {
        $this->state = $state;
        $this->progressBar = $progressBar;
        $this->catalogProduct = $catalogProduct;
        $this->catalogProductTierPrice = $catalogProductTierPrice;
        $this->catalogProductLang = $catalogProductLang;
        $this->catalogArticle = $catalogArticle;
        $this->catalogArticleTierPrice = $catalogArticleTierPrice;
        $this->catalogArticleLang = $catalogArticleLang;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('probance:init:catalog');
        $this->setDescription('Init catalog to probance');

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
            $output->writeln($this->progressBar->getLogo());
            $output->writeln('<info>Preparing to export catalog products...</info>');

            $this->catalogProduct
                ->setProgressBar($this->progressBar->getProgressBar($output))
                ->export();

            $output->writeln("");
            $output->writeln('<info>Preparing to export catalog articles...</info>');

            $this->catalogArticle
                ->setProgressBar($this->progressBar->getProgressBar($output))
                ->export();

            $output->writeln("");
            $output->writeln('<info>Preparing to export catalog products tier price...</info>');

            $this->catalogProductTierPrice
                ->setProgressBar($this->progressBar->getProgressBar($output))
                ->export();

            $output->writeln("");
            $output->writeln('<info>Preparing to export catalog articles tier price...</info>');

            $this->catalogArticleTierPrice
                ->setProgressBar($this->progressBar->getProgressBar($output))
                ->export();

            $output->writeln("");
            $output->writeln('<info>Preparing to export catalog products lang...</info>');

            $this->catalogProductLang
                ->setProgressBar($this->progressBar->getProgressBar($output))
                ->export();

            $output->writeln("");
            $output->writeln('<info>Preparing to export catalog articles lang...</info>');

            $this->catalogArticleLang
                ->setProgressBar($this->progressBar->getProgressBar($output))
                ->export();

            $output->writeln("");
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}