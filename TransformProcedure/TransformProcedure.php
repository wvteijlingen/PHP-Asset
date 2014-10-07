<?php

namespace PHPAsset\TransformProcedure;

class TransformProcedure {
	
	protected $_name;
	protected $_transformations;
	protected $_outputType;
	protected $_outputSettings;

	public function getName() {
		return $this->_name;
	}

	public function setName($name) {
		$this->_name = $name;
	}


	public function getTransformations() {
		return $this->_transformations;
	}

	public function setTransformations(array $transformations) {
		$this->_transformations = $transformations;
	}


	public function getOutputType() {
		return $this->_outputType;
	}

	public function setOutputType($outputType) {
		$this->_outputType = $outputType;
	}


	public function getOutputSettings() {
		return $this->_outputSettings;
	}

	public function setOutputSettings($outputSettings) {
		$this->_outputSettings = $outputSettings;
	}


	public function getHash() {
		return md5(json_encode($this->_transformations) . $this->_outputType . json_encode($this->_outputSettings));
	}
}