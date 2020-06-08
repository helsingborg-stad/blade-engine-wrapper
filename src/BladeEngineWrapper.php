<?php 

namespace BladeEngineWrapper;

use BC\Blade\Blade as BladeInstance;

class BladeEngineWrapper {

    private $viewPaths = [];
    private $cachePath;

    public function __construct()
    {
        $this->cachePath = (string) sys_get_temp_dir() . '/blade-engine-cache';
    }

    /**
     * Gets previous instance, or create a new if empty/invalid 
     * 
     * @return object The blade obect 
     */
    public function instance() {


        //Require view paths
        if(empty($this->getViewPaths())) {
            throw new \Exception("Error: View paths must be defined before running init.");
        }

        //Clear cache on local instance
        $this->maybeClearCache(); 

        //Create cache path
        $this->createComponentCachePath(); 

        //Create new blade instance
        $bladeEngineInstance = new BladeInstance(
            (array) $this->getViewPaths(),
            (string) sys_get_temp_dir() . '/global-blade-engine-cache'
        );

        return $bladeEngineInstance; 
    }

    /**
     * Appends/prepends the view path object 
     * 
     * @return string The updated object with controller paths
     */
    public function addViewPath($path, $prepend = true) : array 
    {

        //Sanitize path
        $path = rtrim($path, "/");

        //Push to location array
        if($prepend === true) {
            if (array_unshift($this->viewPaths, $path)) {
                return $this->viewPaths;
            }
        } else {
            if (array_push($this->viewPaths, $path)) {
                return $this->viewPaths;
            }
        }
        
        //Error if something went wrong
        throw new \Exception("Error appending controller path: " . $path);
    }

    /**
     * Gets the view paths as array 
     * 
     * @return string The updated object with controller paths
     */
    public function getViewPaths() : array 
    {
        return $this->viewPaths;
    }

    /**
     * Create a cache dir
     *
     * @return string Local path to the cache path
     */
    private function createComponentCachePath() : string
    {
        if (!file_exists($this->cachePath)) {
            if (!mkdir($this->cachePath, 0764, true)) {
                throw new \Exception("Could not create cache folder: " . $this->cachePath);
            }
        }

        return (string) $this->cachePath;
    }

    /**
     * Clears blade cache if in dev domain
     *
     * @return boolean True if cleared, false otherwise
     */
    private function maybeClearCache($objectPath = null)
    {
        if(defined('GLOBAL_BLADE_ENGINE_CLEAR_CACHE')  && GLOBAL_BLADE_ENGINE_CLEAR_CACHE === true){

            $dir = rtrim($this->cachePath, "/") . DIRECTORY_SEPARATOR; 

            if (is_dir($dir)) { 

                $objects = array_diff(scandir($dir), array('..', '.'));

                if(is_array($objects) && !empty($objects)) {

                    foreach ($objects as $object) {
                        $objectPath = $dir."/".$object;

                        if(is_dir($objectPath)) {
                            $this->maybeClearCache($objectPath); 
                        } else {
                            unlink($objectPath);
                        }
                    }
                }

                rmdir($dir); 
            }
        }
        
        return false; 
    }

}