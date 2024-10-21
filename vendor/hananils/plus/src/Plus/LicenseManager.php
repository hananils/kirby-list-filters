<?php

namespace Hananils\Plus;

use DateTime;
use DateTimeZone;
use Exception;
use Kirby\Cms\App as Kirby;

class LicenseManager
{
    const MISSING = 0;
    const VALID = 1;
    const INVALID = 10;
    const INVALID_VERSION = 11;
    const INVALID_DOMAIN = 12;
    const INVALID_TYPE = 13;

    private Kirby $kirby;
    private string $id;
    private string $name;
    private string $key = '';
    private null|string $date = null;
    private array $license = [];
    private string $message = '';
    private int $code = 1;

    private string $locale = 'en';
    private array $translations = [
        'en' => [
            'license' => 'Plus License',
            'check' =>
                'Please check your license at https://kirby.hananils.de.',
            'missing' => 'The license file could not be found.',
            'invalid' => 'The license file is not valid.',
            'invalid.version' =>
                'The license is not valid for this plugin version.',
            'invalid.domain' => 'The license is not valid for this domain.',
            'invalid.type' => 'The license is not valid for this plugin type.'
        ],
        'de' => [
            'license' => 'Plus-Lizenz',
            'check' =>
                'Bitte überprüfe deine Lizenz unter https://kirby.hananils.de.',
            'missing' => 'Die Lizenzdatei konnte nicht gefunden werden.',
            'invalid' => 'Die Lizenzdatei ist nicht gültig.',
            'invalid.version' =>
                'Die Lizenz ist nicht für diese Pluginversion gültig.',
            'invalid.domain' => 'Die Lizenz ist nicht für diese Domain gültig.',
            'invalid.type' =>
                'Die Lizenz ist nicht für diesen Plugin-Typ gültig.'
        ]
    ];

    public function __construct(
        string $id,
        string $name,
        null|string $locale = null,
        null|string $root = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->kirby = kirby();

        if ($locale === null) {
            $user = $this->kirby->user();
            $locale = $user ? $user->language() : 'en';
        }

        $this->locale = $locale;

        if ($root === null) {
            $plugin = $this->kirby
                ->system()
                ->plugins()
                ->find($this->id);
            $root = $plugin->root();
        }

        if ($composer = file_get_contents($root . '/composer.json')) {
            if ($info = json_decode($composer, true)) {
                if (isset($info['time'])) {
                    $this->date = $info['time'];
                }
            }
        }
    }

    private function translate($key)
    {
        return $this->translations[$this->locale][$key];
    }

    public function validate()
    {
        try {
            $this->validateFile();
            $this->validateVersion();
            $this->validateType();
            $this->validateChecksum();
        } catch (Exception $e) {
            $this->message = $e->getMessage() . ' ' . $this->translate('check');
            $this->code = $e->getCode();
        }

        return $this->message === '';
    }

    public function validateFile(): bool
    {
        $license = $this->getLicense();

        if (
            count($license) !== 4 &&
            array_key_exists('activation', $license) &&
            array_key_exists('code', $license) &&
            array_key_exists('domain', $license) &&
            array_key_exists('checksum', $license)
        ) {
            throw new Exception(
                $this->translate('invalid'),
                code: self::INVALID
            );
        }

        return true;
    }

    public function validateVersion(): bool
    {
        $license = $this->getLicense();

        if (!$this->date) {
            throw new Exception(
                $this->translate('invalid.version'),
                code: self::INVALID_VERSION
            );
        }

        $utc = new DateTimeZone('UTC');
        $releaseDate = new DateTime($this->date, $utc);
        $activationDate = new DateTime($license['activation'], $utc);
        $expirationDate = $activationDate->modify('+1 year');

        if ($expirationDate > $releaseDate) {
            throw new Exception(
                $this->translate('invalid.version'),
                code: self::INVALID_VERSION
            );
        }

        return true;
    }

    public function validateDomain(): bool
    {
        $license = $this->getLicense();
        $domain = parse_url($this->kirby->url(), PHP_URL_HOST);

        if ($license['domain'] !== $domain) {
            throw new Exception(
                $this->translate('invalid.domain'),
                code: self::INVALID_DOMAIN
            );
        }

        return true;
    }

    public function validateType(): bool
    {
        $code = $this->parseCode();

        if ($code[0] !== 'HN' || $code[1] !== $this->key()) {
            throw new Exception(
                $this->translate('invalid.type'),
                code: self::INVALID_TYPE
            );
        }

        return true;
    }

    public function validateChecksum(): bool
    {
        $license = $this->getLicense();
        $hash = $this->generateHash();

        if ($hash !== $license['checksum']) {
            throw new Exception(
                $this->translate('invalid'),
                code: self::INVALID
            );
        }

        return true;
    }

    public function isExpired()
    {
        $license = $this->getLicense(silent: true);

        if ($license === false) {
            return false;
        }

        $utc = new DateTimeZone('UTC');
        $activationDate = new DateTime($license['activation'], $utc);
        $expirationDate = $activationDate->modify('+1 year');
        $currentDate = new DateTime('now', $utc);

        return $expirationDate < $currentDate;
    }

    public function isValid()
    {
        $this->validate();

        return $this->code === 1;
    }

    public function getLicense($silent = false): array|false
    {
        if ($this->license !== []) {
            return $this->license;
        }

        $file = $this->kirby->root('license') . '/' . $this->id . '.license';

        if (file_exists($file)) {
            $this->license = json_decode(file_get_contents($file), true);

            return $this->license;
        } elseif ($silent === false) {
            throw new Exception(
                $this->translate('missing'),
                code: self::MISSING
            );
        }

        return false;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function toResponse(): array
    {
        return [
            'valid' => $this->validate(),
            'debug' => $this->kirby->option('debug', false),
            'message' => $this->name . ': ' . $this->getMessage()
        ];
    }

    public function toInfo(): array
    {
        $name = 'hana+nils · ' . $this->translate('license');
        $status = 'active';

        if (!$this->isValid()) {
            [$error, $label] = explode('. ', $this->message);
            $name .= ': ' . $error;
            $status = [
                'value' => 'missing',
                'label' => $label,
                'theme' => 'negative',
                'icon' => 'alert'
            ];
        } elseif ($this->isExpired()) {
            $status = 'legacy';
        }

        $info = [
            'name' => $name,
            'link' => 'https://kirby.hananils.de',
            'status' => $status
        ];

        return $info;
    }

    public function key(): string
    {
        if ($this->key === '') {
            $this->generateKey();
        }

        return $this->key;
    }

    private function generateKey(): self
    {
        $key = strtoupper($this->name);
        $key = preg_replace('/[^BCDFGHJKLMNPQRSTVWYXZ]/', '', $key);

        if (preg_match('/^[AEIOU]/', $this->name)) {
            $key = substr($this->name, 0, 1) . $key;
        }

        $this->key = substr($key, 0, 4);

        return $this;
    }

    private function generateHash(): string
    {
        $license = $this->getLicense();
        $utc = new DateTimeZone('UTC');

        $activation = new DateTime($license['activation'], $utc);
        $code = $license['code'];
        $domain = $license['domain'];

        return md5($activation->getTimestamp() . $code . $domain);
    }

    private function parseCode(): array
    {
        $license = $this->getLicense();

        return explode('-', $license['code']);
    }
}
