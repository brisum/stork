<?php

namespace Brisum\Stork\Bundle\PageBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


class PageController extends Controller
{
    /**
     * @Route("/", defaults={"name" = "home"}, name="bsm_page_home")
     * @Route("/{name}", defaults={"name" = "home"}, name="bsm_page")
     */
    public function indexAction(Request $request, $name)
    {
//        /** @var EntityManager $em */
//        $em = $this->getDoctrine()->getManager();
//        /** @var Page $entity */
//        $entity = $em->getRepository('StorkPageBundle:Page')->findOneByName($name);
        $templates = $this->getParameter('stork_page.templates');
//
//        if (!$entity || Page::STATUS_PUBLISH != $entity->getStatus()) {
//            throw $this->createNotFoundException();
//        }
//
        $template = 'home'; // $entity->getTemplate();
        if (!array_key_exists($template, $templates)) {
            throw $this->createNotFoundException("Not Found Template \"{$template}\"");
        }

        return $this->render(
            $templates[$template],
            [
                //'entity' => $entity
            ]
        );
    }
}
