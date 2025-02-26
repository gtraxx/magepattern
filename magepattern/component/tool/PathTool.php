<?php
namespace Magepattern\Component\Tool;
use Magepattern\Component\Debug\Logger;

class PathTool
{
    /**
     * @static
     * @param array $extendSearch
     * @return bool|string
     * @example :
        filesystem_path::basePath(
            array('component','filesystem')
        );
     */
    public static function basePath(array $extendSearch = []): bool|string
    {
        try {
            $search = array_merge(['component','tool'], $extendSearch, [DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR]);

            if(count($search) > 0) $replace = array_fill(0,count($search),'');
            else throw new \Exception('Error replace : internal params is null',E_WARNING);

            $pathreplace = str_replace($search, $replace, __DIR__);
            $pathclean = strrpos($pathreplace,DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) ? substr($pathreplace, -1) : $pathreplace;
            return strrpos($pathclean,DIRECTORY_SEPARATOR, strlen($pathclean) -1) ? $pathclean : $pathclean.DIRECTORY_SEPARATOR;
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e);
            return false;
        }
    }

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     * @return bool
     */
    public static function isAbsolutePath(string $file): bool
    {
        return (strspn($file, '/\\', 0, 1)
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && substr($file, 1, 1) === ':'
                && (strspn($file, '/\\', 2, 1))
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        );
    }
}