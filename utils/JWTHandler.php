<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../utils/loadenv.php';
loadEnv();

class JWTHandler
{
    private static $secret_key;
    private static $algorithm = 'HS256';

    // Automatically load the secret if not already set
    private static function ensureInitialized()
    {
        if (!self::$secret_key) {
            self::$secret_key = getenv('JWT_SECRET');
        }
    }

    public static function generateToken($user_id, $email)
    {
        self::ensureInitialized();

        $issued_at = time();
        $expiration = $issued_at + (60 * 60); // Token expires in 1 hour

        $payload = array(
            "user_id" => $user_id,
            "email" => $email,
            "iat" => $issued_at,
            "exp" => $expiration
        );

        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    public static function validateToken($token)
    {
        self::ensureInitialized();

        try {
            $decoded = JWT::decode($token, new Key(self::$secret_key, self::$algorithm));
            return $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
}
