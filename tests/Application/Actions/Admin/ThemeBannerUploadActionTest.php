<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Admin;

use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UploadedFileFactory;
use Tests\AuthApiUtility;

final class ThemeBannerUploadActionTest extends AuthApiUtility
{
    public function testWebpBannerUploadCreatesThumbnails(): void
    {
        $this->setAdmin();

        $filename = 'webp-banner-thumbnail-test-'.bin2hex(random_bytes(6)).'.webp';
        $mediaDirectory = $this->getMediaDirPath().'/theme/banner';
        $thumbnailDirectory = $mediaDirectory.'/thumbnails';
        $imagePath = $mediaDirectory.'/'.$filename;
        $themeDirectory = $this->getPublicDirPath().'/theme';
        $themeFile = $themeDirectory.'/theme.json';
        $themeFileExisted = file_exists($themeFile);
        $originalTheme = $themeFileExisted ? file_get_contents($themeFile) : null;
        $temporaryFile = tempnam(sys_get_temp_dir(), 'mobilecms-banner-webp-');
        $image = \imagecreatetruecolor(1200, 600);

        if (!is_dir($mediaDirectory)) {
            mkdir($mediaDirectory, 0755, true);
        }
        if (!is_dir($themeDirectory)) {
            mkdir($themeDirectory, 0755, true);
        }
        if (!$themeFileExisted) {
            file_put_contents($themeFile, '{}');
        }

        try {
            $this->assertTrue(\imagewebp($image, $temporaryFile));
            \imagedestroy($image);
            unset($image);

            $stream = (new StreamFactory())->createStreamFromFile($temporaryFile);
            $uploadedFile = (new UploadedFileFactory())->createUploadedFile(
                $stream,
                filesize($temporaryFile),
                UPLOAD_ERR_OK,
                $filename,
                'image/webp'
            );
            $request = $this->createRequest(
                'POST',
                $this->getApi().'/adminapi/theme/banner',
                $this->headers
            )->withUploadedFiles(['banner' => $uploadedFile]);

            $response = $this->getAppInstance()->handle($request);
            $payload = json_decode((string) $response->getBody());

            $this->assertSame(200, $response->getStatusCode());
            $this->assertNotEmpty($payload->thumbnails);
            $this->assertFileExists($imagePath);
            foreach ($payload->thumbnails as $thumbnail) {
                $this->assertFileExists($thumbnailDirectory.'/'.$thumbnail->url);
            }
        } finally {
            if (isset($image) && $image instanceof \GdImage) {
                \imagedestroy($image);
            }
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            if (is_dir($thumbnailDirectory)) {
                foreach (glob($thumbnailDirectory.'/'.pathinfo($filename, PATHINFO_FILENAME).'-*') ?: [] as $thumbnailPath) {
                    unlink($thumbnailPath);
                }
                if (count(scandir($thumbnailDirectory)) === 2) {
                    rmdir($thumbnailDirectory);
                }
            }
            if ($themeFileExisted) {
                file_put_contents($themeFile, $originalTheme);
            } elseif (file_exists($themeFile)) {
                unlink($themeFile);
            }
            if (is_dir($themeDirectory) && count(scandir($themeDirectory)) === 2) {
                rmdir($themeDirectory);
            }
            if (is_dir($mediaDirectory) && count(scandir($mediaDirectory)) === 2) {
                rmdir($mediaDirectory);
            }
            if (file_exists($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }
}
