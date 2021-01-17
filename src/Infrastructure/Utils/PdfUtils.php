<?php namespace App\Infrastructure\Utils;

/**
 * PDF thumbnail and resize utility
 */
class PdfUtils
{
    /**
     * default image quality
     */
    private $quality = 80;

    /**
     * image driver
     */
    private $driver = 'imagick';

    /**
     * Create a list of thumbnails
     * @param string $file : file path
     * @param string $dir : directory containing resized files
     * @param array $sizes : array of new resized widths
     * @return array created files
     */
    public function multipleResize(string $file, string $dir, array $sizes)
    {
        $result = [];

        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $extension = 'jpg';

        // create directory if necessary
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        foreach ($sizes as $width) {
            // base name : foo.pdf
            $resizedFileName = $fileName . '-' . (string) $width . '.' . $extension;

            // file name : foobar/foo-320.jpg
            $resizedFilePath = $dir . '/' . $resizedFileName;

            $thumbfileResult = $this->resize($file, $resizedFilePath, $width);
            if (!empty($thumbfileResult)) {
                \array_push($result, $thumbfileResult);
            }
        }
        return $result;
    }

    /**
     * Create a thumbnail of the first page
     *
     * @param string $source somewhere/foo.pdf
     * @param string $target foo/foo.pdf
     * @param int $width width in px

     * @return \stdClass JSON image description {"width":"210","height":"297","url":"document.jpg"}
     */
    public function resize($source, $target, $width = 210)
    {
        $result = null;
        // detect mime type
        $file_info = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer(\file_get_contents($source));
        unset($file_info);

        if ($this->driver === 'imagick') {
            if ('application/pdf' === $mime_type) {
                $im = new \Imagick(\realpath($source));
                $im->setIteratorIndex(0);
                $im->setCompression(\Imagick::COMPRESSION_JPEG);
                $im->setCompressionQuality($this->quality);

                $ratio_orig = $im->getImageWidth() / $im->getImageHeight();
                $height = \intval(\round($width / $ratio_orig, 0, PHP_ROUND_HALF_UP));

                $im->setImageFormat('jpeg');
                //https://stackoverflow.com/questions/41585848/imagickflattenimages-method-is-deprecated-and-its-use-should-be-avoided
                $im->setImageAlphaChannel(11); // Imagick::ALPHACHANNEL_REMOVE
                $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $im->setImageColorspace(255); // prevent image colors from inverting
                $im->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
                $im->writeimage($target);
                $im->clear();
                $im->destroy();

                $result = \json_decode('{}');
                $result->{'width'} = (string) $width;
                $result->{'height'} = (string) $height;

                $result->{'url'} = \basename($target);
            }
        }

        return $result;
    }

    /**
     * @param string $file : file path
     * @return \stdClass true if smaller size is created
     */
    public function pdfInfo(string $file)
    {
        $result = \json_decode('{}');
        $fileutils = new FileUtils();
        $result->{'mimetype'} = $fileutils->getMimeType($file);
        $result->{'url'} = \basename($file);

        return $result;
    }

    /**
     * Set quality.
     *
     * @param int $newval set quality
     */
    public function setQuality(int $newval)
    {
        $this->quality = $newval;
    }

    /**
     *
     * @param string $newval enable imagick
     */
    public function setDriver(string $newval)
    {
        $this->driver = $newval;
    }
}
