<?php declare(strict_types = 1);

use Xervice\DataProvider\DataProviderConfig;

$config[DataProviderConfig::DATA_PROVIDER_GENERATED_PATH] = __DIR__ . '/../../shopware-app/shared/DataProvider/Generated';
$config[DataProviderConfig::DATA_PROVIDER_NAMESPACE] = 'Nxs\\Shared\\DataProvider';
$config[DataProviderConfig::DATA_PROVIDER_PATHS] = [
    __DIR__ . '/../../shopware-app/shared/DataProvider/Schema',
];
