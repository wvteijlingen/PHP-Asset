<?php

namespace PHPAsset;
use PDO;

class AssetManager {

	protected $_originalsDirectory;
	protected $_tempDirectory;
	protected $_cacheDirectory;

	protected $_database;

	protected $_procedures = array();
	protected $_typeTransformers = array();

	/**
	 * Instantiates a new AssetManager.
	 * 
	 * @param string                             $originalsDirectory  Path to directory for asset originals.
	 * @param string                             $temporaryDirectory  Path to directory for temporary files.
	 * @param string                             $cacheDirectory      Path to directory for cached assets.
	 * @param PHPAsset/Database/AdapterInterface $databaseAdapter     The database adapter to use.
	 */
	public function __construct($originalsDirectory, $temporaryDirectory, $cacheDirectory, $databaseAdapter) {
		$this->_originalsDirectory = $originalsDirectory;
		$this->_tempDirectory = $temporaryDirectory;
		$this->cacheDirectory = $cacheDirectory;

		$this->_database = $databaseAdapter;
	}


	/**
	 * -----------------------------------------------
	 * Managing assets
	 * -----------------------------------------------
	 */

	/**
	 * Adds the given asset to the managed asset store.
	 * 
	 * @param  PHPAsset\Asset\Asset $asset  The asset to add to the store.
	 * @return PHPAsset\Asset\Asset         The stored asset
	 */
	public function addAsset(Asset\Asset $asset) {
		$identifier = uniqid();
		$asset->setIdentifier($identifier);

		//Save the asset in the originals directory
		file_put_contents($this->_originalsDirectory . DIRECTORY_SEPARATOR . $identifier, $asset->getData());

		$asset = $this->_database->storeAsset($asset);

		return $asset;
	}

	/**
	 * Removes the given asset or asset with given name.
	 * 
	 * @param  string|PHPAsset\Asset\Asset $assetOrName  The asset or name of asset to remove.
	 * @return boolean                                   True on success, false otherwise.
	 */
	public function removeAsset($assetOrName) {
		$name = $this->_resolveAssetName($assetOrName);
		$asset = $this->getAsset($name);

		if(!$asset) {
			throw new Exception("Cannot remove asset with name '{$name}'. Asset not found");
		}

		//Remove original asset from disk
		unlink($this->_originalsDirectory . DIRECTORY_SEPARATOR . $identifier);

		//Remove transformed cached assets from disk
		$transformedAssets = $this->_database->getTransformedAssets($asset->getId());

		foreach ($transformedAssets as $transformedAsset) {
			$filePath = $this->_cacheDirectory . DIRECTORY_SEPARATOR . $transformedAsset->getIdentifier();

			if(file_exists($filePath)) {
				unlink($filePath);
			}
		}
		
		//Remove transformed cached assets from database
		$this->_database->removeTransformedAssetsByOriginalId($asset->getId());

		//Remove original asset from database
		return $this->_database->removeAssetByName($name);
	}

	/**
	 * Returns the original asset for the given asset or name.
	 * 
	 * @param  string|PHPAsset\Asset\Asset $assetOrName  The name of the asset or the asset to return.
	 * @return PHPAsset\Asset\Asset                      The original asset or null if no asset found.
	 */
	public function getAsset($assetOrName) {
		$name = $this->_resolveAssetName($assetOrName);

		$asset = $this->_database->getAssetByName($name);

		if($asset) {
			$asset->setFilePath($this->_originalsDirectory . DIRECTORY_SEPARATOR . $asset->getIdentifier());
			$asset->setData(file_get_contents($asset->getFilePath()) );
			$asset->setType('file');

			return $asset;
		}

		return null;
	}



	/**
	 * -----------------------------------------------
	 * Managing procedures
	 * -----------------------------------------------
	 */

	/**
	 * Registers a new named tranformations procedure.
	 * These can later be referenced using `getTransformedAssetByProcedure`.
	 * 
	 * @param  string $name             The name of the procedure.
	 * @param  array  $transformations  Array of transformations in the procedure.
	 * @param  string $outputType       The type of output desired.
	 * @param  array  $outputSettings   Settings for the output transformer.
	 */
	public function registerProcedure($name, $transformations, $outputType, $outputSettings) {
		$procedure = new TransformProcedure\TransformProcedure();

		$procedure->setName($name);
		$procedure->setTransformations($transformations);
		$procedure->setOutputType($outputType);
		$procedure->setOutputSettings($outputSettings);

		$this->_procedures[$name] = $procedure;
	}



	/**
	 * -----------------------------------------------
	 * Transforming assets
	 * -----------------------------------------------
	 */

