<?php

namespace Brisum\FBCrawler\Console;

use App\FBCrawler\Utils\AdsService;
use Brisum\FBCrawler\Entity\Company;
use Brisum\FBCrawler\Entity\Post;
use Brisum\FBCrawler\Selenium\Page;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompanyCrawlCommand extends Command
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var AdsService
     */
    protected $adsService;

    /**
     * @param EntityManager $entityManager
     * @param AdsService $adsService
     */
    public function __construct(EntityManager $entityManager, AdsService $adsService) {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->adsService = $adsService;
    }

    protected function configure()
    {
        $this
            ->setName('fb-crawler:company-crawl')
            ->addArgument('company', InputArgument::REQUIRED, 'Company name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $companyName = $input->getArgument('company');
        /** @var Company $company */
        $company = $this->entityManager->getRepository(Company::class)->findOneBy(['name' => $companyName]);

        if (!$company) {
          echo "Not found company with name \"{$company}\"\n";
          return;
        }

        try {
            $preferences = [
                "profile.managed_default_content_settings.notifications" => 2,
                'intl.accept_languages' => 'en-US'
            ];
            $caps = DesiredCapabilities::chrome();
            $caps->setCapability(ChromeOptions::CAPABILITY, ['prefs' => $preferences]);
            $driver = RemoteWebDriver::create('192.168.0.100:4444/wd/hub', $caps);
            $page = new Page($driver);
            // $page->login('facebook@brisum.com', '11cj,frf33');
            $page->login('sasha.manchenko@gmail.com', '11raddet33Fb');
            $page->open($company->getUrl());
            // $page->scrollToBottom();

            $reports = $page->getAds();
            foreach ($reports as $report) {
                $reportId = md5(serialize($report));
                $postOrigin = $this->entityManager->getRepository(Post::class)->findOneBy(['reportId' => $reportId]);
                $post = $postOrigin ? $postOrigin : new Post();

                $post->setCompany($company);
                $post->setReportId($reportId);
                $post->setType($report['type']);
                $post->setContent($report['content']);
                $post->setData($report['data']);
                $this->entityManager->persist($post);
                $this->entityManager->flush($post);

                switch ($report['type']) {
                    case Post::TYPE_PHOTO:
                        $report['data']['image'] = $this->adsService->saveImage($post, $report['data']['image_origin']);
                        $post->setData($report['data']);
                        $this->entityManager->flush($post);
                        break;

                    case Post::TYPE_CAROUSEL:
                        foreach ($report['data']['items'] as $itemKey => $item) {
                            $report['data']['items'][$itemKey]['image'] = $this->adsService
                                ->saveImage($post, $item['image_origin']);
                        }
                        $post->setData($report['data']);
                        $this->entityManager->flush($post);
                        break;
                }
            }
        } catch (Exception $e) {
            //
        } finally {
            $driver->close();
        }
    }
}
