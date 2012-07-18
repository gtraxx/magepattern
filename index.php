<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 17/06/12
 * Time: 17:50
 *
 */
require dirname(__FILE__).'/_autoload.php';
if(http_request::isGet('machin')){
    $date = new date_dateformat();
    $datestart = $date->dateDefine('2012-01-01');
    $interval = $date->setInterval('2012-01-01','D');
    $dateend = $date->add(
        array('interval'=>$interval,'type'=>'object'),
        'Y-m-d',
        '2012-01-30'
    );
    print $date->getStateDiff($dateend,$datestart);
}
// Database driver (mysql, pgsql)
define('MP_DBDRIVER','mysql');

// Database hostname (usually "localhost")
define('MP_DBHOST','localhost');

// Database user
define('MP_DBUSER','root');

// Database password
define('MP_DBPASSWORD','root');

// Database name
define('MP_DBNAME','test');

// Path for error log
define('MP_TMP_DIR','/Applications/MAMP/htdocs/magepattern/test');

$color = '';
/*$db = new db_layer();
$sql =  'SELECT id, color FROM fruit';
foreach  ($db->fetchAll($sql) as $row) {
    $color.= $row['color'];
}
print $color.'<br />';
*/
$id=1;
$db = new db_layer();
$sql =  'SELECT id, color
        FROM fruit
        WHERE id = ?';
foreach  ($db->fetchAll($sql,array($id)) as $row) {
    $color.= $row['color'];
}
print $color.'<br />';
print filesystem_path::basePath();
//prunt truyc;
//$truc = new autoloader();
/*$truc->registerPrefixFallback(__DIR__.'/truc');
print_r($truc->getPrefixFallbacks());
$truc->register();*/
/*$test = new test();
$test->mafonction();*/