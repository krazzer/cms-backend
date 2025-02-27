<?php

namespace App\Entity\Mail;

use Exception;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class CmsMailer
{
    /** @var MailerInterface */
    private MailerInterface $mailer;

    /** @var string */
    private string $defaultEmailFrom;

    /**
     * @param MailerInterface $mailer
     * @param string $defaultEmailFrom
     */
    public function __construct(MailerInterface $mailer, string $defaultEmailFrom)
    {
        $this->mailer           = $mailer;
        $this->defaultEmailFrom = $defaultEmailFrom;
    }

    /**
     * @param Email $email
     * @return bool
     */
    public function send(Email $email): bool
    {
        if ( ! $email->getFrom()) {
            $email->from($this->defaultEmailFrom);
        }

        try {
            $this->mailer->send($email);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}