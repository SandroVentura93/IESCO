<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
$view = $app['view'];
$path = $view->getFinder()->find('layouts.app');
$contents = file_get_contents($path);
$compiler = $app['blade.compiler'];
$compiled = $compiler->compileString($contents);
// print portion around the nav else
$lines = explode("\n", $compiled);
for ($i=1;$i<=200;$i++) {
    $ln = $i;
    if (isset($lines[$ln-1])) echo sprintf("%4d: %s\n", $ln, $lines[$ln-1]);
}
