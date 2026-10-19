<?php
return ['default' => 'file', 'stores' => [
    'file' => ['driver' => 'file', 'path' => storage_path('framework/cache/data')],
    'array' => ['driver' => 'array', 'serialize' => false],
], 'prefix' => 'it_stock_cache'];
