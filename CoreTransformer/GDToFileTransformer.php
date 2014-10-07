<?php

namespace PHPAsset\CoreTransformer;

use PHPAsset\Transformer;
use PHPAsset\Asset;

class GDToFileTransformer extends AbstractTransformer {
	public $inType = 'image_resource_identifier';
	public $outType = 'file';

	public function transformAsset(Asset $asset, array $settings) {
		$asset->setType($this->outType);

		$success = imagepng($asset->getData(), $asset->getFilePath());

		return $asset;
	}
}