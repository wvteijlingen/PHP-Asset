<?php

namespace PHPAsset\Database;

use PDO;
use PHPAsset\Asset;

class PDOAdapter implements AdapterInterface {
	public $host;
	public $databaseName;
	public $user;
	public $password;

	public $tableName;

	protected $_pdo;

	function __construct($host, $databaseName, $user, $password) {
		$this->host = $host;
		$this->databaseName = $databaseName;
		$this->user = $user;
		$this->password = $password;

		$this->tableName = "assets";

		$this->_pdo = new PDO("mysql:host={$host};dbname={$databaseName}", $user, $password);
	}

	public function storeAsset(Asset $asset) {
		$query = "INSERT INTO `original_assets` (`name`, `file_identifier`, `type`)
		            VALUES (:name, :file_identifier, :type)";

		$statement = $this->_pdo->prepare($query);
		$statement->bindValue(':name', $asset->getName());
		$statement->bindValue(':file_identifier', $asset->getIdentifier());
		$statement->bindValue(':type', $asset->getType());

		$success = $statement->execute();

		if($success) {
			$asset->setId($this->_pdo->lastInsertId());
			return $asset;
		} else {
			throw new Exception('Error storing asset');
		}
	}

	public function removeAssetByName($name) {
		$query = "DELETE FROM `original_assets`
		          WHERE `name` = :name";

		$statement = $this->_pdo->prepare($query);
		$statement->bindValue(':name', $name);

		$success = $statement->execute();
		return $success;
	}

	public function getAssetByName($name) {
		$query = "SELECT * FROM `original_assets`
		          WHERE `name` = :name";

		$statement = $this->_pdo->prepare($query);
		$statement->bindValue(':name', $name);

		$statement->setFetchMode(PDO::FETCH_CLASS, '\PHPAsset\Asset\Asset');

		$success = $statement->execute();

		if($success) {
			$asset = $statement->fetch(PDO::FETCH_CLASS);

			if($asset) {
				return $asset;
			}
		}

		return null;
	}


	public function storeTransformedAsset(Asset $asset) {
		$query = "INSERT INTO `transformed_assets` (`original_asset_id`, `file_identifier`, `type`, `transformation_hash`)
			        VALUES (:original_asset_id, :file_identifier, :type, :transformation_hash)";

		$statement = $this->_pdo->prepare($query);
		$statement->bindValue(':original_asset_id', $asset->getOriginalAssetId());
		$statement->bindValue(':file_identifier', $asset->getIdentifier());
		$statement->bindValue(':type', $asset->getType());
		$statement->bindValue(':transformation_hash', $asset->getTransformationHash());

		$success = $statement->execute();

		if($success) {
			$asset->setId($this->_pdo->lastInsertId());
			return $asset;
		}

		return false;
	}

	public function removeTransformedAssetsByOriginalId($originalAssetId) {
		$query = "DELETE FROM `transformed_assets`
		          WHERE `original_asset_id` = :original_asset_id";

		$statement = $this->_pdo->prepare($query);
		$statement->bindValue(':original_asset_id', $originalAssetId);

		$success = $statement->execute();
		return $success;
	}

	public function getTransformedAssetsByOriginalId($originalAssetId) {
		$query = "SELECT * FROM `transformed_assets`
		          WHERE `original_asset_id` = :original_asset_id";

		$statement = $this->_pdo->prepare($query);
		$statement->bindValue(':original_asset_id', $originalAssetId);

		$statement->setFetchMode(PDO::FETCH_CLASS, '\PHPAsset\Asset\TransformedAsset');

		$success = $statement->execute();

		if($success) {
			$assets = $statement->fetchAll(PDO::FETCH_CLASS);

			if($assets !== false) {
				return $assets;
			}
		}

		return false;
	}

	public function getTransformedAssetByOriginalId($originalAssetId, $transformationHash) {
		$query = "SELECT * FROM `transformed_assets`
		          WHERE `original_asset_id` = :original_asset_id
		            AND `transformation_hash` = :transformation_hash";

		$statement = $this->_pdo->prepare($query);
		$statement->bindValue(':original_asset_id', $originalAssetId);
		$statement->bindValue(':transformation_hash', $transformationHash);

		$statement->setFetchMode(PDO::FETCH_CLASS, '\PHPAsset\Asset\TransformedAsset');

		$success = $statement->execute();

		if($success) {
			$asset = $statement->fetch(PDO::FETCH_CLASS);

			if($asset) {
				return $asset;
			}
		}

		return null;
	}
}