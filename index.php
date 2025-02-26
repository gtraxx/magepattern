<?php
/**
 * Chargement du bootstrap
 */
$bootstrap = __DIR__.'/magepattern/bootstrap.php';
//print $bootstrap;
if (file_exists($bootstrap)){
    require $bootstrap;
}else{
    throw new Exception('Boostrap is not exist');
}
use Magepattern\Component\HTTP\Request,
    Magepattern\Component\HTTP\Url,
    Magepattern\Component\File\Finder;
//print Url::getUrl();
print_r(Finder::dirFilterIterator('test',['jpeg']));
print Finder::sizeDirectory(__DIR__.'/test');
?>