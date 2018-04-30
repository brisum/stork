<?php

namespace App\DependencyInjection;

use App\Service\Spelling\Month;
use Symfony\Component\DependencyInjection\Container;
use Twig_Extension;

class TwigExtension extends Twig_Extension
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * TwigExtension constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('parameter', [$this, 'functionParameter']),
        );
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('monthGenitiveCase', [$this, 'filterMonthGenitiveCase']),
        );
    }

    /**
     * @param $name
     * @return mixed
     */
    public function functionParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * @param $month
     * @return string
     */
    public function filterMonthGenitiveCase($month)
    {
        return Month::genitiveCase($month);
    }
}
