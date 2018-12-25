<?php
namespace app\api\view\Address;

use app\api\models\Suggestion;
use app\api\models\Suggestion\Data;

class Suggestions
{
    /** @var array */
    private $suggestions;

    /**
     * @return array
     */
    public function build()
    {
        $result = [];
        if ($this->suggestions) {
            foreach ($this->suggestions as $suggestion) {
                $result[] = (new \app\api\builder\Suggestion($suggestion))->getSuggestion();
            }
        }

        return $result;
    }

    /**
     * @param array $suggestions
     * @return $this
     */
    public function setSuggestions(array $suggestions)
    {
        $this->suggestions = $suggestions;
        return $this;
    }
}