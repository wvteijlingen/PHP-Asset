<?php

namespace PHPAsset\CoreTransformer;

class FileToGDTransformer extends \PHPAsset\Transformer\AbstractTransformer {
	public $inType = 'file';
	public $outType = 'image_resource_identifier';

	public function transformAsset($asset, $settings) {
		$filePath = $asset->getFilePath();

		$asset->setData(imagecreatefrompng($filePath));
		$asset->setType($this->outType);
		
		return $asset;
	}
}