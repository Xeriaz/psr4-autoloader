<?php

class Psr4Autoloader
{
    private $prefixes = [];

    public function add(string $namespace_prefix, string $path): self
    {
        $namespace_prefix = trim($namespace_prefix, '\\') . '\\';

        if (false === isset($this->prefixes[$namespace_prefix])) {
            $this->prefixes[$namespace_prefix] = [];
        }

        array_push($this->prefixes[$namespace_prefix], $path);

        return $this;
    }

    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function loadClass(string $class)
    {
        $classPrefix = $class;

        while (false !== $pos = strrpos($classPrefix, '\\')) {
            $classPrefix = substr($class, 0, $pos + 1);
            $relative_class = substr($class, $pos + 1);
            $mapped_file = $this->loadMappedFile($classPrefix, $relative_class);

            if ($mapped_file) {
                return $mapped_file;
            }

            $classPrefix = rtrim($classPrefix, '\\');
        }
        return false;
    }

    protected function loadMappedFile(string $prefix, string $relative_class)
    {
        if (false === isset($this->prefixes[$prefix])) {
            return false;
        }

        foreach ($this->prefixes[$prefix] as $base_dir) {

            $file = $base_dir
                . str_replace('\\', '/', $relative_class)
                . '.php';

            if ($this->requireFile($file)) {
                return $file;
            }
        }
        return false;
    }

    protected function requireFile(string $file): bool
    {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }
}

$autoloader = new Psr4Autoloader();
$autoloader
    ->add('Nfq\\Academy\\Homework\\', __DIR__.'/src/')
    ->add('Nfq\\Academy\\Homework\\Subpackage', __DIR__.'/src/Subpackage')
    ->register();
