<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random 160-bit Base32 secret for RFC 6238 TOTP authenticator apps.
     */
    public function generateSecret(int $length = 32): string
    {
        $bytes = random_bytes(20);
        $bits = '';
        foreach (str_split($bytes) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $secret = '';
        foreach (str_split($bits, 5) as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $secret .= self::BASE32_ALPHABET[bindec($chunk)];
        }

        return substr($secret, 0, $length);
    }

    public function getProvisioningUri(User $user, string $secret): string
    {
        $issuer = rawurlencode('Joygivers Raag-E-Ishq');
        $account = rawurlencode($user->email);
        return "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&digits=6&period=30";
    }

    public function renderProvisioningQrSvg(User $user, string $secret, int $size = 180): string
    {
        $uri = $this->getProvisioningUri($user, $secret);
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString($uri);
    }

    public function generateCode(string $secret, ?int $timestamp = null): string
    {
        $timeSlice = (int) floor(($timestamp ?? time()) / 30);
        $binarySecret = $this->base32Decode($secret);
        $timeBytes = pack('N*', 0, $timeSlice);
        $hash = hash_hmac('sha1', $timeBytes, $binarySecret, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncated = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        return str_pad((string) ($truncated % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public function verifyCode(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        $normalizedCode = preg_replace('/\s+/', '', trim($code));
        if (!preg_match('/^\d{6}$/', $normalizedCode)) {
            return false;
        }

        $now = $timestamp ?? time();
        for ($offset = -$window; $offset <= $window; $offset++) {
            $candidate = $this->generateCode($secret, $now + ($offset * 30));
            if (hash_equals($candidate, $normalizedCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
        }

        return $codes;
    }

    public function consumeRecoveryCode(User $user, string $input): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $normalized = strtoupper(trim($input));

        $idx = array_search($normalized, $codes, true);
        if ($idx === false) {
            return false;
        }

        unset($codes[$idx]);
        $user->update(['two_factor_recovery_codes' => array_values($codes)]);

        return true;
    }

    protected function base32Decode(string $secret): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret));
        $bits = '';
        foreach (str_split($clean) as $char) {
            $pos = strpos(self::BASE32_ALPHABET, $char);
            if ($pos !== false) {
                $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
            }
        }

        $binary = '';
        foreach (str_split($bits, 8) as $byteBits) {
            if (strlen($byteBits) === 8) {
                $binary .= chr(bindec($byteBits));
            }
        }

        return $binary;
    }
}
