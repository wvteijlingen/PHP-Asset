<?php

namespace PHPAsset\Database;

use PHPAsset\Asset;

interface AdapterInterface {
	public function storeAsset(Asset $asset);
	public function removeAssetByName($asset);
	public function getAssetByName($assetName);

	public function storeTransformedAsset(Asset $asset);
	public function removeTransformedAssetsByOriginalId($originalAssetId);
	public function getTransformedAssetsByOriginalId($originalAssetId);
	public function getTransformedAssetByOriginalId($originalAssetId, $transformationHash);
}