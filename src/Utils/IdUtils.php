<?php

namespace Olz\Utils;

class IdUtils {
    use WithUtilsTrait;

    protected string $base64Iv = '9V0IXtcQo5o=';
    protected string $algo = 'des-ede-cbc'; // Find one using `composer get_id_algos`

    public function toExternalId(int|string $internal_id, string $type = ''): string {
        $serialized_id = $this->serializeId($internal_id, $type);
        return $this->encryptId($serialized_id);
    }

    protected function serializeId(int|string $internal_id, string $type): string {
        $int_internal_id = intval($internal_id);
        if (strval($int_internal_id) !== strval($internal_id)) {
            throw new \Exception("Internal ID must be int");
        }
        if ($int_internal_id < 0) {
            throw new \Exception("Internal ID must be positive");
        }
        $type_hash_hex = str_pad(dechex($this->crc16($type)), 4, '0', STR_PAD_LEFT);
        $id_hex = str_pad(dechex($int_internal_id), 10, '0', STR_PAD_LEFT);
        if (strlen($id_hex) > 10) {
            throw new \Exception("Internal ID must be at most 40 bits");
        }
        return hex2bin($type_hash_hex.$id_hex);
    }

    protected function encryptId(string $serialized_id): string {
        $plaintext = $serialized_id;
        $key = $this->envUtils()->getIdEncryptionKey();
        $iv = base64_decode($this->base64Iv);
        $ciphertext = @openssl_encrypt($plaintext, $this->algo, $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($ciphertext === false) {
            throw new \Exception(openssl_error_string());
        }
        return $this->generalUtils()->base64EncodeUrl($ciphertext);
    }

    public function toInternalId(string $external_id, string $type = ''): int {
        $serialized_id = $this->decryptId($external_id);
        return $this->deserializeId($serialized_id, $type);
    }

    protected function decryptId(string $encrypted_id): string {
        $ciphertext = $this->generalUtils()->base64DecodeUrl($encrypted_id);
        $key = $this->envUtils()->getIdEncryptionKey();
        $iv = base64_decode($this->base64Iv);
        return openssl_decrypt($ciphertext, $this->algo, $key, OPENSSL_RAW_DATA, $iv);
    }

    protected function deserializeId(string $serialized_id, string $type): int {
        $type_hash_hex = str_pad(dechex($this->crc16($type)), 4, '0', STR_PAD_LEFT);
        $serialized_id_hex = bin2hex($serialized_id);
        if (substr($serialized_id_hex, 0, 4) !== $type_hash_hex) {
            throw new \Exception("Invalid serialized ID: Type mismatch");
        }
        return hexdec(substr($serialized_id_hex, 4));
    }

    protected function crc16(string $data): int {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }
        return $crc;
    }

    public static function fromEnv(): self {
        return new self();
    }
}
