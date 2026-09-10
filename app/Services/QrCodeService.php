<?php

namespace App\Services;

class QrCodeService
{
    /**
     * Galois Field 256 exponent and log tables for Reed-Solomon error correction.
     */
    private static array $exp = [];

    private static array $log = [];

    private static bool $gfInit = false;

    /**
     * Initialize Galois Field GF(256) with primitive polynomial 0x11d.
     */
    private static function initGf(): void
    {
        if (self::$gfInit) {
            return;
        }

        self::$exp = array_fill(0, 512, 0);
        self::$log = array_fill(0, 256, 0);

        $val = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$exp[$i] = $val;
            self::$log[$val] = $i;
            $val <<= 1;
            if ($val & 0x100) {
                $val ^= 0x11D;
            }
        }
        for ($i = 255; $i < 512; $i++) {
            self::$exp[$i] = self::$exp[$i - 255];
        }

        self::$gfInit = true;
    }

    private static function gfMul(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) {
            return 0;
        }

        return self::$exp[self::$log[$x] + self::$log[$y]];
    }

    /**
     * Compute Reed-Solomon generator polynomial of degree $degree.
     */
    private static function rsGeneratorPoly(int $degree): array
    {
        $poly = [1];
        for ($i = 0; $i < $degree; $i++) {
            $next = [1, self::$exp[$i]];
            $res = array_fill(0, count($poly) + 1, 0);
            for ($j = 0; $j < count($poly); $j++) {
                for ($k = 0; $k < count($next); $k++) {
                    $res[$j + $k] ^= self::gfMul($poly[$j], $next[$k]);
                }
            }
            $poly = $res;
        }

        return $poly;
    }

    /**
     * Compute Reed-Solomon error correction codewords.
     */
    private static function rsCalculate(array $data, int $ecCount): array
    {
        $gen = self::rsGeneratorPoly($ecCount);
        $remainder = array_fill(0, $ecCount, 0);

        foreach ($data as $byte) {
            $factor = $byte ^ $remainder[0];
            array_shift($remainder);
            $remainder[] = 0;
            if ($factor !== 0) {
                for ($i = 0; $i < $ecCount; $i++) {
                    $remainder[$i] ^= self::gfMul($gen[$i + 1], $factor);
                }
            }
        }

        return $remainder;
    }

    /**
     * Generate standard QR Code Version 5 (37x37 modules) or Version 4 (33x33) with Byte encoding & EC Level M.
     * Version 4-M has 64 data codewords and 36 EC codewords (total 100).
     * Version 5-M has 86 data codewords and 48 EC codewords (total 134), perfect for URLs up to 84 characters.
     */
    public static function generateSvg(string $text, int $size = 200, string $darkColor = '#14532d', string $lightColor = '#ffffff'): string
    {
        self::initGf();

        // Target: Version 5 (37x37 modules), Error Correction Level M
        // Version 5 capacity: 86 data bytes, 48 EC bytes (2 blocks of 43 data / 24 EC)
        $version = 5;
        $modulesCount = 37;
        $totalDataBytes = 86;
        $ecBytesPerBlock = 24;
        $blocks = [
            ['data' => 43, 'ec' => 24],
            ['data' => 43, 'ec' => 24],
        ];

        // 1. Encode text in 8-bit byte mode
        $bitBuffer = '';
        // Mode indicator for Byte: 0100
        $bitBuffer .= '0100';
        // Character count indicator (8 bits for Version 1-9)
        $charCount = strlen($text);
        $bitBuffer .= str_pad(decbin($charCount), 8, '0', STR_PAD_LEFT);

        // Data bytes
        for ($i = 0; $i < $charCount; $i++) {
            $bitBuffer .= str_pad(decbin(ord($text[$i])), 8, '0', STR_PAD_LEFT);
        }

        // Terminator (up to 4 zeros)
        $remainingBits = ($totalDataBytes * 8) - strlen($bitBuffer);
        $termLength = min(4, max(0, $remainingBits));
        $bitBuffer .= str_repeat('0', $termLength);

        // Pad to multiple of 8
        if (strlen($bitBuffer) % 8 !== 0) {
            $bitBuffer .= str_repeat('0', 8 - (strlen($bitBuffer) % 8));
        }

        // Pad bytes (0xEC, 0x11)
        $padBytes = [0xEC, 0x11];
        $padIndex = 0;
        while (strlen($bitBuffer) < ($totalDataBytes * 8)) {
            $bitBuffer .= str_pad(decbin($padBytes[$padIndex % 2]), 8, '0', STR_PAD_LEFT);
            $padIndex++;
        }

        // Split into bytes
        $dataCodewords = [];
        for ($i = 0; $i < strlen($bitBuffer); $i += 8) {
            $dataCodewords[] = bindec(substr($bitBuffer, $i, 8));
        }

        // Split data into blocks and calculate EC
        $blockData = [];
        $blockEc = [];
        $offset = 0;
        foreach ($blocks as $idx => $blockInfo) {
            $slice = array_slice($dataCodewords, $offset, $blockInfo['data']);
            $blockData[$idx] = $slice;
            $blockEc[$idx] = self::rsCalculate($slice, $blockInfo['ec']);
            $offset += $blockInfo['data'];
        }

        // Interleave data codewords
        $finalCodewords = [];
        $maxDataLen = 43;
        for ($i = 0; $i < $maxDataLen; $i++) {
            foreach ($blockData as $bd) {
                if (isset($bd[$i])) {
                    $finalCodewords[] = $bd[$i];
                }
            }
        }
        // Interleave EC codewords
        for ($i = 0; $i < $ecBytesPerBlock; $i++) {
            foreach ($blockEc as $bec) {
                if (isset($bec[$i])) {
                    $finalCodewords[] = $bec[$i];
                }
            }
        }

        // Remainder bits for version 5: 7 bits of 0
        $finalBits = '';
        foreach ($finalCodewords as $cw) {
            $finalBits .= str_pad(decbin($cw), 8, '0', STR_PAD_LEFT);
        }
        $finalBits .= str_repeat('0', 7);

        // 2. Build 37x37 matrix
        $matrix = array_fill(0, $modulesCount, array_fill(0, $modulesCount, null));
        $reserved = array_fill(0, $modulesCount, array_fill(0, $modulesCount, false));

        // Place Finder Patterns (7x7) at (0,0), (0, 30), (30, 0)
        self::placeFinderPattern($matrix, $reserved, 0, 0);
        self::placeFinderPattern($matrix, $reserved, 0, $modulesCount - 7);
        self::placeFinderPattern($matrix, $reserved, $modulesCount - 7, 0);

        // Place Separators around finder patterns
        self::placeSeparators($matrix, $reserved, $modulesCount);

        // Place Alignment Patterns for Version 5: center positions at 6, 30
        $alignPos = [6, 30];
        foreach ($alignPos as $row) {
            foreach ($alignPos as $col) {
                if ($reserved[$row][$col]) {
                    continue;
                }
                self::placeAlignmentPattern($matrix, $reserved, $row, $col);
            }
        }

        // Place Timing Patterns (row 6 and col 6)
        for ($i = 8; $i < $modulesCount - 8; $i++) {
            if (! $reserved[6][$i]) {
                $matrix[6][$i] = ($i % 2 === 0);
                $reserved[6][$i] = true;
            }
            if (! $reserved[$i][6]) {
                $matrix[$i][6] = ($i % 2 === 0);
                $reserved[$i][6] = true;
            }
        }

        // Dark module at (4*version + 9, 8) => (29, 8)
        $matrix[29][8] = true;
        $reserved[29][8] = true;

        // Reserve Format Information areas
        self::reserveFormatAreas($reserved, $modulesCount);

        // 3. Place data bits in zigzag order
        $bitIdx = 0;
        $totalBits = strlen($finalBits);
        $col = $modulesCount - 1;
        $up = true;

        while ($col > 0) {
            if ($col === 6) {
                $col--; // Skip vertical timing pattern
            }

            for ($rowStep = 0; $rowStep < $modulesCount; $rowStep++) {
                $r = $up ? ($modulesCount - 1 - $rowStep) : $rowStep;
                for ($cOffset = 0; $cOffset < 2; $cOffset++) {
                    $c = $col - $cOffset;
                    if (! $reserved[$r][$c]) {
                        $bit = ($bitIdx < $totalBits) ? ($finalBits[$bitIdx] === '1') : false;
                        $matrix[$r][$c] = $bit;
                        $bitIdx++;
                    }
                }
            }
            $col -= 2;
            $up = ! $up;
        }

        // 4. Apply Mask 0: (row + col) % 2 == 0
        for ($r = 0; $r < $modulesCount; $r++) {
            for ($c = 0; $c < $modulesCount; $c++) {
                if (! $reserved[$r][$c]) {
                    if (($r + $c) % 2 === 0) {
                        $matrix[$r][$c] = ! $matrix[$r][$c];
                    }
                }
            }
        }

        // 5. Place Format Information: EC Level M (00) + Mask 0 (000) => 00000
        // BCH(15, 5) code for 00000 XOR 101010000010010 = 101010000010010 (binary)
        $formatBits = '101010000010010';
        self::placeFormatInfo($matrix, $formatBits, $modulesCount);

        // 6. Render SVG
        $quietZone = 2;
        $viewBoxSize = $modulesCount + ($quietZone * 2);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$viewBoxSize.' '.$viewBoxSize.'" width="'.$size.'" height="'.$size.'">';
        $svg .= '<rect width="100%" height="100%" fill="'.htmlspecialchars($lightColor, ENT_QUOTES).'"/>';

        for ($r = 0; $r < $modulesCount; $r++) {
            for ($c = 0; $c < $modulesCount; $c++) {
                if ($matrix[$r][$c] === true) {
                    $x = $c + $quietZone;
                    $y = $r + $quietZone;
                    $svg .= '<rect x="'.$x.'" y="'.$y.'" width="1" height="1" fill="'.htmlspecialchars($darkColor, ENT_QUOTES).'"/>';
                }
            }
        }
        $svg .= '</svg>';

        return $svg;
    }

    private static function placeFinderPattern(array &$matrix, array &$reserved, int $startRow, int $startCol): void
    {
        for ($r = 0; $r < 7; $r++) {
            for ($c = 0; $c < 7; $c++) {
                $isBorder = ($r === 0 || $r === 6 || $c === 0 || $c === 6);
                $isCenter = ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4);
                $matrix[$startRow + $r][$startCol + $c] = ($isBorder || $isCenter);
                $reserved[$startRow + $r][$startCol + $c] = true;
            }
        }
    }

    private static function placeSeparators(array &$matrix, array &$reserved, int $modulesCount): void
    {
        // Top-left separators
        for ($i = 0; $i < 8; $i++) {
            if ($i < 8) {
                $matrix[7][$i] = false;
                $reserved[7][$i] = true;
                $matrix[$i][7] = false;
                $reserved[$i][7] = true;
            }
        }
        // Top-right separators
        for ($i = 0; $i < 8; $i++) {
            $matrix[7][$modulesCount - 8 + $i] = false;
            $reserved[7][$modulesCount - 8 + $i] = true;
            $matrix[$i][$modulesCount - 8] = false;
            $reserved[$i][$modulesCount - 8] = true;
        }
        // Bottom-left separators
        for ($i = 0; $i < 8; $i++) {
            $matrix[$modulesCount - 8][$i] = false;
            $reserved[$modulesCount - 8][$i] = true;
            $matrix[$modulesCount - 8 + $i][7] = false;
            $reserved[$modulesCount - 8 + $i][7] = true;
        }
    }

    private static function placeAlignmentPattern(array &$matrix, array &$reserved, int $centerRow, int $centerCol): void
    {
        for ($r = -2; $r <= 2; $r++) {
            for ($c = -2; $c <= 2; $c++) {
                $row = $centerRow + $r;
                $col = $centerCol + $c;
                $isBorder = ($r === -2 || $r === 2 || $c === -2 || $c === 2);
                $isCenter = ($r === 0 && $c === 0);
                $matrix[$row][$col] = ($isBorder || $isCenter);
                $reserved[$row][$col] = true;
            }
        }
    }

    private static function reserveFormatAreas(array &$reserved, int $modulesCount): void
    {
        for ($i = 0; $i <= 8; $i++) {
            $reserved[8][$i] = true;
            $reserved[$i][8] = true;
        }
        for ($i = 0; $i < 8; $i++) {
            $reserved[8][$modulesCount - 8 + $i] = true;
            $reserved[$modulesCount - 8 + $i][8] = true;
        }
    }

    private static function placeFormatInfo(array &$matrix, string $formatBits, int $modulesCount): void
    {
        // Top-left area
        $pos = [
            [8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5], [8, 7], [8, 8],
            [7, 8], [5, 8], [4, 8], [3, 8], [2, 8], [1, 8], [0, 8],
        ];

        for ($i = 0; $i < 15; $i++) {
            $bit = ($formatBits[$i] === '1');
            $matrix[$pos[$i][0]][$pos[$i][1]] = $bit;
        }

        // Top-right & Bottom-left split
        $pos2 = [
            [$modulesCount - 1, 8], [$modulesCount - 2, 8], [$modulesCount - 3, 8], [$modulesCount - 4, 8],
            [$modulesCount - 5, 8], [$modulesCount - 6, 8], [$modulesCount - 7, 8],
            [8, $modulesCount - 8], [8, $modulesCount - 7], [8, $modulesCount - 6], [8, $modulesCount - 5],
            [8, $modulesCount - 4], [8, $modulesCount - 3], [8, $modulesCount - 2], [8, $modulesCount - 1],
        ];

        for ($i = 0; $i < 15; $i++) {
            $bit = ($formatBits[$i] === '1');
            $matrix[$pos2[$i][0]][$pos2[$i][1]] = $bit;
        }
    }
}
