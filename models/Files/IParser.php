<?php declare(strict_types = 1);
namespace app\models\Files;

interface IParser
{
    public function parse(): array;
}