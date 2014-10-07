<?php

namespace PHPAsset\CoreTransformer;

use PHPAsset\Transformer;
use PHPAsset\Asset;

class ImageSizeTransformer extends AbstractTransformer {
	public $inType = 'file';
	public $outType = 'image_resource_identifier';

	public function transformAsset(Asset $asset, array $settings) {
		$filePath = $asset->getFilePath();

		list($originalWidth, $originalHeight) = getimagesize($filePath);

		$originalImage = imagecreatefrompng($filePath);
		$transformedImage = imagecreatetruecolor($settings['width'], $settings['height']);

		imagecopyresampled($transformedImage, $originalImage, 0, 0, 0, 0, $settings['width'], $settings['height'], $originalWidth, $originalHeight);

		$asset->setData($transformedImage);
		$asset->setType($this->outType);

		return $asset;
	}
}