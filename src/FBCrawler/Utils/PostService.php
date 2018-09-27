<?php

namespace App\FBCrawler\Utils;

use Brisum\FBCrawler\Entity\Post;
use Exception;

class PostService
{
    /**
     * @var string
     */
    protected $uploadsDir;

    /**
     * @var string
     */
    protected $uploadsUrl;

    /**
     * @param string $uploadsDir
     * @param string $uploadsUrl
     */
    public function __construct($uploadsDir, $uploadsUrl)
    {
        $this->uploadsDir = $uploadsDir;
        $this->uploadsUrl = $uploadsUrl;
    }

    /**
     * @param Post $post
     * @param $imageUrl
     * @return string
     * @throws Exception
     */
    public function saveImage(Post $post, $imageUrl)
    {
        $imageName = basename(preg_replace('/\?[^\?]*$/', '', $imageUrl));
        $imageDir = $this->generateImageDir($post->getId());
        $imageRelativePath = $imageDir . $imageName;
        $imageFullPath = $this->uploadsDir . $imageRelativePath;
        $image = file_get_contents($imageUrl);

        if (!file_exists($this->uploadsDir . $imageDir) && !mkdir($this->uploadsDir . $imageDir, 0777, true)) {
            throw new Exception("Could not create image dir.");
        }

        if (!file_put_contents($imageFullPath, $image)) {
            throw new Exception("Could not save image.");
        }

        return $imageRelativePath;
    }

    public function generateImageDir($id)
    {
        $id = (int) abs($id);
        if ( !$id ) {
            return '';
        }

        $thousandth = (int) ($id / 1000);
        $hundredth = (int) (($id % 1000) / 100);

        return $thousandth . '/' . $hundredth . '/' . $id . '/';
    }
}
