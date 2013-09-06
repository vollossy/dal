<?php
/**
 * Created by IntelliJ IDEA.
 * User: roman
 * Date: 06.09.13
 * Time: 15:39
 * To change this template use File | Settings | File Templates.
 */

namespace vollossy\DAL;

/**
 * Class Notifiable
 * Интерфейс, обеспечивающий реализацию notifyAccess. Применяется к коллекциям для реализации ленивой загрзуки
 * @package vollossy\DAL
 */
interface Notifiable{
    /**
     * @param DAL $dalInstance
     * @param \PDOStatement $stmt
     */
    public function __construct(DAL $dalInstance, \PDOStatement $stmt);

}