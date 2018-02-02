<?php
// include "CustomBootstrap.php";

use PHPUnit\Framework\TestCase;

use \Emotion\Configuration\ConfigurationBuilder;
use \Emotion\Configuration\ConfigurationRoot;
use \Emotion\Configuration\File\MockFileProvider;
use \Emotion\Configuration\File\FileProvider;
use \Emotion\Configuration\Json\JsonConfigurationProvider;
use \Emotion\Configuration\Json\JsonConfigurationSource;

class ConfigTest extends TestCase
{
    public function testInit() {
        $fi = new MockFileProvider("{\"a\": 1}");

        // Genero un nuevo objeto de configuración y le asigno un archivo.
        $source = new JsonConfigurationSource();
        $source->fileProvider = $fi;

        // La agrego al constructor.
        $builder = new ConfigurationBuilder();
        $builder->add($source);

        // Genero la configuración.
        $configRoot = $builder->build();

        $this->assertEquals(1, $configRoot->getValue("a"));
    }

    public function testInit2() {
        $fi = new FileProvider("app.json", "tests/");

        // Genero un nuevo objeto de configuración y le asigno un archivo.
        $source = new JsonConfigurationSource();
        $source->fileProvider = $fi;

        // La agrego al constructor.
        $builder = new ConfigurationBuilder();
        $builder->add($source);

        // Genero la configuración.
        $configRoot = $builder->build();

        $this->assertEquals("tests", $configRoot->getValue("src"));
    }

    public function testInit3() {
        $fi = new FileProvider("composer.json");

        // Genero un nuevo objeto de configuración y le asigno un archivo.
        $source = new JsonConfigurationSource();
        $source->fileProvider = $fi;

        // La agrego al constructor.
        $builder = new ConfigurationBuilder();
        $builder->add($source);

        // Genero la configuración.
        $configRoot = $builder->build();

        $this->assertEquals("emotion/emotion", $configRoot->getValue("name"));
    }
}