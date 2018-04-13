<?php

namespace App\Form\ContactWidget;

use BSMAstuteFormBundle\Form\AbstractForm;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints;
use Twig_Environment;

class Callback extends AbstractForm
{
    /** @var Swift_Mailer  */
    protected $mailer;

    /**
     * @var TranslatorInterface
     */
    protected $trans;

    /**
     * @var string
     */
    protected $emailFrom;

    /**
     * @var string
     */
    protected $emailFromName;

    /**
     * @var array
     */
    protected $emailNotification;

    /**
     * @var string
     */
    protected $name = 'contact_widget_callback';

    /**
     * @var array
     */
    protected $resetFields = [
        'name',
        'phone',
        'message',
    ];

    /**
     * Form constructor.
     * @param Twig_Environment $twig
     * @param Swift_Mailer $mailer
     * @param TranslatorInterface $trans
     * @param Session $session
     * @param string $url
     * @param string $emailFrom
     * @param string $emailFromName
     * @param array $emailNotification
     */
    public function __construct(
        Session $session,
        Twig_Environment $twig,
        Swift_Mailer $mailer,
        TranslatorInterface $trans,
        $url,
        $emailFrom,
        $emailFromName,
        array $emailNotification
    ) {
        $this->mailer = $mailer;
        $this->trans = $trans;
        $this->emailFrom = $emailFrom;
        $this->emailFromName = $emailFromName;
        $this->emailNotification = $emailNotification;

        $this->options['translation_domain'] = 'form';
        $this->options['attr']['data-url'] = $url;
        $this->options['attr']['data-message-error'] = $this->trans->trans('form.message-error', [], 'form');
        $this->options['attr']['data-success'] = 'windowResize';
        $this->options['attr']['data-error'] = 'windowResize';

        $this->defaultValues['form-name'] = 'app.contact_widget.form.callback';

        parent::__construct($session, $twig);
    }

    /**
     * @return mixed
     */
    protected function buildForm()
    {
        $this->form = $this->getFormBuilder()
            ->add(
                'form-name',
                Type\HiddenType::class
            )
            ->add(
                'name',
                Type\TextType::class,
                [
                    'label' => false,
                    'label_attr' => [
                        'class' => 'required'
                    ],
                    'attr' => ['placeholder' => $this->trans->trans('contact_widget.form.callback.name', [], 'contact_widget')],
                    'required' => false,
                    'constraints' => [
                        new Constraints\NotBlank(['message' => $this->trans->trans('form.error.name.not_blank', [], 'form')])
                    ],
                ]
            )
            ->add(
                'phone',
                Type\TextType::class,
                [
                    'label' => false,
                    'label_attr' => [
                        'class' => 'required'
                    ],
                    'attr' => ['placeholder' => $this->trans->trans('contact_widget.form.callback.phone', [], 'contact_widget')],
                    'required' => false,
                    'constraints' => [
                        new Constraints\NotBlank(['message' => $this->trans->trans('form.error.phone.not_blank', [], 'form')])
                    ],
                ]
            )
            ->add(
                'message',
                Type\TextareaType::class,
                [
                    'required' => false,
                    'label' => false,
                    'label_attr' => [
                        'class' => 'required'
                    ],
                    'attr' => [
                        'placeholder' => $this->trans->trans('contact_widget.form.callback.message', [], 'contact_widget'),
                        'rows' => 5,
                    ]
                ]
            )
            ->add(
                'submit',
                Type\SubmitType::class,
                [
                    'label' => $this->trans->trans('contact_widget.form.callback.submit', [], 'contact_widget'),
                ]
            )
            ->getForm();
        ;
    }

    /**
     * @return boolean
     */
    protected function processSuccess()
    {

        $message = Swift_Message::newInstance()
            ->setSubject($this->trans->trans('contact_widget.callback.title', [], 'contact_widget'))
            ->setBody(
                $this->twig->render(
                    'App:Email:Form/ContactWidget/callback.html.twig',
                    $this->form->getData()
                )
            )
            ->setContentType('text/html');
        $sendCounter = 0;

        foreach ($this->emailNotification as $emailNotification) {
            $message->setFrom([$this->emailFrom => $this->emailFromName]);
            $message->setTo($emailNotification);
            $sendCounter += $this->mailer->send($message);
        }

        if ($sendCounter) {
            $this->status = self::STATUS_SUCCESS;
            $this->addMessage(
                self::MESSAGE_TYPE_SUCCESS,
                $this->trans->trans('contact_widget.form.callback.message.success', [], 'contact_widget')
            );
            return true;
        } else {
            $this->status = self::STATUS_ERROR;
            $this->addMessage(
                self::MESSAGE_TYPE_ERROR,
                $this->trans->trans('form.message-error', [], 'form')
            );
            return false;
        }
    }

    /**
     * @return void
     */
    protected function processFail()
    {
        // TODO: Implement processFail() method.
    }
}
