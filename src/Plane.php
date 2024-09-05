<?php

namespace Gendiff\Plane;

use function Functional\sort;

function assemblePath($path)
{
    if (file_exists($path)) {
        return $path;
    }
    return __DIR__ . "/../{$path}";
}

function toString($value)
{
    return trim(var_export($value, true), "'");
}

function genDiff($path1, $path2)
{
    $pathToFile1 = assemblePath($path1);
    $pathToFile2 = assemblePath($path2);

    $jsonStr1 = file_get_contents($pathToFile1);
    $jsonStr2 = file_get_contents($pathToFile2);

    $array1 = json_decode($jsonStr1, true); // associative array
    $array2 = json_decode($jsonStr2, true); // associative array

    $keys1 = array_keys($array1);
    $keys2 = array_keys($array2);

    $uniqueKeys = array_unique([...$keys1, ...$keys2]);
    $sortedKeys = sort($uniqueKeys, fn($first, $second) => $first <=> $second);

    $lines = array_reduce($sortedKeys, function ($acc, $key) use ($array1, $array2) {
        $value1 = isset($array1[$key]) ? toString($array1[$key]) : null;
        $value2 = isset($array2[$key]) ? toString($array2[$key]) : null;


        if (!$value1 || !$value2) {
            $newValue = $value1 ? "  - {$key}: {$value1}" : "  + {$key}: {$value2}";
            return [...$acc, $newValue];
        } elseif ($value1 !== $value2) {
            $newValue = "  - {$key}: {$value1} \n  + {$key}: {$value2}";
            return [...$acc, $newValue];
        } else {
            $newValue = "    {$key}: {$value1}";
            return [...$acc, $newValue];
        }
    }, []);

    $result = implode("\n", ['{', ...$lines, '}']);
    print_r($result);
    print_r("\n");
}
