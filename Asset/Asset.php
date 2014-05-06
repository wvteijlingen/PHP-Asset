<?php

namespace PHPAsset\Asset;

class Asset {
	protected $_id;
	protected $_fileIdentifier;
	protected $_name;
	protected $_type;

	protected $_filePath;
	protected $_data;

	/**
	 * Instantiates a new asset.
	 * You can optionally pass another Asset instance
	 * which properties will then be copied into the new instance.
	 * 
	 * @param PHPAsset\Asset $asset Optional asset to clone.
	 */
	public function __construct(Asset $asset = null) {
		if($asset !== null) {
			$this->_id = $asset->getId();
			$this->_fileIdentifier = $asset->getIdentifier();
			$this->_name = $asset->getName();
			$this->_type = $asset->getType();

			$this->_filePath = $asset->getFilePath();
			$this->_data = $asset->getData();
		}
	}

	/**
	 * Instantiates a new asset from a given file.
	 * This returns an Asset with the name and data of the file.
	 * 
	 * @param  string $filePath  Path to file to create asset from.
	 * @return PHPAsset\Asset    Asset with name and data of file.
	 */
	public static function fromFile($filePath) {
		$pathInfo = pathinfo($filePath);

		$this->_data = file_get_contents($filePath);
		$this->_name = $pathInfo['filename'];
	}


	public function getId() {
		return $this->_id;
	}

	public function setId($id) {
		$this->_id = $id;
	}


	public function getIdentifier() {
		return $this->_fileIdentifier;
	}

	public function setIdentifier($identifier) {
		$this->_fileIdentifier = $identifier;
	}


	public function getName() {
		return $this->_name;
	}

	public function setName($name) {
		$this->_name = $name;
	}


	public function getType() {
		return $this->_type;
	}

	public function setType($type) {
		$this->_type = $type;
	}


	public function getFilePath() {
		return $this->_filePath;
	}

	public function setFilePath($filePath) {
		$this->_filePath = $filePath;
	}


	public function getData() {
		return $this->_data;
	}

	public function setData($data) {
		$this->_data = $data;
	}

	//Magic setters for PDO
	public function __set($key, $value) {
		//Convert snake_case_key to camelCaseKey
		$func = create_function('$c', 'return strtoupper($c[1]);');
		$camelCaseKey = preg_replace_callback('/_([a-z])/', $func, $key);

		$ding = '_' . $camelCaseKey;
		$this->{$ding} = $value;
	}
}