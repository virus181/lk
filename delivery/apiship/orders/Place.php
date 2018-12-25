<?php

namespace app\delivery\apiship\orders;

class Place
{
    /** @var string */
    public $placeNumber;
    /** @var string */
    public $barcode;
    /** @var integer */
    public $height;
    /** @var integer */
    public $length;
    /** @var integer */
    public $width;
    /** @var integer */
    public $weight;
    /** @var Item[] */
    public $items = [];
}