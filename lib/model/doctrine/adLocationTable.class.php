<?php

/**
 * adLocationTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class adLocationTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object adLocationTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('adLocation');
    }

    public static function getAllLocation()
    {
        return adLocationTable::getInstance()->createQuery()
            ->fetchArray();
    }
}