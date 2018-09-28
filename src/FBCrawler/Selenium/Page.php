<?php

namespace Brisum\FBCrawler\Selenium;

use Brisum\FBCrawler\Entity\Post;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

class Page {
    /**
     * @var RemoteWebDriver
     */
    protected $driver;

    public function __construct(RemoteWebDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param $url
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function open($url)
    {
        $this->driver->get($url);
        $this->waitForPageReady();
    }

    /**
     * @param $email
     * @param $password
     * @return $this
     * @throws Exception
     */
    public function login($email, $password)
    {
        $this->open('https://www.facebook.com/');
        $this->waitForPageReady();
        sleep(1);

        $loginForm = $this->driver->findElement(WebDriverBy::id('login_form'));
        if (!$loginForm->isDisplayed()) {
            throw new Exception('Login form is absent');
        }

        $loginForm->findElement(WebDriverBy::id('email'))->click()->sendKeys($email);
        $loginForm->findElement(WebDriverBy::id('pass'))->click()->sendKeys($password);
        $loginForm->findElement(WebDriverBy::cssSelector('input[type="submit"]'))->click();
        sleep(1);

        return $this;
    }

    /**
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function waitForPageReady()
    {
        $driver = $this->driver;

        $driver->wait(60, 500)->until(
            function () use ($driver) {
                $result = $driver->executeScript("return document.readyState === 'complete'");

                return $result;
            },
            'Page has not been loaded.'
        );
    }

    public function scrollToBottom()
    {
        $driver = $this->driver;
        $content = $this->driver->findElement(WebDriverBy::cssSelector('#content_container'));

        do {
            $morePager = null;

            try {
                $driver->wait(600, 500)->until(
                    function () use ($content) {
                        $position = $this->driver->executeScript(
                                "return (window.pageYOffset || document.documentElement.scrollTop)  - (document.documentElement.clientTop || 0)"
                            ) + 500;
                        $this->driver->executeScript("window.scrollBy(0, {$position})");
                        $class = $content->findElement(WebDriverBy::cssSelector('.uiMorePager'))->getAttribute('class');

                        echo "{$class}\n";

                        return false !== strpos($class, 'async_saving');
                    },
                    '"async_saving" has not been shown.'
                );
                $driver->wait(10, 100)->until(
                    function () use ($content) {
                        $class = $content->findElement(WebDriverBy::cssSelector('.uiMorePager'))->getAttribute('class');
                        echo "{$class}\n";
                        return false === strpos($class, 'async_saving');
                    },
                    '"async_saving" has not been hidden.'
                );

                $morePager = $content->findElement(WebDriverBy::cssSelector('.uiMorePager'));
                sleep(1);
            } catch (Exception $e) {
                $morePager = null;
                break;
            }
        } while ($morePager);
    }

    public function getReports()
    {
        $reports = [];
        $reportElements = $this->driver->findElements(WebDriverBy::cssSelector('div[data-report-meta]'));

        foreach ($reportElements as $reportElement) {
            $meta = json_decode($reportElement->getAttribute('data-report-meta'), true);

            try {
                $articleElement = $reportElement->findElement(WebDriverBy::cssSelector('div[role="article"]'));
                $articleMeta = json_decode($articleElement->getAttribute('data-ft'), true);
                $titleElement = $articleElement->findElement(WebDriverBy::cssSelector('a[data-lynx-mode]'));
                $report = [
                    'report_id' => $articleMeta['page_insights'][$articleMeta['page_id']]['post_context']['story_fbid'],
                    'type' => Post::TYPE_UNKNOWN,
                    'publish_time' => $articleMeta['page_insights'][$articleMeta['page_id']]['post_context']['publish_time'],
                    'title' => $titleElement->isDisplayed() ? $titleElement->getText() : 'no title',
                    'subtitle' => $titleElement->isDisplayed()
                        ? $titleElement->findElement(WebDriverBy::xpath('parent::div//following-sibling::div'))->getText()
                        : 'no subtitle',
                    'content' => $articleElement->findElement(WebDriverBy::cssSelector('.userContent p'))
                        ->getText(),
                    'data' => []
                ];

                switch ($this->getType($articleElement)) {
                    case Post::TYPE_PHOTO:
                        $report['type'] = Post::TYPE_PHOTO;
                        $report['data']['image_origin'] = $articleElement->findElement(WebDriverBy::cssSelector('.uiScaledImageContainer img'))
                        ->getAttribute('src');
                        break;


                    default:
                        $report['type'] = Post::TYPE_UNKNOWN;
                }

                $reports[] = $report;
            } catch (Exception $e) {
                echo "\nNot parsed {$meta['report_id']}. {$e->getMessage()}\n";
            }
        }

        return array_reverse($reports);
    }

    protected function getType(RemoteWebElement $element)
    {
        try {
            $image = $element->findElement(WebDriverBy::cssSelector('.uiScaledImageContainer img'));
            if ($image) {
                return Post::TYPE_PHOTO;
            }
        } catch (Exception $e) {

        }

        return Post::TYPE_UNKNOWN;
    }
}
