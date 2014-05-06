<?php

namespace PHPAsset\Asset;

class TransformedAsset extends Asset {
	protected $_originalAssetId;
	protected $_transformationHash;

	public function __construct(Asset $asset = null) {
		parent::__construct($asset);

		if($asset instanceof TransformedAsset) {
			$this->_originalAssetId = $asset->getOriginalAssetId();
			$this->_transformationHash = $asset->getTransformationHash();
		} elseif($asset instanceof Asset) {
			$this->_originalAssetId = $asset->getId();
		}
	}

	public function getOriginalAssetId() {
		return $this->_originalAssetId;
	}

	public function setOriginalAssetId($originalAssetId) {
		$this->_originalAssetId = $originalAssetId;
	}

	public function getTransformationHash() {
		return $this->_transformationHash;
	}

	public function setTransformationHash($transformationHash) {
		$this->_transformationHash = $transformationHash;
	}

}