<?php

namespace App\Entity\Mail;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class CmsMailer
{
    /** @var MailerInterface */
    private MailerInterface $mailer;

    /** @var string */
    private string $defaultEmailFrom;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /** @var string */
    private string $companyInfoLine;

    /**
     * @param MailerInterface $mailer
     * @param LoggerInterface $logger
     * @param string $defaultEmailFrom
     * @param string $companyInfoLine
     */
    public function __construct(MailerInterface $mailer, LoggerInterface $logger, string $defaultEmailFrom, string $companyInfoLine)
    {
        $this->mailer           = $mailer;
        $this->defaultEmailFrom = $defaultEmailFrom;
        $this->logger           = $logger;
        $this->companyInfoLine  = $companyInfoLine;
    }

    /**
     * @param TemplatedEmail $email
     * @return bool
     * @throws TransportExceptionInterface
     */
    public function send(TemplatedEmail $email): bool
    {
        if ( ! $email->getFrom()) {
            $email->from($this->defaultEmailFrom);
        }

        if ( ! $email->getHtmlTemplate()) {
            $email->htmlTemplate('email/default.twig');
        }

        $context = $email->getContext();

        if ( ! isset($context['company'])) {
            $context['company'] = $this->companyInfoLine;
            $email->context($context);
        }

        try {
            $this->mailer->send($email);
            return true;
        } catch (Exception $e) {
            $this->logger->error('Error sending email: ' . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
}