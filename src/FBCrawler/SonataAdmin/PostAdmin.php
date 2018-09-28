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
        '_sort_by' => 'id',
    );

    /**
     * @var int
     */
    protected $maxPerPage = 50;

    /**
     * @var array
     */
    protected $perPageOptions = [50, 100, 200];

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
            ->add('company')
            ->add('content')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('type')
            ->add('company')
            ->addIdentifier('content')
            ->add('created')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Post $entity */
        $entity = $formMapper->getAdmin()->getSubject();
        $entityData = $entity->getData();

        $formMapper
            ->with('Post')
                ->add('company', StringType::class)
                ->add('type', StringType::class)
                ->add('content', StringType::class)
                ->add(
                    'created',
                    StringType::class,
                    [
                        'data' => $entity->getCreated()->format('Y-m-d H:i:s')
                    ]
                )
            ->end()
        ;

        switch ($entity->getType()) {
            case Post::TYPE_PHOTO:
                $formMapper
                    ->with('Data')
                        ->add(
                            'imageOrigin',
                            StringType::class,
                            [
                                'mapped' => false,
                                'data' => $entityData['image_origin']
                            ]
                        )
                        ->add(
                            'image',
                            ImagePreviewType::class,
                            [
                                'mapped' => false,
                                'data' => $entityData['image']
                            ]
                        )
                        ->add(
                            "title",
                            StringType::class,
                            [
                                'mapped' => false,
                                'data' => $entityData['title']
                            ]
                        )
                        ->add(
                            "subtitle",
                            StringType::class,
                            [
                                'mapped' => false,
                                'data' => $entityData['subtitle']
                            ]
                        )
                    ->end()
                ;
                break;

            case Post::TYPE_CAROUSEL:
                $formMapper->with('Data');
                    foreach ($entityData['items'] as $itemNumber => $item) {
                        $itemNumber += 1; // skip zero number
                        $formMapper
                            ->add(
                                "link_{$itemNumber}",
                                StringType::class,
                                [
                                    'mapped' => false,
                                    'label' => "Link #{$itemNumber}",
                                    'data' => $item['link']
                                ]
                            )
                            ->add(
                                "imageOrigin_{$itemNumber}",
                                StringType::class,
                                [
                                    'mapped' => false,
                                    'label' => "Image Origin #{$itemNumber}",
                                    'data' => $item['image_origin']
                                ]
                            )
                            ->add(
                                "image_{$itemNumber}",
                                ImagePreviewType::class,
                                [
                                    'mapped' => false,
                                    'label' => "Image #{$itemNumber}",
                                    'data' => $item['image']
                                ]
                            )
                            ->add(
                                "title_{$itemNumber}",
                                StringType::class,
                                [
                                    'mapped' => false,
                                    'label' => "Title #{$itemNumber}",
                                    'data' => $item['title']
                                ]
                            )
                            ->add(
                                "subtitle_{$itemNumber}",
                                StringType::class,
                                [
                                    'mapped' => false,
                                    'label' => "Subtitle #{$itemNumber}",
                                    'data' => $item['subtitle']
                                ]
                            )
                        ;
                    }
                $formMapper->end();
                break;
        }
    }
}
