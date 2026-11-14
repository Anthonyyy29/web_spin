<?php

use App\Models\AdminHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

function permintaan(string $ip, ?string $userAgent): Request
{
    return Request::create('/', 'POST', server: array_filter([
        'REMOTE_ADDR' => $ip,
        'HTTP_USER_AGENT' => $userAgent,
    ], fn ($nilai) => $nilai !== null));
}

test('perangkat dikenali dari user agent', function (?string $userAgent, string $perangkat) {
    AdminHistory::recordLogin(permintaan('10.0.0.1', $userAgent));
    expect(AdminHistory::sole()->device)->toBe($perangkat);
})->with([
    ['Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)', 'Iphone'],
    ['Mozilla/5.0 (Linux; Android 14; Pixel 8) Mobile Safari/537.36', 'Android'],
    ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', 'Desktop'],
    [null, 'Desktop'],
]);

test('ip dan perangkat yang sama memperbarui baris yang ada', function () {
    $this->freezeSecond();
    AdminHistory::recordLogin(permintaan('10.0.0.1', 'iPhone'));
    $this->travel(5)->minutes();
    AdminHistory::recordLogin(permintaan('10.0.0.1', 'iPhone'));

    expect(AdminHistory::count())->toBe(1)
        ->and(AdminHistory::sole()->last_login->equalTo(now()))->toBeTrue();
});

test('ip atau perangkat berbeda membuat baris baru', function () {
    AdminHistory::recordLogin(permintaan('10.0.0.1', 'iPhone'));
    AdminHistory::recordLogin(permintaan('10.0.0.2', 'iPhone'));
    AdminHistory::recordLogin(permintaan('10.0.0.1', 'Android'));

    expect(AdminHistory::count())->toBe(3);
});