	/**
	 * Returns an asset transformed by a given procedure or registered named procedure.
	 * 
	 * @param  string|PHPAsset\Asset\Asset                            $assetOrName     The name of the asset or the asset to transform.
	 * @param  string|PHPAsset\TransformProcedure\TransformProcedure  $procedureOrName The procedure or name of procedure containing transformations.
	 * @param  boolean                                                $cache           Whether to cache this transformation. Default true.
	 * @return PHPAsset\Asset\TransformedAsset                                         The transformed asset.
	 */
	public function getTransformedAssetByProcedure($assetOrName, $procedureOrName, $cache = true) {
		$procedure = is_string($procedureOrName) ? $this->_procedures[$procedureOrName] : $procedureOrName;

		$originalAsset = $this->getAsset($assetOrName);

		if($cache) {
			$cachedAsset = $this->_getTransformedAssetFromCache($originalAsset->getId(), $procedure->getHash());
			if($cachedAsset) return $cachedAsset;
		}

		//Create a copy of the asset and transform it
		$transformedIdentifier = uniqid();

		copy($this->_originalsDirectory . DIRECTORY_SEPARATOR . $originalAsset->getIdentifier(), $this->_tempDirectory . DIRECTORY_SEPARATOR . $transformedIdentifier);

		$assetToTransform = new Asset\TransformedAsset($originalAsset);
		$assetToTransform->setIdentifier($transformedIdentifier);
		$assetToTransform->setFilePath($this->_tempDirectory . DIRECTORY_SEPARATOR . $transformedIdentifier);

		$transformedAsset = $this->_transformAsset($assetToTransform, $procedure);

		//Save to database and cache directory if cache is true
		if($cache) {
			//Move to cache directory
			$cacheFilePath = $this->_cacheDirectory . DIRECTORY_SEPARATOR . $transformedAsset->getIdentifier();
			rename($transformedAsset->getFilePath(), $cacheFilePath);
			$transformedAsset->setFilePath($cacheFilePath);

			$this->_database->storeTransformedAsset($transformedAsset);
		}

		return $transformedAsset;
	}

	/**
	 * Returns an asset transformed by given transformations and output settings.
	 * 
	 * @param  string|PHPAsset\Asset\Asset      $assetOrName      The name of the asset or the asset to transform.
	 * @param  array                            $transformations  Array of transformations to apply.
	 * @param  string                           $outputType       The name of the desirec output type.
	 * @param  array                            $outputSettings   The type of output desired.
	 * @param  boolean                          $cache            Settings for the output transformer.
	 * @return PHPAsset\Asset\TransformedAsset                    The transformed asset.
	 */
	public function getTransformedAsset($assetOrName, $transformations, $outputType, $outputSettings, $cache = true) {
		$procedure = new TransformProcedure\TransformProcedure();
		$procedure->setTransformations($transformations);
		$procedure->setOutputType($outputType);
		$procedure->setOutputSettings($outputSettings);

		return $this->getTransformedAssetByProcedure($assetOrName, $procedure, $cache);
	}

	protected function _getTransformedAssetFromCache($originalAssetId, $transformationHash) {
		$cachedAsset = $this->_database->getTransformedAsset($originalAssetId, $transformationHash);

		if($cachedAsset) {
			$cachedAsset->setFilePath($this->_cacheDirectory . DIRECTORY_SEPARATOR . $cachedAsset->getIdentifier());
			$cachedAsset->setData(file_get_contents($cachedAsset->getFilePath()) );
			return $cachedAsset;
		}

		return null;
	}

	protected function _transformAsset($assetToTransform, TransformProcedure\TransformProcedure $procedure) {
		//Transform
		$transformations = $procedure->getTransformations();

		foreach ($transformations as $transformerString => $transformerSettings) {
			$transformer = new $transformerString();

			//Type transform if needed
			if($assetToTransform->getType() !== $transformer->inType) {
				$assetToTransform = $this->_typeTransformAsset($asset, $transformer->inType);
			}

			$assetToTransform = $transformer->transformAsset($assetToTransform, $transformerSettings);
		}

		//Make sure it is converted to the correct output type with settings
		if($assetToTransform->getType() !== $procedure->getOutputType()) {
			$assetToTransform = $this->_typeTransformAsset($assetToTransform, $procedure->getOutputType(), $procedure->getOutputSettings());
		}

		$assetToTransform->setTransformationHash($procedure->getHash() );

		return $assetToTransform;
	}


	/**
	 * -----------------------------------------------
	 * Type transforming
	 * -----------------------------------------------
	 */
	
	/**
	 * Registers a type transformer used for passing assets between regular transformers.
	 * 
	 * @param  PHPAsset\Transformer\Transformer  $transformer  The transformer.
	 */
	public function registerTypeTransformer($transformer) {
		$this->_typeTransformers[] = $transformer;
	}

	protected function _getSuitableTypeTransformer($inType, $outType) {
		foreach ($this->_typeTransformers as $transformer) {
			if($transformer->inType === $inType && $transformer->outType === $outType) {
				return new $transformer;
			}
		}

		return null;
	}

	protected function _typeTransformAsset($asset, $outType, $settings = null) {
		$typeTransformer = $this->_getSuitableTypeTransformer($asset->getType(), $outType);

		if(!$typeTransformer) {
			throw new Exception("Transformer not found for: " . $asset->getType() . " > " . $transformer->inType);
		}

		$asset = $typeTransformer->transformAsset($asset, $settings);

		return $asset;
	}



	/**
	 * -----------------------------------------------
	 * Utilities
	 * -----------------------------------------------
	 */

	protected function _resolveAssetName($assetOrName) {
		$name = $assetOrName;

		if($assetOrName instanceof Asset) {
			$name = $asset->getName();
		}

		return $name;
	}
}