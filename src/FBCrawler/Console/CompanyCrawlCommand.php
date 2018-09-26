<?php

namespace Brisum\FBCrawler\Console;

use Brisum\FBCrawler\Entity\Company;
use Brisum\FBCrawler\Entity\Post;
use Brisum\FBCrawler\Selenium\Page;
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
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        parent::__construct();

        $this->entityManager = $entityManager;
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

                "profile.managed_default_content_settings.notifications" => 2
            ];
            $caps = DesiredCapabilities::chrome();
            $caps->setCapability(ChromeOptions::CAPABILITY, ['prefs' => $preferences]);
            $driver = RemoteWebDriver::create('192.168.0.100:4444/wd/hub', $caps);
            $page = new Page($driver);
            $page->login('facebook@brisum.com', '11cj,frf33');
            $page->open($company->getUrl());
            $page->scrollToBottom();

            $reports = $page->getReports();
            foreach ($reports as $report) {
                $reportId = "{$report['report_id']}-{$report['publish_time']}";
                $postOrigin = $this->entityManager->getRepository(Post::class)->findOneBy(['reportId' => $reportId]);
                $post = $postOrigin ? $postOrigin : new Post();

                $post->setCompany($company);
                $post->setReportId($reportId);
                $post->setTitle($report['title']);
                $post->setSubtitle($report['subtitle']);
                $post->setContent($report['content']);
                $post->setImageUrl($report['image_url']);
                $post->setImage('');

                if (!$post->getId()) {
                    $this->entityManager->persist($post);
                }
                $this->entityManager->flush($post);
            }

        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $driver->close();
        }
    }
}
