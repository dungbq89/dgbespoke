<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('adMassageComment', 'doctrine');

/**
 * BaseadMassageComment
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $massage_id
 * @property string $comment
 * @property boolean $is_active
 * 
 * @method integer          getMassageId()  Returns the current record's "massage_id" value
 * @method string           getComment()    Returns the current record's "comment" value
 * @method boolean          getIsActive()   Returns the current record's "is_active" value
 * @method adMassageComment setMassageId()  Sets the current record's "massage_id" value
 * @method adMassageComment setComment()    Sets the current record's "comment" value
 * @method adMassageComment setIsActive()   Sets the current record's "is_active" value
 * 
 * @package    symfony
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseadMassageComment extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('ad_massage_comment');
        $this->hasColumn('massage_id', 'integer', null, array(
             'type' => 'integer',
             'comment' => 'Cơ sở',
             ));
        $this->hasColumn('comment', 'string', 1000, array(
             'type' => 'string',
             'comment' => 'Nội dung comment',
             'length' => 1000,
             ));
        $this->hasColumn('is_active', 'boolean', null, array(
             'type' => 'boolean',
             'notnull' => true,
             'default' => false,
             'comment' => 'Trạng thái hiển thị (0: ko hiển thị, 1: hiển thị)',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}