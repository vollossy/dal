<?php
namespace vollossy\DAL;
use vollossy\DAL\exceptions\DALException;

/**
 * Class DAL
 * Базовый класс для работы с БД
 * @package vollossy\DAL
 */
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUndefinedClassInspection */
abstract class DAL {
    /**
     * @var bool Флаг, указывающий, является ли текущий экземпляр вновь созданным.
     */
    public $isNew = true;

    /**
     * @var string Имя первичного ключа для таблицы, представляющей текущий объект
     */
    public $pk = 'id';

    /**
     * @var \PDO соединение с базой данных
     */
    protected $db;

    /** @todo: нужно бы добавить репозиторий для конфигурации */
    public function __construct()
    {
        $this->db = new \PDO(
            ConfigRegistry::getInstance()->getDsn(),
            ConfigRegistry::getInstance()->getUsername(),
            ConfigRegistry::getInstance()->getPassword(),
            array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')
        );
    }

    /**
     * Поиск всех данных в таблице
     * @param Criteria $criteria Условия фильтрации
     * @return Collection
     * @throws DALException
     */
    public function findAll(Criteria $criteria = null)
    {
        $criteriaStr = '';
        if($criteria){
            $criteriaStr = $criteria->getCriteriaString();
        }
        $stmt = $this->db->prepare("SELECT * FROM {$this->tableName()} {$criteriaStr}");

        $collectionClassName = $this->collectionClass();
        return new $collectionClassName($this, $stmt);
    }

    /**
     * Преобразует данные, полученны от PDO в экземпляр класса
     * @param $record
     * @return DAL
     */
    public function mapObject($record)
    {
        $resultReflection = new \ReflectionClass(get_class($this));
        $result = $resultReflection->newInstance();
        foreach ($record as $key => $value) {
            if (!is_numeric($key))
                $result->$key = $value;
        }
        return $result;
    }

    /**
     * Ищет запись по значению атрибута
     * @param $attributeName Имя атрибута
     * @param $attributeValue Значение атрибута
     * @return DAL
     * @throws DALException
     */
    public function findByAttribute($attributeName, $attributeValue)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tableName()} WHERE {$attributeName} = {$attributeValue}");
        if ($stmt->execute()) {
            return $this->mapObject($stmt->fetch());
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new DALException($errorInfo);
        }
    }

    /**
     * Сохраняет экземпляр класса в БД
     * @return bool
     * @throws DALException
     */
    public function save()
    {
        $values = $this->prepareSaveValuesString();
        if ($this->isNew) {
            $query = "INSERT INTO {$this->tableName()} SET {$values}";
            $stmt = $this->db->prepare($query);
        } else {
            $query = "UPDATE {$this->tableName()} SET {$values}";
            $query .= $this->prepareSaveValuesString();
            $pk = $this->pk;
            $query .= "WHERE {$this->pk} = {$this->$pk}";
            $stmt = $this->db->prepare($query);
        }
        if ($stmt->execute($this->prepareSaveData())) {
            if($this->isNew){
                $pk = $this->pk;
                $this->$pk = $this->db->lastInsertId();
            }
            return true;
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new DALException($errorInfo[2]);
        }
    }

    /**
     * Подготавливает данные для вставки в запрос посредством PDO
     * @return array Ассоциативный массив для PDO-выражения
     */
    protected function prepareSaveData(){
        $availableColumns = $this->getColumns();
        $result = array();
        foreach ($availableColumns as $columnName) {
            if(isset($this->$columnName) && $columnName != $this->pk){
                $result[$columnName] = $this->$columnName;
            }
        }
        return $result;
    }

    /**
     * Подготавливает строку для выражений update и insert
     * @return string
     */
    protected  function prepareSaveValuesString()
    {
        $availableColumns = $this->getColumns();
        $c = 0;
        $query = '';
        foreach ($availableColumns as $columnName) {
            if (isset($this->$columnName) && $columnName != $this->pk) {
                $query .= ($c > 0) ? ',' : '';
                $query .= "{$columnName} = :{$columnName} ";
                $c++;
            }
        }
        return $query;
    }

    /**
     * Получает массив с именами колонок в текущей таблице
     * @return array
     */
    private function getColumns()
    {
        $stmt = $this->db->prepare("SHOW COLUMNS FROM :table", array(':table' => $this->tableName()));
        $result = array();
        if ($stmt->execute()) {
            $fetchedColumns = $stmt->fetchAll();
            foreach ($fetchedColumns as $fetchedColumn) {
                $result[] = $fetchedColumn["Field"];
            }
        }
        return $result;
    }

    /**
     * @abstract
     * Имя таблицы для текущего класса
     */
    abstract protected function tableName();

    /**
     * Возвращает имя класса коллекции.
     * @return string
     */
    abstract protected function collectionClass();
}