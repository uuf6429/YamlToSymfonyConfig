<?php

header('Content-Type: application/json');

require_once(__DIR__ . '/../vendor/autoload.php');

try {
    $yamls = array_map(
        [\Symfony\Component\Yaml\Yaml::class, 'parse'],
        isset($_REQUEST['yaml']) ? $_REQUEST['yaml'] : []
    );

    $phpWriter = new PhpWriter();
    $converter = new ConfigConverter();
    $converter->from($yamls)->to($phpWriter);

    $result = ['code' => $phpWriter->toString()];
} catch (\Exception $ex) {
    $result = ['error' => (string)$ex];
}

echo json_encode($result);
