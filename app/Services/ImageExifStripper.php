<?php

namespace App\Services;

class ImageExifStripper
{
    /**
     * Remove embedded metadata (EXIF/XMP, which can carry GPS location data)
     * from image bytes without re-encoding the pixel data.
     */
    public function strip(string $contents, string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => $this->stripJpeg($contents),
            'image/png' => $this->stripPng($contents),
            default => $contents,
        };
    }

    private function stripJpeg(string $data): string
    {
        if (substr($data, 0, 2) !== "\xFF\xD8") {
            return $data;
        }

        $output = "\xFF\xD8";
        $offset = 2;
        $length = strlen($data);

        while ($offset + 1 < $length) {
            if ($data[$offset] !== "\xFF") {
                $output .= substr($data, $offset);
                break;
            }

            $marker = $data[$offset + 1];
            $markerByte = ord($marker);

            // Markers with no payload (RSTn, SOI, EOI).
            if ($marker === "\xD9" || ($markerByte >= 0xD0 && $markerByte <= 0xD8)) {
                $output .= $data[$offset].$marker;
                $offset += 2;

                continue;
            }

            // Start of Scan: the rest of the file is compressed image data.
            if ($marker === "\xDA") {
                $output .= substr($data, $offset);
                break;
            }

            if ($offset + 4 > $length) {
                $output .= substr($data, $offset);
                break;
            }

            $segmentLength = (ord($data[$offset + 2]) << 8) | ord($data[$offset + 3]);
            $segmentEnd = $offset + 2 + $segmentLength;

            if ($segmentEnd > $length) {
                $output .= substr($data, $offset);
                break;
            }

            // APP1 carries Exif/XMP metadata, including GPS coordinates - drop it.
            if ($marker === "\xE1") {
                $offset = $segmentEnd;

                continue;
            }

            $output .= substr($data, $offset, $segmentEnd - $offset);
            $offset = $segmentEnd;
        }

        return $output;
    }

    private function stripPng(string $data): string
    {
        $signature = "\x89PNG\r\n\x1a\n";

        if (! str_starts_with($data, $signature)) {
            return $data;
        }

        $output = $signature;
        $offset = 8;
        $length = strlen($data);

        while ($offset + 8 <= $length) {
            $chunkLength = unpack('N', substr($data, $offset, 4))[1];
            $chunkType = substr($data, $offset + 4, 4);
            $chunkTotal = 8 + $chunkLength + 4;

            if ($offset + $chunkTotal > $length) {
                $output .= substr($data, $offset);
                break;
            }

            if ($chunkType !== 'eXIf') {
                $output .= substr($data, $offset, $chunkTotal);
            }

            $offset += $chunkTotal;

            if ($chunkType === 'IEND') {
                break;
            }
        }

        return $output;
    }
}
