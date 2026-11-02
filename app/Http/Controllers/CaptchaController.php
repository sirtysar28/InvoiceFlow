<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CaptchaController extends Controller
{
    /**
     * Karakter yang mudah dibaca (tanpa karakter ambigu: O/0, I/1/l, S/5, B/8, Z/2, G/6).
     */
    private const CHARSET = 'ACDEFHJKLMNPQRTUVWXY34679';

    public function login(): Response
    {
        $code = strtoupper(
            collect(str_split(self::CHARSET))
                ->random(5)
                ->implode('')
        );

        session(['login_captcha' => $code]);

        return response($this->buildSvg($code), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function buildSvg(string $code): string
    {
        $width = 220;
        $height = 64;

        // Warna karakter gelap agar kontras & jelas dibaca
        $charColors = ['#312e81', '#1e1b4b', '#3730a3', '#4338ca'];

        // Noise: garis lembut transparan
        $lines = '';
        for ($i = 0; $i < 3; $i++) {
            $y1 = random_int(8, $height - 8);
            $y2 = random_int(8, $height - 8);
            $lines .= sprintf(
                '<line x1="10" y1="%d" x2="%d" y2="%d" stroke="rgba(99,102,241,0.25)" stroke-width="1.5"/>',
                $y1,
                $width - 10,
                $y2
            );
        }

        // Noise: titik kecil transparan
        $dots = '';
        for ($i = 0; $i < 14; $i++) {
            $dots .= sprintf(
                '<circle cx="%d" cy="%d" r="1.4" fill="rgba(79,70,229,0.22)"/>',
                random_int(8, $width - 8),
                random_int(8, $height - 8)
            );
        }

        // Karakter: posisi merata, rotasi kecil agar tetap sangat jelas
        $chars = '';
        $slot = ($width - 40) / 5;
        foreach (str_split($code) as $i => $char) {
            $x = 20 + ($slot * $i) + ($slot / 2);
            $y = $height / 2 + 12;
            $rotate = random_int(-8, 8);
            $dy = random_int(-3, 3);
            $color = $charColors[array_rand($charColors)];

            $chars .= sprintf(
                '<text x="%.1f" y="%d" fill="%s" font-family="\'Courier New\',monospace" font-size="34" font-weight="800" text-anchor="middle" transform="rotate(%d, %.1f, %d)">%s</text>',
                $x,
                $y + $dy,
                $color,
                $rotate,
                $x,
                $y + $dy,
                htmlspecialchars($char, ENT_XML1)
            );
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}" role="img" aria-label="Kode CAPTCHA">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#eef2ff"/>
      <stop offset="100%" stop-color="#e0e7ff"/>
    </linearGradient>
  </defs>
  <rect width="{$width}" height="{$height}" rx="12" fill="url(#bg)" stroke="#c7d2fe" stroke-width="1.5"/>
  {$lines}
  {$dots}
  {$chars}
</svg>
SVG;
    }
}
