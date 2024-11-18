<?php

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Set\ValueObject\DowngradeSetList;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Misc\Rector\RemoveReturnType;
use Misc\Rector\DowngradeParameterType;
use Misc\Rector\RemoveFunctionType;
use Rector\DowngradePhp70\Rector\Declare_\DowngradeStrictTypeDeclarationRector;

$version = getenv('PHP_VERSION');
$version = $version ?: '72';
$cacheDir = getenv('RECTOR_CACHE_DIR');
$cacheDir = $cacheDir ?: '/tmp/rector_cached_files';

return static function (RectorConfig $rectorConfig) use ($version, $cacheDir): void {
    $skipFiles = array(
        'test/*',
        'framework/zand',
        '*/export2xlsx.php',
    );
    if($version == '81') $skipFiles[] = 'lib/requests/*';
    $rectorConfig->skip($skipFiles);

    $rectorConfig->phpVersion(constant(PhpVersion::class . '::PHP_' . $version));
    $rectorConfig->sets([constant(DowngradeLevelSetList::class . '::DOWN_TO_PHP_' . $version)]);
    $rectorConfig->rules([
        RemoveReturnType::class,
        RemoveFunctionType::class,
        DowngradeParameterType::class,
        DowngradeStrictTypeDeclarationRector::class
    ]);
    // $rectorConfig->sets([constant(DowngradeSetList::class . '::PHP_' . $version)]);
    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory($cacheDir);
};
