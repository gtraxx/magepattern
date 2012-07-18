<?php
class autoloader{
    /**
     * CONSTANTE PREFIX SEPARATOR
     */
    CONST PREFIX_SEPARATOR = '_';
    /**
     * @var array $prefixes
     */
    private $prefixes = array();
    /**
     * @var array $prefixFallbacks
     */
    private $prefixFallbacks = array();

    /**
     * Gets the configured class prefixes.
     *
     * @return array A hash with class prefixes as keys and directories as values
     */
    public function getPrefixes()
    {
        return $this->prefixes;
    }

    /**
     * Gets the directory(ies) to use as a fallback for class prefixes.
     *
     * @return array An array of directories
     */
    public function getPrefixFallbacks()
    {
        return $this->prefixFallbacks;
    }

    /**
     * Registers directories to use as a fallback for class prefixes.
     *
     * @param array $dirs An array of directories
     *
     * @throws Exception
     * @api
     */
    public function registerPrefixFallbacks(array $dirs)
    {
        if (!is_array($dirs)) {
            throw new Exception('registerPrefixFallbacks : dirs is not array');
        }
        $this->prefixFallbacks = $dirs;
    }

    /**
     * Registers a directory to use as a fallback for class prefixes.
     *
     * @param string $dir A directory
     */
    public function registerPrefixFallback($dir)
    {
        $this->prefixFallbacks[] = $dir;
    }

    /**
     * Registers an array of classes using the PEAR naming convention.
     *
     * @param array $classes An array of classes (prefixes as keys and locations as values)
     *
     * @throws Exception
     * @api
     */
    public function registerPrefixes(array $classes)
    {
        if (!is_array($classes)) {
            throw new Exception('Prefix pairs must be either an array or Traversable');
        }
        foreach ($classes as $prefix => $locations) {
            $this->prefixes[$prefix] = (array) $locations;
        }
    }

    /**
     * Registers a set of classes using the PEAR naming convention.
     *
     * @param string       $prefix  The classes prefix
     * @param array|string $paths   The location(s) of the classes
     *
     * @api
     */
    public function registerPrefix($prefix, $paths)
    {
        $prefix = rtrim($prefix, self::PREFIX_SEPARATOR). self::PREFIX_SEPARATOR;
        $this->prefixes[$prefix] = (array) $paths;
    }

    /**
     * Registers this instance as an autoloader.
     *
     * @param Boolean $prepend Whether to prepend the autoloader or not
     *
     * @api
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class The name of the class
     */
    private function loadClass($class){
    	$file = $this->findFile($class);
        if ($file) {
            require $file;
        }
    }

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return string|null The path, if found
     */
    public function findFile($class){
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }
        // PEAR-like class name
        $normalizedClass = str_replace(self::PREFIX_SEPARATOR, DIRECTORY_SEPARATOR, $class).'.php';
        foreach ($this->prefixes as $prefix => $dirs) {
             if (0 !== strpos($class, $prefix)) {
             	continue;
             }
             foreach ($dirs as $dir) {
                 $file = $dir.DIRECTORY_SEPARATOR.$normalizedClass;
                 if (is_file($file)) {
                      return $file;
                 }
             }
         }
         foreach ($this->prefixFallbacks as $dir) {
            $file = $dir.DIRECTORY_SEPARATOR.$normalizedClass;
            if (is_file($file)) {
            	return $file;
            }
        }
	}
}
?>