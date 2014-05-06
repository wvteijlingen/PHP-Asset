####This is very much a work in progress. I do not recommend using PHP-Asset in production yet.


PHPAsset
========
PHPAsset simplifies the storing and transforming of 'assets'. An asset can be anything: an image, a PDF, an audio file, a simple text file, etc. It uses chainable transformers to transform these assets. For example: you can create a chain that resizes an image, masks it and adds a glossy reflection. Or, it trims an audio file and adds compression, it's up to you!

Managing assets
----------------

###Adding assets###
Assets can be added to the managed store with the `addAsset` method:
```
$asset = new PHPAsset\Asset\Asset();
$asset->setData(file_get_contents('Some asset.png'));
$asset->setName('Some asset');
$manager->addAsset($asset);

//Or using the static fromFile method
$asset = PHPAsset\Asset\Asset::fromFile('Some asset.png'); //Asset name will be 'Some asset'
$manager->addAsset($asset);
```

###Removing assets###
Assets can be removed by passing them or their name to the `removeAsset` method.
```
$manager->removeAsset($asset); //Using the asset itself
$manager->removeAsset('Some asset'); //Using the asset name
```

Transforming assets
===================
The core functionality is by requesting transformed assets. Transforming is done by copying the original asset and passing it through a chain of configured transformers. At the end of the chain, the asset will be transformed to the desired output format.

The following code takes an image asset, resizes it, applies a grayscale effect and returns a PNG with compression level 2.

```
$transformationChain = array(
    '\ImageTransformer\ImageSizeTransformer' => array(
    	'width' => 500,
		'height' => 500
		),
    '\ImageTransformer\GrayscaleTransformer'
);

$transformedAsset = $manager->getTransformedAsset(
    'Some asset', //Name of asset to transform
    $transformationChain, //Array of transformers to apply
    'file', //The desired output format
    array( //Configuration for the output
	    'imageType' => 'png',
	    'imageCompression' => 2
    ),
    true //Cache this transformation (default)
);
```

Transformers
------------
A transformer takes an asset, transforms it, and returns it. Because PHPAsset doesn't care what kind of assets you use, every transformer has an `inType` and an `outType`. These define what type goes in, and what type goes out. For example: an image transformer using the GD library will accept an image file, and return an image resource identifier.

Basic transformation
--------------------

Transform procedures
--------------------
Internally, every transformer chain is actually a `TransformProcedure`. A procedure defines the transformations, output type and output settings.

Type transformers
-----------------
Sometimes the `outType` of transformer A doesn't match the `inType` of transformer B. In this case, the AssetManager will try to insert an implicit transformer to 'translate' between transformer A and B. These transformers are refered to as 'type transformers' (they work exactly like regular transformers).

####Registering type transformers#####
Type transformers should be registerered using `registerTypeTransformer`. AssetManager will then automatically search all registered transformers for one with a suitable `inType` and `outType`.

Named procedures
----------------
The real power of PHPAsset comes from named transformation procedures. Instead of passing a large array of transformations every time, you can register a procedure and refer to it by name later. You can create a configuration file that contain all your different transformations. For example: `avatar-overview`, `avatar-profile`, `avatar-footer`, makes it really easy to have different sizes of user avatars. This makes it really easy to manage and reuse asset variations!

####Registering procedures####
Procedures can be registered using the `registerProcedure` method.

####Using registered procedures####
Registered procedures can be called using `getTransformedAssetByProcedure`. If we register the example in chapter 'Transforming assets' using the name 'grayscale-500', we can call it as follows:
```
$transformedAsset = $assetManager->getTransformedAssetByProcedure('Some asset', 'grayscale-500');
```

Manager configuration
=====================

Storage directories
-------------------
All assets are stored on disk in three directories. The paths should be passed in the AssetManager constructor.

- `originalDirectory`: The directory where the asset originals are stored.  
- `temporaryDirectory`: The directory where transformed assets are stored. This includes assets that are undergoing transformation and transformed assets that are not cached.  
- `cacheDirectory`: The directory where cached transformed assets are stored.


Database adapters
-----------------
PHPAsset is database agnostic by using a database adapter pattern. The adapter to use should be passed in the AssetManager constructor:
```
$manager = new PHPAsset\AssetManager(
    new PHPAsset\Database\PDOAdapter('host', 'db', 'user', 'pass')
);
```

Included adapters:

- PDO
