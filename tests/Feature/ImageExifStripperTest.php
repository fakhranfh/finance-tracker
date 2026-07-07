<?php

use App\Services\ImageExifStripper;

test('strips the Exif/GPS segment from a JPEG without corrupting the image', function () {
    $baseJpeg = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAA/AKp//9k=');

    $exifPayload = "Exif\0\0".'FAKE_GPS_LATLON_37.7749_-122.4194';
    $exifSegment = "\xFF\xE1".pack('n', 2 + strlen($exifPayload)).$exifPayload;
    $jpegWithExif = substr($baseJpeg, 0, 2).$exifSegment.substr($baseJpeg, 2);

    expect($jpegWithExif)->toContain('FAKE_GPS_LATLON');

    $stripped = (new ImageExifStripper)->strip($jpegWithExif, 'image/jpeg');

    expect($stripped)->not->toContain('FAKE_GPS_LATLON')
        ->and($stripped)->not->toContain("Exif\0\0");

    $dimensions = getimagesizefromstring($stripped);
    expect($dimensions)->not->toBeFalse();
});

test('strips the eXIf chunk from a PNG without corrupting the image', function () {
    $chunk = fn (string $type, string $data) => pack('N', strlen($data)).$type.$data.pack('N', 0);

    $ihdr = $chunk('IHDR', pack('N', 1).pack('N', 1)."\x08\x02\x00\x00\x00");
    $exifChunk = $chunk('eXIf', "Exif\0\0".'FAKE_GPS_LATLON_37.7749_-122.4194');
    $iend = $chunk('IEND', '');

    $pngWithExif = "\x89PNG\r\n\x1a\n".$ihdr.$exifChunk.$iend;

    expect($pngWithExif)->toContain('FAKE_GPS_LATLON');

    $stripped = (new ImageExifStripper)->strip($pngWithExif, 'image/png');

    expect($stripped)->not->toContain('FAKE_GPS_LATLON')
        ->and($stripped)->toContain('IHDR')
        ->and($stripped)->toContain('IEND');
});

test('leaves non-image content untouched', function () {
    $contents = 'not an image';

    expect((new ImageExifStripper)->strip($contents, 'application/pdf'))->toBe($contents);
});
