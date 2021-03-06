<?php

/**
 * VtpProductImageTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class VtpProductImageTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object VtpProductImageTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('VtpProductImage');
    }
    public static function deleteImageByProduct($productId){
        return VtpProductImageTable::getInstance()->createQuery()
            ->delete()
            ->whereIn('product_id',$productId)->execute();
    }

    //lay danh sach photo theo albumId
    
    public static function getPhotoByProductId($productId)
    {
        return VtpProductImageTable::getInstance()->createQuery()
                ->where('product_id=?',$productId)
                ->orderBy('priority asc');
    }

    /* 
     * frontend
     */
    //Lấy danh sách ảnh của sản phẩm
    public static function getImagesByProductId($productId){
        $query = VtpProductImageTable::getInstance()->createQuery()
                ->select('id, file_path')
                ->where('product_id=?',$productId)
                ->orderBy('priority ASC')
                ->fetchArray();
        return $query;
    }
}