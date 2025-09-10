<?php
namespace Modules\Core\Services;
use RuntimeException;
/**
 * CryptoService: AES-256-GCM encryption service.
 * Requires ENCRYPTION_KEY (base64) in .env; 32 bytes (256 bits).
 */
class CryptoService {
    private string $key;
    public function __construct() {
        $b64 = getenv('ENCRYPTION_KEY') ?: '';
        if (!$b64) { throw new RuntimeException('ENCRYPTION_KEY not set'); }
        if (str_starts_with($b64, 'base64:')) { $b64 = substr($b64, 7); }
        $key = base64_decode($b64, true);
        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('ENCRYPTION_KEY must be base64(32 bytes)');
        }
        $this->key = $key;
    }
    /** Encrypt plaintext, return base64(json{nonce,tag,ct}) */
    public function encrypt(string $plaintext): string {
        $nonce = random_bytes(12);
        $tag = '';
        $ct = openssl_encrypt($plaintext, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $nonce, $tag);
        if ($ct === false) { throw new RuntimeException('Encryption failed'); }
        $payload = json_encode([
            'nonce' => base64_encode($nonce),
            'tag'   => base64_encode($tag),
            'ct'    => base64_encode($ct),
        ]);
        return base64_encode($payload);
    }
    /** Decrypt from base64(json{nonce,tag,ct}) */
    public function decrypt(string $ciphertextB64): string {
        $json = base64_decode($ciphertextB64, true);
        if ($json === false) { throw new RuntimeException('Invalid ciphertext base64'); }
        $obj = json_decode($json, true);
        if (!is_array($obj) || !isset($obj['nonce'], $obj['tag'], $obj['ct'])) {
            throw new RuntimeException('Invalid ciphertext format');
        }
        $nonce = base64_decode($obj['nonce'], true);
        $tag   = base64_decode($obj['tag'], true);
        $ct    = base64_decode($obj['ct'], true);
        $pt = openssl_decrypt($ct, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $nonce, $tag);
        if ($pt === false) { throw new RuntimeException('Decryption failed'); }
        return $pt;
    }
}
