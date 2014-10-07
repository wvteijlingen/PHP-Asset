<?php

namespace PHPAsset\CoreTransformer;

use PHPAsset\Transformer;
use PHPAsset\Asset;

class FileToGDTransformer extends AbstractTransformer {
	public $inType = 'file';
	public $outType = 'image_resource_identifier';

	public function transformAsset(Asset $asset, array $settings) {
		$filePath = $asset->getFilePath();

		$asset->setData(imagecreatefrompng($filePath));
		$asset->setType($this->outType);
		
		return $asset;
	}
}