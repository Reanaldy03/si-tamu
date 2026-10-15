<?php

namespace App\Infrastructure\Printing;

use Illuminate\Support\Facades\Storage;

/**
 * The institution's official letterhead ("kop surat").
 *
 * The printed forms of Sekretariat DPRD Kota Binjai set the kop in
 * Haettenschweiler and the body in Times New Roman. Both faces ship with
 * Windows, so they are copied into app storage on first use and never
 * committed to the repository — the same policy for every printed document,
 * whether it is rendered by Dompdf or by the browser.
 *
 * Two consumers, two shapes of the same font:
 *   - Dompdf reads a filesystem path (`faces()`), because it renders server-side.
 *   - The browser needs bytes it can fetch (`faceDataUri()`), because it cannot
 *     reach the private storage disk.
 *
 * The address split also lives here so the report and the visitor pass can
 * never disagree about what the letterhead says.
 */
final class Letterhead
{
    /**
     * Face name => [destination file name, vendor file names in preference order].
     *
     * @var array<string, array{string, list<string>}>
     */
    private const FACES = [
        'kop' => ['Haettenschweiler.ttf', ['HATTEN.TTF', 'hbatten.ttf']],
        'serif' => ['TimesNewRoman.ttf', ['times.ttf']],
        'serifBold' => ['TimesNewRoman-Bold.ttf', ['timesbd.ttf']],
    ];

    /**
     * Where the vendor ships these faces. Windows first — the deployment
     * target for the institution's own print workflow.
     *
     * @var list<string>
     */
    private const SOURCE_DIRS = [
        'C:/Windows/Fonts',
        '/usr/share/fonts/truetype/msttcorefonts',
    ];

    /**
     * Filesystem paths for every face, for a server-side renderer.
     *
     * @return array<string, string|null>
     */
    public function faces(): array
    {
        $resolved = [];

        foreach (array_keys(self::FACES) as $face) {
            $resolved[$face] = $this->path($face);
        }

        return $resolved;
    }

    /**
     * A single face as a filesystem path, or null when the machine does not
     * have it and no copy could be staged.
     */
    public function path(string $face): ?string
    {
        if (! isset(self::FACES[$face])) {
            return null;
        }

        [$destination, $vendorNames] = self::FACES[$face];
        $target = 'fonts/'.$destination;
        $disk = Storage::disk('local');

        if (! $disk->exists($target)) {
            foreach ($vendorNames as $vendorName) {
                foreach (self::SOURCE_DIRS as $dir) {
                    $source = $dir.'/'.$vendorName;

                    if (is_file($source)) {
                        $disk->put($target, (string) file_get_contents($source));

                        break 2;
                    }
                }
            }
        }

        return $disk->exists($target)
            ? str_replace('\\', '/', $disk->path($target))
            : null;
    }

    /**
     * The same face as a data URI, so a browser can load it with no public
     * asset, no CDN and no extra route. Returns null when the face is missing,
     * letting the caller fall back to a system stack.
     */
    public function faceDataUri(string $face): ?string
    {
        $path = $this->path($face);

        if ($path === null || ! is_file($path)) {
            return null;
        }

        return 'data:font/ttf;base64,'.base64_encode((string) file_get_contents($path));
    }

    /**
     * Split the stored address into the two letterhead lines of the printed
     * forms: "Jalan Veteran No. 9" and "Binjai - 20714".
     *
     * @return array{string, string}
     */
    public function addressLines(?string $address): array
    {
        $fallback = 'Jalan Veteran No. 9, Binjai 20714';
        $parts = explode(', ', trim((string) $address) ?: $fallback, 2);
        // "Binjai 20714" -> "Binjai - 20714", as printed on the letterhead.
        $city = preg_replace('/\s+(\d+)$/', ' - $1', $parts[1] ?? 'Binjai 20714');

        return [$parts[0], $city];
    }

    /**
     * The first letterhead line, above the institution name.
     */
    public function governmentLine(): string
    {
        return 'Pemerintah Kota Binjai';
    }
}
