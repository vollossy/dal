<?php
namespace vollossy\DAL;
use vollossy\DAL\exceptions\CollectionException;

/**
 * Базовый класс для коллекций. Предоставляет функционал по преобразованию данных, полученных от \PDOStatement::fetchAll()
 * в объекты класса DAL
 */
abstract class Collection implements \Iterator
{
    /**
     * @var array|null Массив данных, полученных от \PDOStatement::fetchAll()
     */
    protected $raw = array();
    /**
     * @var int Указатель на индекс текущего элемента коллекции
     */
    protected $pointer = 0;

    /**
     * @var DAL[] Массив уже преобразованных объектов
     */
    protected $objects = array();

    /**
     * @var null|DAL Объект, предоставляющий функционал по преобразованию записи в таблице в экземпляр класс DAL
     */
    protected $mapper;

    /**
     * @param array|null $raw
     * @param null|DAL $dalInstance
     */
    public function __construct(array $raw = null, DAL $dalInstance = null)
    {
        if (!is_null($raw) && !is_null($dalInstance)) {
            $this->raw = $raw;
        }
        $this->mapper = $dalInstance;
    }

    /**
     * Добавляет элемент к коллекции элемент, проверяя его тип, указанный в методе Collection::getTarget()
     * @param DAL $item Элемент для вставки в коллекция
     * @throws CollectionException Выбрасывается, если класс добавляемого элемента не соответствует указанному в методе
     * Collection::getTarget();
     */
    public function add(DAL $item)
    {
        $this->notifyAccess();
        $class = $this->targetClass();
        if(! ($item instanceof $class))
            throw new CollectionException("Параметр должен быть экземпляром класса '{$class}'");
        $this->objects[] = $item;
    }

    /**
     * Получает элемент с указанным индексом из коллекции
     * @param $index Индекс элемента
     * @return null|DAL
     */
    protected function getRow($index){
        $this->notifyAccess();
        if(isset($this->objects[$index])){
            return $this->objects[$index];
        }
        if(isset($this->raw[$index])){
            $this->objects[$index] = $this->mapper->mapObject($this->raw[$index]);
            return $this->objects[$index];
        }
        return null;
    }

    /**
     * Функция, вызываемая при каждом обращении к коллекции. Ее нужно переопределеить для реализации ленивой загрузки
     */
    protected  function notifyAccess(){

    }

    /**
     * Возвращает класс, экземпляры которого могут быть добавлены к коллекции
     * @abstract
     */
    abstract function targetClass();

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->getRow($this->pointer);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return DAL Any returned value is ignored.
     */
    public function next()
    {
        $row = $this->getRow($this->pointer);
        if($row){
            $this->pointer++;
        }
        return $row;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return int scalar on success, integer
     * 0 on failure.
     */
    public function key()
    {
        return $this->pointer;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return (! is_null($this->current()));
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->pointer = 0;
    }
}