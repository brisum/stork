<?php

namespace Brisum\FBCrawler\Selenium;

use Brisum\FBCrawler\Entity\Post;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;

class Page {
    /**
     * @var RemoteWebDriver
     */
    protected $driver;

    /**
     * Page constructor.
     * @param RemoteWebDriver $driver
     */
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
                            ) + 100;
                        $this->driver->executeScript("window.scrollBy(0, {$position})");
                        $class = $content->findElement(WebDriverBy::cssSelector('.uiMorePager'))->getAttribute('class');

                        usleep(500000);

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

    /**
     * @return array
     */
    public function getAdsList()
    {
        $reports = [];
        $reportElements = $this->driver->findElements(WebDriverBy::cssSelector('div[data-report-meta]'));

        foreach ($reportElements as $reportElement) {
            $meta = json_decode($reportElement->getAttribute('data-report-meta'), true);

            try {
                $this->scrollTo($reportElement);
                $articleElement = $reportElement->findElement(WebDriverBy::cssSelector('div[role="article"]'));
                $report = [
                    'type' => $this->getAdsType($articleElement),
                    'data' => []
                ];

                switch ($report['type']) {
                    case Post::TYPE_IMAGE:
                        $report['content'] = $this->getAdsContent($articleElement);
                        $report['data'] = $this->getAdsImageData($articleElement);
                        break;

                    case Post::TYPE_CAROUSEL:
                        $report['content'] = $this->getAdsContent($articleElement);
                        $report['data'] = $this->getAdsCarouselData($articleElement);
                        break;

                    default:
                        $report['content'] = $articleElement
                            ->findElement(WebDriverBy::cssSelector('.userContent p'))
                            ->getText();
                }

                if (Post::TYPE_UNKNOWN == $report['type']) {
                    var_dump($report);
                } else {
                    $reports[] = $report;
                }
            } catch (Exception $e) {
                echo "\nNot parsed {$meta['report_id']}. {$e->getMessage()}\n";
            }
        }

        return array_reverse($reports);
    }

    /**
     * @param RemoteWebElement $element
     * @return null|string
     */
    protected function getAdsType(RemoteWebElement $element)
    {
        $elementDataFt = json_decode($element->getAttribute('data-ft'), true);

        try {
            $list = $element->findElement(WebDriverBy::cssSelector('ul.uiList'));
            return Post::TYPE_CAROUSEL;
        } catch (Exception $e) {

        }

        try {
            $image = $element->findElement(WebDriverBy::cssSelector('.uiScaledImageContainer img'));
            if (isset($elementDataFt['page_insights']) && $image) {
                return Post::TYPE_IMAGE;
            }
        } catch (Exception $e) {

        }

        return Post::TYPE_UNKNOWN;
    }

    /**
     * @param RemoteWebElement $articleElement
     * @return string
     */
    protected function getAdsContent(RemoteWebElement $articleElement)
    {
        $content = [];

        foreach ($articleElement->findElements(WebDriverBy::cssSelector('.userContent p')) as $p) {
            $content[] = $p->getText();
        }

        return implode("\n", $content);
    }

    /**
     * @param RemoteWebElement $articleElement
     * @return array
     */
    protected function getAdsImageData(RemoteWebElement $articleElement)
    {
        $titleElement = $this->findElementOrNull($articleElement, WebDriverBy::cssSelector('.mtm span div a[data-lynx-mode]'));
        $subtitleElement = $titleElement
            ? $this->findElementOrNull($titleElement, WebDriverBy::xpath('parent::div/parent::div/following-sibling::div//a[@data-lynx-mode]'))
            : null;
        $data = [];

        if (!$titleElement || !$titleElement->getText()) {
            $titleElement = $this->findElementOrNull($articleElement, WebDriverBy::cssSelector('.mtm span div div.ellipsis'));
        }
        if (!$subtitleElement || !$subtitleElement->getText()) {
            $subtitleElement = $titleElement
                ? $this->findElementOrNull($titleElement, WebDriverBy::xpath('parent::div/parent::div/following-sibling::div//a[@data-lynx-mode]'))
                : null;
        }

        $data['title'] = $titleElement ? $titleElement->getText() : '';
        $data['subtitle'] =  $subtitleElement ? $subtitleElement->getText() : '';
        $data['image_origin'] = $articleElement
            ->findElement(WebDriverBy::cssSelector('.uiScaledImageContainer img'))
            ->getAttribute('src');

        return $data;
    }

    /**
     * @param RemoteWebElement $element
     * @return array
     */
    protected function getAdsCarouselData(RemoteWebElement $element)
    {
        $data = [
            'items' => []
        ];

        $items = $element->findElements(WebDriverBy::cssSelector('ul.uiList li'));
        $nextArrow = $element->findElement(
            WebDriverBy::xpath("//ul[contains(@class,'uiList')]/following-sibling::div[2]/a[1]")
        );

        foreach ($items as $item) {
            $titleElement= $this->findElementOrNull($item, WebDriverBy::xpath('./div[1]/div/a/div/div/div[2]/div[1]'));
            if (!$titleElement) {
                $titleElement = $this->findElementOrNull($item, WebDriverBy::xpath('./div[1]/div/a/div/div'));
            }
            $subtitleElement = $this->findElementOrNull($item, WebDriverBy::xpath('./div[1]/div/a/div/div/div[2]/div[2]'));

            $data['items'][] = [
                'link' => $item->findElement(WebDriverBy::xpath('./div[1]/div/a'))->getAttribute('href'),
                'image_origin' => $item->findElement(WebDriverBy::cssSelector('img.img'))->getAttribute('src'),
                'title' => $titleElement ? $titleElement->getText() : '',
                'subtitle' => $subtitleElement ? $subtitleElement->getText() : ''
            ];

            try {
                $nextArrow->click();
            } catch (Exception $e) {};

            sleep(1);
        }

        return $data;
    }

    /**
     * @param WebDriverElement $context
     * @param WebDriverBy $by
     * @return WebDriverElement|null
     */
    protected function findElementOrNull(WebDriverElement $context, WebDriverBy $by)
    {
        try {
            return $context->findElement($by);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param WebDriverElement $element
     * @throws Exception
     */
    protected function scrollTo(WebDriverElement $element)
    {
        $id = $element->getAttribute('id');
        $offset = 100;

        if (!$id) {
            throw new Exception("Id of the element is empty");
        }

        $top = $this->driver->executeScript(
            "return document.getElementById('{$id}').getBoundingClientRect().top - {$offset};"
        );
        $this->driver->executeScript("window.scrollBy(0, {$top})");
    }
}
