<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 17/06/12
 * Time: 17:50
 *
 */
require dirname(__FILE__).'/_autoload.php';
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

// DEBUG ('debug' OR 'log' OR false)
define('MP_LOG','debug');

// Path for error log
define('MP_LOG_DIR','/Applications/MAMP/htdocs/magepattern/test');

// FirePHP (false or true)
define('MP_FIREPHP',true);

if(defined('MP_LOG')){
    if(MP_LOG == 'debug'){
        $dis_errors = 1;
    }elseif(MP_LOG == 'log'){
        $dis_errors = 1;
    }else{
        $dis_errors = 0;
    }
    ini_set('display_errors', $dis_errors);
}
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
/*
$string = 'monmailmonadresse';
$input = new form_inputfilter();
if($input->isNumeric($string)){
    print $string;
}
$hashstring =  'monpassword';
print filter_rsa::hashEncode('md5',$hashstring).'<br/>';
print filter_rsa::hashEncode('md5_base64',$hashstring).'<br/>';
print filter_rsa::hashEncode('sha1',$hashstring).'<br/>';
print filter_rsa::tokenID();*/
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}
/*$firephp = new debug_firephp();
$firephp->log('test');*/
/*$sql = 'INSERT INTO fruit (name,color) VALUE(:name,:color)';
$db->insert(
    $sql,array(':name'=>'couleur verte avec évaluation',':color'=>'vert')
);*/
/*$sql = 'DELETE FROM fruit WHERE color = "vert"';
$db->transaction(array($sql));*/
//$makefile->mkdir(array($root."/baz"));
//$makefile->remove(array($root."/truc",$root."/machin"));
/*$root =  filter_path::basePath();
$form = new form_input();
print $form->field('myfield',30,30,'','myclass')."\n";
print $form->textArea('myfield',20,30,'Default text','myclass')."\n";
*/

$db = new db_layer();
$sql =  'SELECT id, color FROM fruit';
/*foreach  ($db->fetchAll($sql) as $row) {
    $color.= $row['color'].'<br />';
}
print $color.'<br />';*/
$fetch = $db->fetchAll($sql);
$option = '';
foreach($fetch as $value){
    $id[] = $value['id'];
    $color[] = $value['color'];
}
$selectcolor = array_combine($id,$color);
/*$firephp = new debug_firephp();
$firephp->log($conbine);*/
$form = new form_input();
print $form->select(array('monselect','myselect1'),$selectcolor,array('class'=>'montest'));
print $form->checkbox('macheckbox','montest');
print $form->radio(array('radio','radio1'),'montest',array('class'=>'montest'));
print filter_path::basePath();
/*$select =  $db->fetch('SELECT id, color,name FROM fruit');
print $select['name'].'<br />';*/
/*$id=1;
$db = new db_layer();
$sql =  'SELECT id, color
        FROM fruit
        WHERE id = ?';
foreach  ($db->fetchAll($sql,array($id)) as $row) {
    $color.= $row['color'];
}
print $color.'<br />';*/
/*
/*$makefile = new filesystem_makefile();
$makefile->rename(array(
    $root."/super"=>$root."/truc"
));*/
//$makefile->copy($root."/truc/montest.txt",$root."/machin/montest.txt");
//prunt truyc;
//$truc = new autoloader();
/*$truc->registerPrefixFallback(__DIR__.'/truc');
print_r($truc->getPrefixFallbacks());
$truc->register();*/
/*$test = new test();
$test->mafonction();*/
?>