<?php declare(strict_types = 1);
namespace app\models\Files;

use Yii;

class XML implements IFile, IParser
{
    const AVAILABLE_TYPES = ['Excel2007'];

    /** @var array */
    private $file;

    /** @var string */
    private $fileType;

    /**
     * XML constructor.
     *
     * @param array $file
     */
    public function __construct(array $file)
    {
        $this->file = $file;
        try {
            $this->fileType = \PHPExcel_IOFactory::identify($this->file['tmp_name']);
        } catch (\Exception $e) {
            $this->fileType = 'unknown';
        }

    }

    /**
     * @return array
     */
    public function parse(): array
    {
        try {
            $objReader = \PHPExcel_IOFactory::createReader($this->fileType);
            $objPhpExcel = $objReader->load($this->file['tmp_name']);
        } catch (\Exception $e) {
            return [];
        }

        $sheet = $objPhpExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $counter = Yii::$app->request->post('counter');
        for ($row = 1; $row <= $highestRow; $row++) {
            $counter++;
            $rowData = $sheet->rangeToArray(
                'A' . $row. ":" . $highestColumn. $row,
                NULL,
                true,
                false
            );

            if ($row == 1) {
                continue;
            }

            $product[$counter] = $rowData[0];
        }
        return $product ?? [];
    }

    public function open(): bool
    {
        // TODO: Implement open() method.
    }

    public function save(): bool
    {
        // TODO: Implement save() method.
    }

    public function delete(): bool
    {
        // TODO: Implement delete() method.
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        return in_array($this->fileType, self::AVAILABLE_TYPES);
    }
}