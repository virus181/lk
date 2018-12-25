<?php

namespace app\behaviors;

interface SaverInterface
{
    public function getModel();

    public function setModel($model);

    public function getOwner();

    public function setOwner($owner);

    public function save($validate);
}