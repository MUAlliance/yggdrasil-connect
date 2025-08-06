<?php

namespace LittleSkin\YggdrasilConnect\Utils;

class RSAPublicUtil {
    use Traits\RSAPublicTrait;

    public function __construct(string $publicKey) {
        $this->publicKey = $publicKey;
    }
}