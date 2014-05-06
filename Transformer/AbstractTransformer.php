<?php

namespace PHPAsset\Transformer;

abstract class AbstractTransformer {
	public $inType;
	public $outType;

	abstract public function transformAsset($asset, $settings);
}