<?php
// include "CustomBootstrap.php";

use PHPUnit\Framework\TestCase;

use \Emotion\Configuration\File\MockFileProvider;
use \Emotion\Configuration\File\FileProvider;

use \Emotion\Configuration\ConfigurationBuilder;
use \Emotion\Configuration\ConfigurationRoot;

use \Emotion\Configuration\Json\JsonConfigurationSource;
use \Emotion\Configuration\Memory\MemoryConfigurationSource;

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

    public function testInit4() {
        $fi = new FileProvider("app.json", "tests/");
        $fi2 = new FileProvider("composer.json");

        // Genero un nuevo objeto de configuración y le asigno un archivo.
        $source = new JsonConfigurationSource();
        $source->fileProvider = $fi;

        $source2 = new JsonConfigurationSource();
        $source2->fileProvider = $fi2;

        // La agrego al constructor.
        $builder = new ConfigurationBuilder();
        $builder->add($source);
        $builder->add($source2);

        // Genero la configuración.
        $configRoot = $builder->build();

        $this->assertEquals("emotion/emotion", $configRoot->getValue("name"));
        $this->assertEquals("tests", $configRoot->getValue("src"));
    }

    public function testInit5() {
        $fi = new FileProvider("app.json", "tests/");
        $fi2 = new FileProvider("composer.json");

        // Genero un nuevo objeto de configuración y le asigno un archivo.
        $source = new JsonConfigurationSource();
        $source->fileProvider = $fi;

        $source2 = new JsonConfigurationSource();
        $source2->fileProvider = $fi2;

        $source3 = new MemoryConfigurationSource();
        $source3->config = array("b" => 1);


        // La agrego al constructor.
        $builder = new ConfigurationBuilder();
        $builder->add($source);
        $builder->add($source2);
        $builder->add($source3);

        // Genero la configuración.
        $configRoot = $builder->build();

        $this->assertEquals("emotion/emotion", $configRoot->getValue("name"));
        $this->assertEquals("tests", $configRoot->getValue("src"));
        $this->assertEquals(1, $configRoot->getValue("b"));
    }
}