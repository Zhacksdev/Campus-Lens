<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $secret = config('services.jwt.secret');
        $parts = $token ? explode('.', $token) : [];

        if (! $secret || count($parts) !== 3) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        [$headerPart, $payloadPart, $signature] = $parts;
        $header = $this->decode($headerPart);
        $payload = $this->decode($payloadPart);
        $expected = $this->encode(hash_hmac('sha256', "$headerPart.$payloadPart", $secret, true));
        $studentId = $payload['student_id'] ?? $payload['sub'] ?? null;

        if (($header['alg'] ?? null) !== 'HS256'
            || ! hash_equals($expected, $signature)
            || (isset($payload['exp']) && (int) $payload['exp'] < time())
            || ! $studentId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->attributes->set('jwt', $payload);
        $request->attributes->set('student_id', (string) $studentId);

        return $next($request);
    }

    private function decode(string $value): array
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return is_string($decoded) ? (json_decode($decoded, true) ?: []) : [];
    }

    private function encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
