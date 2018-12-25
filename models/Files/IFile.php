<?php declare(strict_types = 1);
namespace app\models\Files;

interface IFile
{
    public function open(): bool;

    public function save(): bool;

    public function delete(): bool;

    public function validate(): bool;
}