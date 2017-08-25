<?php

define('LIB_DIR', './lib');
define('OUT_PATH', './out');
define('TMP_LIB_DIR', './tmp_lib');
define('ARCHIVE_NAME', 'lib.phar');

function cleanTabsAndNewLines($content) {
    $find = ["<?php", "\r", "\n", "\t", "<?", "    ", "   ", "  "];
    $replace = ["", "", "", "", "", " ", " ", " "];
    return "<?php " . str_replace($find, $replace, $content);
}

mkdir(TMP_LIB_DIR, 0777);
$iterator = new DirectoryIterator(LIB_DIR);

/**
 * @var $it DirectoryIterator
 */
foreach ($iterator as $it) {
    if ($it->isFile()) {
        file_put_contents(TMP_LIB_DIR . DIRECTORY_SEPARATOR . $it->getFilename(), cleanTabsAndNewLines(file_get_contents($it->getPathname())));
    }
}

$phar = new Phar(ARCHIVE_NAME, 0, ARCHIVE_NAME);
$phar->buildFromDirectory(TMP_LIB_DIR);

$tmpDirIterator = new DirectoryIterator(TMP_LIB_DIR);
foreach ($tmpDirIterator as $tmpFile) {
    if ($tmpFile->isFile()) {
        unlink($tmpFile->getPathname());
    }
}
rmdir(TMP_LIB_DIR);

mkdir(OUT_PATH, 0775);

$move = [ARCHIVE_NAME, "App.php", "conf.php", "run", "app.log"];
foreach ($move as $m) {
    copy($m, OUT_PATH . DIRECTORY_SEPARATOR . $m);
}
