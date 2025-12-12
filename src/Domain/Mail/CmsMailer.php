<?php

namespace KikCMS\Domain\Mail;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

readonly class CmsMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private string $defaultEmailFrom,
        private string $companyInfoLine
    ) {}

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
