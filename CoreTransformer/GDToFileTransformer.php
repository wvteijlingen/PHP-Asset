<?php

namespace PHPAsset\CoreTransformer;

class GDToFileTransformer extends \PHPAsset\Transformer\AbstractTransformer {
	public $inType = 'image_resource_identifier';
	public $outType = 'file';

	public function transformAsset($asset, $settings) {
		$asset->setType($this->outType);

		$success = imagepng($asset->getData(), $asset->getFilePath());

		return $asset;
	}
}