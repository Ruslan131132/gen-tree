<?php

namespace App\Tests\Service;

use App\Service\GenTreeGenerator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GenTreeGeneratorTest extends KernelTestCase
{
    public function testJsonGenerating()
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        // (3) run some service & test the result
        $genTreeGenerator = $container->get(GenTreeGenerator::class);

        $dir = $container->getParameter('kernel.project_dir') . '/public/tests/';

        $input = $this->createTestCsv($dir);

        $output = $dir . 'output.json';

        $genTreeGenerator->generateJSON($input, $output);

        $generatedData = json_decode(file_get_contents($output), true);

        $needData = [
            [
                'itemName' => "Total",
                'parent' => null,
                'children' => [
                    [
                        'itemName' => "ПВЛ",
                        'parent' => "Total",
                        'children' => []
                    ]
                ]
            ]
        ];

        $this->assertEquals($generatedData, $needData);
    }

    public function testCsvReading()
    {
        self::bootKernel();

        $container = static::getContainer();

        $genTreeGenerator = $container->get(GenTreeGenerator::class);

        $dir = $container->getParameter('kernel.project_dir') . '/public/tests/';

        $input = $this->createTestCsv($dir);

        $readData = $genTreeGenerator->readFromCsv($input);

        $needData = [
            [
                'itemName' => "Total",
                'type' => "Изделия и компоненты",
                'parent' => null,
                'relation' => null,
                'children' => []
            ],
            [
                'itemName' => "ПВЛ",
                'type' => "Изделия и компоненты",
                'parent' => "Total",
                'relation' => null,
                'children' => []
            ]
        ];

        $this->assertEquals($readData, $needData);
    }

    /**
     * Вспомогательный метод для создания csv-файла
     */
    private function createTestCsv(string $dir): string
    {
        $path = $dir . 'input.csv';
        $handle = fopen($path, "w");
        fwrite($handle, '"Item Name";"Type";"Parent";"Relation"' . PHP_EOL . '"Total";"Изделия и компоненты";;' . PHP_EOL . '"ПВЛ";"Изделия и компоненты";"Total";' . PHP_EOL);
        fclose($handle);

        return $path;
    }
}