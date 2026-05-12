<?php
declare(strict_types=1);

namespace App\Services;

class BrandingService
{
    private const DEFAULT_PREFIX = 'KTR';

    public function generateBookingCode(): string
    {
        $date = date('Ymd');
        $suffix = strtoupper(bin2hex(random_bytes(2)));

        return $this->getBookingCodePrefix() . '-' . $date . '-' . $suffix;
    }

    public function getBookingCodePrefix(): string
    {
        $homeContent = (new HomeContentService())->getHomePageContent();
        $configured = $this->normalizePrefix((string) ($homeContent['booking_code_prefix'] ?? ''));
        if ($configured !== null) {
            return $configured;
        }

        $suggested = $this->suggestPrefixFromHost((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($suggested !== null) {
            return $suggested;
        }

        return self::DEFAULT_PREFIX;
    }

    public function suggestPrefixFromHost(string $host): ?string
    {
        $host = strtolower(trim($host));
        $host = preg_replace('/:\d+$/', '', $host) ?? $host;
        $host = preg_replace('/^www\./', '', $host) ?? $host;

        if ($host === '') {
            return null;
        }

        $tokens = preg_split('/[^a-z0-9]+/', $host) ?: [];
        $letters = '';

        foreach ($tokens as $token) {
            if ($token === '' || ctype_digit($token)) {
                continue;
            }
            $letters .= strtoupper($token[0]);
            if (strlen($letters) >= 3) {
                break;
            }
        }

        if (strlen($letters) >= 3) {
            return substr($letters, 0, 3);
        }

        $flattened = preg_replace('/[^a-z]/', '', $host) ?? '';
        if (strlen($flattened) >= 3) {
            return strtoupper(substr($flattened, 0, 3));
        }

        return null;
    }

    private function normalizePrefix(string $prefix): ?string
    {
        $prefix = strtoupper(trim($prefix));
        if ($prefix === '') {
            return null;
        }

        if (preg_match('/^[A-Z]{3}$/', $prefix) !== 1) {
            return null;
        }

        return $prefix;
    }
}
