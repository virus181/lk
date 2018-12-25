<?php

namespace app\delivery\apiship\orders;

class Item
{
    /** @var string */
    public $articul;
    /** @var string */
    public $description;
    /** @var integer */
    public $quantity;
    /** @var integer */
    public $height = 0;
    /** @var integer */
    public $length = 0;
    /** @var integer */
    public $width = 0;
    /** @var integer */
    public $weight = 0;
    /** @var float */
    public $assessedCost;
    /** @var float */
    public $cost;
    /** @var integer */
    public $costVat;
    /** @var string */
    public $barcode;
}