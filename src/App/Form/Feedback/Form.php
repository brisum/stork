<?php

namespace App\Form\Feedback;

use BSMAstuteFormBundle\Form\AbstractForm;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints;
use Twig_Environment;

class Form extends AbstractForm
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
    protected $name = 'feedback';

    /**
     * @var array
     */
    protected $resetFields = [
        'name',
        'email',
        'message',
    ];

    /**
     * Form constructor.
     * @param Session $session
     * @param Twig_Environment $twig
     * @param Swift_Mailer $mailer
     * @param TranslatorInterface $trans
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

        $this->defaultValues['form-name'] = 'app.form.feedback';

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
                    'label' => $this->trans->trans('form.feedback.name', [], 'form'),
                    'label_attr' => [
                        'class' => 'required'
                    ],
                    'required' => false,
                    'constraints' => [
                        new Constraints\NotBlank(['message' => $this->trans->trans('form.error.name.not_blank', [], 'form')])
                    ],
                ]
            )
            ->add(
                'email',
                Type\EmailType::class,
                [
                    'label' => $this->trans->trans('form.feedback.email', [], 'form'),
                    'label_attr' => [
                        'class' => 'required'
                    ],
                    'required' => false,
                    'constraints' => [
                        new Constraints\NotBlank(['message' => $this->trans->trans('form.error.email.not_blank', [], 'form')])
                    ],
                ]
            )
            ->add(
                'message',
                Type\TextareaType::class,
                [
                    'label' => $this->trans->trans('form.feedback.message', [], 'form'),
                    'required' => false,
                    'label_attr' => [
                        'class' => 'required'
                    ],
                    'attr' => [
                        'rows' => 5
                    ],
                    'constraints' => [
                        new Constraints\NotBlank(['message' => $this->trans->trans('form.error.message.not_blank', [], 'form')])
                    ],
                ]
            )
            ->add(
                'submit',
                Type\SubmitType::class,
                [
                    'label' => $this->trans->trans('form.feedback.submit', [], 'form'),
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
            ->setSubject($this->trans->trans('form.feedback.title', [], 'form'))
            ->setBody(
                $this->twig->render(
                    'App:Email:Form/Feedback/feedback.html.twig',
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
                $this->trans->trans('app.form.feedback.message.success', [], 'form')
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

