<?php
namespace vollossy\DAL;

/**
 * Class Criteria
 * Базовый класс для построения условий для запросов
 * @package vollossy\DAL
 */
class Criteria
{
    protected $limit = 0;
    protected $offset = 0;

    public function getCriteriaString()
    {
        $result = "";
        if($this->limit){
            $result = "LIMIT {$this->offset}, {$this->limit}";
        }

        return $result;
    }

    public function setLimit($limit)
    {
        if (!is_numeric($limit))
            throw new \UnexpectedValueException("Значение Limit должно быть числом");
        $this->limit = $limit;
    }

    public function setOffset($offset)
    {
        if (!is_numeric($offset))
            throw new \UnexpectedValueException("Значение Offset дожно быть числом");
        $this->offset = $offset;
    }


}
