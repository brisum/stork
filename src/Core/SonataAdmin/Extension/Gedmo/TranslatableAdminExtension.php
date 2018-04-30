<?php

namespace App\SonataAdmin\Extension\Gedmo;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\TranslationBundle\Admin\Extension\Gedmo\TranslatableAdminExtension as GedmoTranslatableAdminExtension;

class TranslatableAdminExtension extends GedmoTranslatableAdminExtension
{
    /**
     * Return current translatable locale
     * ie: the locale used to load object translations != current request locale.
     *
     * @return string
     */
    public function getTranslatableLocale(AdminInterface $admin)
    {
        if ($this->translatableLocale == null) {
            if ($admin->getRequest()) {
                $this->translatableLocale = $admin->getRequest()->getLocale();
            }
            if ($this->translatableLocale == null) {
                $this->translatableLocale = $this->getDefaultTranslationLocale($admin);
            }
        }

        return $this->translatableLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters(AdminInterface $admin)
    {
        return [];
    }
}
