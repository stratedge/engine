<?php
namespace Stratedge\Engine\Console;

class Config
{
    protected static $default_node_namespace;
    protected static $default_node_directory;
    protected static $default_edge_namespace;
    protected static $default_edge_directory;


    /**
     * @param  array  $data
     * @return null
     */
    public static function parse(array $data = [])
    {
        if (!empty($data['default']['node']['namespace'])) {
            self::setDefaultNodeNamespace($data['default']['node']['namespace']);
        }

        if (!empty($data['default']['node']['directory'])) {
            self::setDefaultNodeDirectory($data['default']['node']['directory']);
        }

        if (!empty($data['default']['edge']['namespace'])) {
            self::setDefaultEdgeNamespace($data['default']['edge']['namespace']);
        }

        if (!empty($data['default']['edge']['directory'])) {
            self::setDefaultEdgeDirectory($data['default']['edge']['directory']);
        }
    }


    /**
     * @return string|null
     */
    public static function getDefaultNodeNamespace()
    {
        return self::$default_node_namespace;
    }


    /**
     * @param  string $namespace
     * @return null
     */
    public static function setDefaultNodeNamespace($namespace)
    {
        self::$default_node_namespace = $namespace;
    }

    
    /**
     * @return string|null
     */
    public static function getDefaultNodeDirectory()
    {
        return self::$default_node_directory;
    }


    /**
     * @param  string $directory
     * @return null
     */
    public static function setDefaultNodeDirectory($directory)
    {
        if (substr($directory, -1) !== '/') {
            $directory .= '/';
        }

        self::$default_node_directory = $directory;
    }


    /**
     * @return string|null
     */
    public static function getDefaultEdgeNamespace()
    {
        return self::$default_edge_namespace;
    }


    /**
     * @param  string $namespace
     * @return null
     */
    public static function setDefaultEdgeNamespace($namespace)
    {
        self::$default_edge_namespace = $namespace;
    }

    
    /**
     * @return string|null
     */
    public static function getDefaultEdgeDirectory()
    {
        return self::$default_edge_directory;
    }


    /**
     * @param  string $directory
     * @return null
     */
    public static function setDefaultEdgeDirectory($directory)
    {
        if (substr($directory, -1) !== '/') {
            $directory .= '/';
        }
        
        self::$default_edge_directory = $directory;
    }
}
