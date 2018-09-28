<?php

namespace Brisum\FBCrawler\SonataAdmin;

use App\Core\Form\ImagePreviewType;
use App\Core\Form\StringType;
use Brisum\FBCrawler\Entity\Post;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class PostAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'post';

    /**
     * @var array
     */
    protected $datagridValues = array(
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'publishTime',
    );

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            // ->remove('list')
            ->remove('create')
            // ->remove('batch')
            // ->remove('edit')
            ->remove('delete')
            // ->remove('show')
            // ->remove('export')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('type')
            ->add('title')
            ->add('subtitle')
            ->add('company')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('type')
            ->addIdentifier('title')
            ->addIdentifier('subtitle')
            ->add('company')
            ->add('publishTime')
            ->add('created')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Post $entity */
        $entity = $formMapper->getAdmin()->getSubject();

        $formMapper
            ->add('company', StringType::class)
            ->add('type', StringType::class)
            ->add('title', StringType::class)
            ->add('subtitle', StringType::class)
            ->add('content', StringType::class)
//            ->add('imageUrl', StringType::class)
//            ->add('image', ImagePreviewType::class)
        ;

        if ($entity->getId()) {
            $formMapper
                ->add(
                    'created',
                    StringType::class,
                    [
                        'data' => $entity->getCreated()->format('Y-m-d H:i:s')
                    ]
                )
            ;
        }
    }
}
