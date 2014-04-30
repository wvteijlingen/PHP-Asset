PHPAsset
========

Introduction
------------
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
```

###Removing assets###
Assets can be removed by passing them or their name to the `removeAsset` method.
```
$manager->removeAsset($asset); //Using the asset itself
$manager->removeAsset('Some asset'); //Or by its name
```

Transforming assets
===================
The real power comes by requesting transformed assets. Transforming is done by copying the original asset and passing it through a chain of configured transformers. At the end of the chain, the asset will be transformed to the desired output format.

Transformers
------------
A transformer takes an assets, transforms it, and returns it. Because PHPAsset is doesn't care what kind of assets you use, every transformer has an `inType` and an `outType`. These define what type goes in, and what type goes out. For example: an image transforming using the GD library will accept an image file, and return an image resource identifier.

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
The real power of PHPAsset comes from named transformation procedures. Instead of passing a large array of transformations every time, you can register a procedure and refer to it by name later. You can create a configuration file that contain all your different transformations. For example: `avatar-overview`, `avatar-profile`, `avatar-footer`, makes it really easy to have different sizes of user avatars.

####Registering procedures####
Procedures can be registered using the `registerProcedure` method.


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