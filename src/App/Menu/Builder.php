<?php

namespace App\Menu;

use StorkPageBundle\Entity\Page;
use BSMServiceBundle\Entity\Service;
use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager();
        /** @var Translator $translator */
        $translator = $this->container->get('translator');
        $menu = $factory->createItem('root');

        /** @var Page $pageHome */
        $pageHome = $em->getRepository('StorkPageBundle:Page')->findOneBy(['name' => 'home']);
        if ($pageHome) {
            $menu->addChild(
                'home',
                [
                    'label' => $pageHome->getTitle(),
                    'route' => 'bsm_page_home'
                ]
            );
        }

        $menu->addChild(
            'service',
            [
                'label' => $translator->trans('navbar.menu.service'),
                'route' => 'bsm_service_list'
            ]
        );
        /** @var Service[] $services */
        $services = $em->getRepository('BSMServiceBundle:Service')->findAll();
        foreach ($services as $service) {
            $menu['service']->addChild(
                'service-' . $service->getName(),
                [
                    'label' => $service->getTitle(),
                    'route' => 'bsm_service',
                    'routeParameters' => ['name' => $service->getName()]
                ]
            );
        }

        $menu->addChild(
            'team',
            [
                'label' => $translator->trans('navbar.menu.team'),
                'route' => 'bsm_employee_list'
            ]
        );

        $menu->addChild(
            'blog',
            [
                'label' => $translator->trans('navbar.menu.blog'),
                'route' => 'bsm_blog_post_list'
            ]
        );

        /** @var Page $pageContacts */
        $pageContacts = $em->getRepository('StorkPageBundle:Page')->findOneBy(['name' => 'contacts']);
        if ($pageContacts) {
            $menu->addChild(
                'contacts',
                [
                    'label' => $pageContacts->getTitle(),
                    'route' => 'bsm_page',
                    'routeParameters' => ['name' => $pageContacts->getName()]
                ]
            );
        }

        return $menu;
    }
}