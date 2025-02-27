<?php

namespace App\Entity\Mail;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class CmsMailer
{
    /** @var MailerInterface */
    private MailerInterface       $mailer;

    /** @var ParameterBagInterface */
    private ParameterBagInterface $params;

    /**
     * @param MailerInterface $mailer
     * @param ParameterBagInterface $params
     */
    public function __construct(MailerInterface $mailer, ParameterBagInterface $params)
    {
        $this->mailer = $mailer;
        $this->params = $params;
    }

    /**
     * @param Email $email
     * @return bool
     */
    public function send(Email $email): bool
    {
        if( ! $email->getFrom()){
            $email->from($this->params->get('app.default_email_from'));
        }

        try{
            $this->mailer->send($email);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}