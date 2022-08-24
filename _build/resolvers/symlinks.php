<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/SafeDeal/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/safedeal')) {
            $cache->deleteTree(
                $dev . 'assets/components/safedeal/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/safedeal/', $dev . 'assets/components/safedeal');
        }
        if (!is_link($dev . 'core/components/safedeal')) {
            $cache->deleteTree(
                $dev . 'core/components/safedeal/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/safedeal/', $dev . 'core/components/safedeal');
        }
    }
}

return true;