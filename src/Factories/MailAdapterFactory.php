<?php

declare(strict_types=1);

namespace Cornatul\Marketing\Base\Factories;

use InvalidArgumentException;
use Cornatul\Marketing\Base\Adapters\MailgunMailAdapter;
use Cornatul\Marketing\Base\Adapters\MailjetAdapter;
use Cornatul\Marketing\Base\Adapters\PostmarkMailAdapter;
use Cornatul\Marketing\Base\Adapters\SendgridMailAdapter;
use Cornatul\Marketing\Base\Adapters\SesMailAdapter;
use Cornatul\Marketing\Base\Adapters\SmtpAdapter;
use Cornatul\Marketing\Base\Adapters\PostalAdapter;
use Cornatul\Marketing\Base\Interfaces\MailAdapterInterface;
use Cornatul\Marketing\Base\Models\EmailService;
use Cornatul\Marketing\Base\Models\EmailServiceType;

class MailAdapterFactory
{
    /** @var array */
    public static $adapterMap = [
        EmailServiceType::SES => SesMailAdapter::class,
        EmailServiceType::SENDGRID => SendgridMailAdapter::class,
        EmailServiceType::MAILGUN => MailgunMailAdapter::class,
        EmailServiceType::POSTMARK => PostmarkMailAdapter::class,
        EmailServiceType::MAILJET => MailjetAdapter::class,
        EmailServiceType::SMTP => SmtpAdapter::class,
        EmailServiceType::POSTAL => PostalAdapter::class,
    ];

    /**
     * Cache of resolved mail adapters.
     *
     * @var array
     */
    private array $adapters = [];

    /**
     * Get a mail adapter instance.
     */
    public function adapter(EmailService $emailService): MailAdapterInterface
    {
        return $this->adapters[$emailService->id] ?? $this->cache($this->resolve($emailService), $emailService);
    }

    /**
     * Cache a resolved adapter for the given provider.
     */
    private function cache(MailAdapterInterface $adapter, EmailService $emailService): MailAdapterInterface
    {
        return $this->adapters[$emailService->id] = $adapter;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function resolve(EmailService $emailService): MailAdapterInterface
    {
        if (!$emailServiceType = EmailServiceType::resolve($emailService->type_id)) {
            throw new InvalidArgumentException("Unable to resolve mail provider type from ID [$emailService->type_id].");
        }

        $adapterClass = self::$adapterMap[$emailService->type_id] ?? null;

        if (!$adapterClass) {
            throw new InvalidArgumentException("Mail adapter type [{$emailServiceType}] is not supported.");
        }

        return new $adapterClass($emailService->settings);
    }
}
