<?php

namespace BSMUserBundle\SonataAdmin;

use FOS\UserBundle\Model\UserManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class User extends AbstractAdmin
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var array
     */
    protected $roles = [
        'ROLE_ADMIN' => 'admin'
    ];

    public function setUserManager(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @return UserManagerInterface
     */
    public function getUserManager()
    {
        return $this->userManager;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $isNew = empty($this->getSubject()->getUsername());

        $formMapper
            ->add('username', 'text', ['attr' => ['readonly' => !$isNew]])
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('plainPassword', 'text', ['required' => false])
            ->add(
                'groups',
                EntityType::class,
                [
                    'multiple' => true,
                    'class' => 'BSMUserBundle:Group',
                    'choice_label' => 'name',
                ]
            )
            ->add('roles', 'choice', ['multiple' => true, 'choices' => $this->roles])
            ->add('enabled', null, array('required' => false))
        ;
    }

    public function preUpdate($entity)
    {
        $this->getUserManager()->updateCanonicalFields($entity);
        $this->getUserManager()->updatePassword($entity);
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('username')
            ->add('email')
            ->add('enabled')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('username')
            ->add('email')
            ->add('enabled')
            ->add('lastLogin')

        ;
    }
}