<?php

namespace PHPAsset\Database;

interface AdapterInterface {
	public function storeAsset($asset);
	public function removeAsset($asset);
	public function replaceAsset($asset);
	public function getAssetByName($assetName);

	public function storeTransformedAsset($asset);
	public function removeTransformedAssetsByOriginalId($originalAssetId);
	public function getTransformedAssets($originalAssetId);
	public function getTransformedAsset($originalAssetId, $transformationHash);
}