<?php

namespace App\Services;

use App\Exceptions\UnauthorizedException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JwtService
{
    private string $secret;

    private string $algo;

    private int $ttl;

    public function __construct()
    {
        $this->secret = (string) config('laraveladmin.jwt_secret', '');
        $this->algo = (string) config('laraveladmin.jwt_algo', 'HS256');
        $this->ttl = $this->parseExpiry((string) config('laraveladmin.jwt_expires_in', '1h'));

        if (app()->isProduction() && empty($this->secret)) {
            throw new \RuntimeException('JWT_SECRET must be set in production');
        }
    }

    /**
     * Sign a payload and return a JWT string.
     */
    public function encode(array $payload): string
    {
        return JWT::encode($payload, $this->secret, $this->algo);
    }

    /**
     * Decode and verify a JWT string.
     *
     * @throws UnauthorizedException
     */
    public function decode(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->secret, $this->algo));
        } catch (ExpiredException $e) {
            throw new UnauthorizedException('Token has expired', $e);
        } catch (SignatureInvalidException $e) {
            throw new UnauthorizedException('Invalid token signature', $e);
        } catch (BeforeValidException $e) {
            throw new UnauthorizedException('Token not yet valid', $e);
        } catch (\Throwable $e) {
            throw new UnauthorizedException('Invalid token: '.$e->getMessage(), $e);
        }
    }

    /**
     * Create an access token for the given user ID.
     */
    public function makeAccessToken(string $userId): string
    {
        $now = time();

        return $this->encode([
            'sub' => $userId,
            'iat' => $now,
            'exp' => $now + $this->ttl,
        ]);
    }

    /**
     * Blacklist a token by storing its hash in Redis (or DB fallback) until expiry.
     */
    public function blacklist(string $token): void
    {
        $hash = hash('sha256', $token);
        $ttl = $this->getRemainingTtl($token);

        if ($ttl <= 0) {
            // Already expired — nothing to blacklist
            return;
        }

        try {
            Cache::put('jwt_blacklist:'.$hash, true, $ttl);
        } catch (\Throwable $e) {
            Log::warning('JwtService: Redis unavailable for blacklist, falling back to DB', [
                'error' => $e->getMessage(),
            ]);
            $this->dbBlacklist($hash, $ttl);
        }
    }

    /**
     * Check whether a token has been blacklisted.
     */
    public function isBlacklisted(string $token): bool
    {
        $hash = hash('sha256', $token);

        try {
            if (Cache::has('jwt_blacklist:'.$hash)) {
                return true;
            }
        } catch (\Throwable $e) {
            Log::warning('JwtService: Redis unavailable for blacklist check, falling back to DB', [
                'error' => $e->getMessage(),
            ]);
        }

        // DB fallback
        return $this->dbIsBlacklisted($hash);
    }

    /**
     * Decode token and return the subject (user ID).
     *
     * @throws UnauthorizedException
     */
    public function getUserId(string $token): string
    {
        $payload = $this->decode($token);

        if (empty($payload->sub)) {
            throw new UnauthorizedException('Token is missing subject claim');
        }

        return (string) $payload->sub;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function parseExpiry(string $expires): int
    {
        if (is_numeric($expires)) {
            return (int) $expires;
        }
        $unit = strtolower(substr($expires, -1));
        $value = (int) substr($expires, 0, -1);

        return match ($unit) {
            'd' => $value * 86400,
            'h' => $value * 3600,
            'm' => $value * 60,
            's' => $value,
            default => 3600,
        };
    }

    private function getRemainingTtl(string $token): int
    {
        try {
            $payload = $this->decode($token);
            $exp = $payload->exp ?? 0;

            return max(0, (int) $exp - time());
        } catch (\Throwable) {
            return 0;
        }
    }

    private function dbBlacklist(string $hash, int $ttl): void
    {
        try {
            DB::table('jwt_blacklist')->insertOrIgnore([
                'token_hash' => $hash,
                'expires_at' => now()->addSeconds($ttl),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('JwtService: DB blacklist insert failed', ['error' => $e->getMessage()]);
        }
    }

    private function dbIsBlacklisted(string $hash): bool
    {
        try {
            return DB::table('jwt_blacklist')
                ->where('token_hash', $hash)
                ->where('expires_at', '>', now())
                ->exists();
        } catch (\Throwable $e) {
            Log::error('JwtService: DB blacklist check failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
