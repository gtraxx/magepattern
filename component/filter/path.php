<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 18/07/12
 * Time: 23:19
 *
 */
class filter_path{
    /**
     * @static
     * @param array $tabsearch
     * @param array $tabreplace
     * @return mixed|string
     * @example :
         filesystem_path::basePath(
            array('component','filesystem'),
            array('','')
         );
     */
    public static function basePath($tabsearch=array('component','filter'),$tabreplace=array('','')){
        try{
            if($tabsearch != false){
                if(is_array($tabsearch)){
                    $search = array_merge($tabsearch,array(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR));
                }
            }else{
                $search = array_merge(explode(DIRECTORY_SEPARATOR,__DIR__),array(DIRECTORY_SEPARATOR));
            }

            if($tabreplace != false){
                if(is_array($tabreplace)){
                    $replace = $tabreplace;
                }
            }else{
                $replace = array('','');
            }
            $pathreplace = str_replace($search, $replace, __DIR__);
            if(strrpos($pathreplace,DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR)){
                $path = substr($pathreplace, -1);
            }else{
                $path = $pathreplace;
            }
            return $path;
        }catch(Exception $e) {
            $logger = new debug_logger(MP_TMP_DIR);
            $logger->log('php', 'error', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }
}
?>