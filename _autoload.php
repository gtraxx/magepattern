<?php
final class magepattern_bootstrap{
    /**
     * @var bool
     */
    private static $_instance = false;
    /**
     * On indique que le constructeur est privé pour éviter
     * toute instanciation non maîtrisée
     */
    private function __construct(){}
    /**
     * On évite tout clonage pour ne pas avoir deux instances en //
     */
    private function __clone(){}
    /**
     * C'est la méthode qui "remplace" le constructeur vis à vis
     * des autres classes.
     *
     * Son rôle est de créer / distribuer une unique
     * instance de notre objet.
     */
    public static function getInstance(){
        //Si l'instance n'existe pas encore, alors elle est créée.
        if (self::$_instance === false){
            self::$_instance = new self;
        }
        //L'instance existe, on peut la retourner à l'extérieur.
        return self::$_instance;
    }
    /**
     * Constante Path swiftmailer lib
     */
    const PATH_SWIFTMAILER = '/package/Swift-4.1.8/lib/swift_required.php';
    /**
     * Constante Path Autoloader magepattern
     */
    const PATH_AUTOLOADER = '/loader/autoloader.php';
    /**
     * Constante Path Firephp
     */
    const PATH_FIREPHP = '/package/firephp-1.0/FirePHP/Init.php';
    /**
     * @access private
     * @return array
     */
    private function arrayLibFiles(){
        return array(
            'autoloader' => __DIR__.self::PATH_AUTOLOADER,
            'firephp'    => __DIR__.self::PATH_FIREPHP,
            'swift'      => __DIR__.self::PATH_SWIFTMAILER
        );
    }
    /**
     * @access private
     * @throws Exception
     */
    private function getFilesRequire(){
        $setLibOption = self::arrayLibFiles();
        if(is_array($setLibOption)){
            foreach($setLibOption as $key => $value){
                if (file_exists($value)){
                    require $value;
                }else{
                    throw new Exception("not file exists for ".$key);
                }
            }
        }
    }
    /**
     * @access private
     * @param $setClassLoader
     */
    public function getClassAutoloader($setClassLoader){
        if($this->getFilesRequire() != false){
           $this->getFilesRequire();
        }
        if(is_array($setClassLoader)){
            //$mp_autoloader = dirname(__FILE__).'/component/loader/autoloader.php';
            $loader = new autoloader();
            $loader->registerPrefixFallbacks($setClassLoader);
            $loader->register();
        }
    }
}
/**
 * Chargement des classes + des librairies
 */
magepattern_bootstrap::getInstance()->getClassAutoloader(array(
    'loader'     =>  __DIR__.'/loader',
    'component'  =>  __DIR__.'/component'
));
?>