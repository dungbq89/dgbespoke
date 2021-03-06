<?php

require_once dirname(__FILE__).'/../lib/adManageCategoryGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/adManageCategoryGeneratorHelper.class.php';

/**
 * adManageCategory actions.
 *
 * @package    Vt_Portals
 * @subpackage adManageCategory
 * @author     ngoctv1
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class adManageCategoryActions extends autoAdManageCategoryActions
{
    //chuyển sang trang sửa
    protected function executeBatchEdit(sfWebRequest $request)
    {
        $ids = $request->getParameter('ids');
        $ad_category=AdCategoryTable::getInstance()->createQuery()
            ->select('name, parent_id, level, priority')
            ->Where('id=?',$ids[0])->fetchOne();
        $this->redirect(array('sf_route' => 'ad_category_edit', 'sf_subject' => $ad_category));
    }
    //Lên trên
    protected function executeBatchUp(sfWebRequest $request)
    {
        $ids = $request->getParameter('ids');
        $this->MoveCategory($ids[0],'UP');
    }
    //Xuống dưới
    protected function executeBatchDown(sfWebRequest $request)
    {
        $ids = $request->getParameter('ids');
        $this->MoveCategory($ids[0],'DOWN');
    }
    //Sang trái
    protected function executeBatchLeft(sfWebRequest $request)
    {
        $ids = $request->getParameter('ids');
        $this->MoveCategory($ids[0],'LEFT');
    }
    //Sang phải
    protected function executeBatchRight(sfWebRequest $request)
    {
        $ids = $request->getParameter('ids');
        $this->MoveCategory($ids[0],'RIGHT');
    }
    //Hàm đệ quy lấy các chuyên mục con
    public static function getCategoryByParent($category_id){
        $strCat=$category_id;
        $lstCat=AdCategoryTable::getCategoryByParentID($category_id);
        if(count($lstCat)>0){
            foreach($lstCat as $item){
                $strCat .=','. self::getCategoryByParent($item->id);
            }
        }
        if (VtHelper::endsWith($strCat,',')){
            $strCat=substr($strCat, 0 ,strlen($strCat)-1);
        }
        return $strCat;
    }

    //Hàm di chuyển
    public static function MoveCategory($category_id,$type)
    {
        //$type: UP,DOWN,LEFT,RIGHT

        $catFirst=0;
        $catLast=0;
        $catLeft=0;
        $strCat=self::getCategoryByParent($category_id);
        $lstCategory=AdCategoryTable::getListCategory($strCat);
        $parent_id=0;
        foreach ($lstCategory as $ad_category){
            if ($ad_category->id==$category_id){
                $parent_id=$ad_category->parent_id;
            }
        }
        //Lay danh sach chuyen muc cung mức
        $lstCateLevel=AdCategoryTable::getCategoryByLevel($parent_id);
        $i=1;
        foreach ($lstCateLevel as $item){
            if($i==1){
                $catFirst=$item->id;
            }
            if($i==count($lstCateLevel)){
                $catLast=$item->id;
            }
            if($item->level==0){
                $catLeft=$item->id;
            }
            $i=$i+1;
        }
//        echo $catLeft;die;
        switch ($type){
            case "UP":
                //Nếu đã là bản ghi đầu tiên cùng cấp thì không thực hiện di chuyển lên trên
                if($catFirst==$category_id){
                    break;
                }
                foreach ($lstCateLevel as $item ){
                    if ($item->id!==$category_id){
                        $priority=$item->priority;
                        $catUp=$item->id;
                    }
                    if ($item->id==$category_id){
                        break;
                    }
                }
//
                foreach ($lstCategory as $ad_category){
                    if ($ad_category->id==$category_id){
                        $ad_category->priority= $priority;
                        $ad_category->save();
                        $priority=$priority+1;
                    }else{
                        $ad_category->priority= $priority;
                        $ad_category->save();
                        $priority=$priority+1;
                    }
                }
//
                $strCat=self::getCategoryByParent($catUp);
                $lstCategory=AdCategoryTable::getListCategory($strCat);
                foreach ($lstCategory as $ad_category){
                    if ($ad_category->id==$catUp){
                        $ad_category->priority= $priority;
                        $ad_category->save();
                        $priority=$priority+1;
                    }else{
                        $ad_category->priority= $priority;
                        $ad_category->save();
                        $priority=$priority+1;
                    }
                }

                break;
            case "DOWN":
                //Neu la ban ghi cuoi cung trong mot cap thi khong thuc hien di chuyen xuong duoi
                if($catLast==$category_id){
                    break;
                }
                $flag=false;
                foreach ($lstCateLevel as $item ){
                    if ($flag==true){
                        $catDown=$item->id;
                        break;
                    }
                    if ($item->id==$category_id){
                        $priority=$item->priority;
                        $flag=true;
                    }
                }
//
                $strCat=self::getCategoryByParent($catDown);
                $lstCatDown=AdCategoryTable::getListCategory($strCat);
                foreach ($lstCatDown as $ad_category){
                    if ($ad_category->id==$catDown){
                        $ad_category->priority= $priority;
                        $ad_category->save();
                        $priority=$priority+1;
                    }else{
                        $ad_category->priority= $priority;
                        $ad_category->save();
                        $priority=$priority+1;
                    }
                }
//
                foreach ($lstCategory as $ad_category){
                    if ($ad_category->id==$category_id){
                        $ad_category->priority= $priority;
                        $ad_category->save();
                        $priority=$priority+1;
                    }else{
                        $ad_category->priority= $priority;
                        $ad_category->save();
                        $priority=$priority+1;
                    }
                }
                break;
            case "LEFT":
                //Nếu đã là root thì không thực hiện dịch trái
                if($catLeft>0){
                    break;
                }
                //Lay danh sách category cung mức cha
                foreach ($lstCategory as $ad_category){
                    if($ad_category->id==$category_id){
                        $parent=AdCategoryTable::getCategoryById($ad_category->parent_id);
                        $lstCat=AdCategoryTable::getCategoryByParentID($parent->parent_id);
                    }
                }
                $i=1;
                $menu_id_last='0';
                foreach ($lstCat as $item)
                {
                    if($i==count($lstCat)){
                        $menu_id_last=$item->id;//dung de lay ra cac con chau cua chuyen cuoi cung trong danh sach level moi
                        $priority=$item->priority;
                    }
                    $i=$i+1;
                }
                //Lấy ra tất cả menu con của menu cuối cùng trong mức mới
                $strCat=self::getCategoryByParent($menu_id_last);
                if ($menu_id_last!=$strCat){
                    $i=1;
                    $lstCatLast=AdCategoryTable::getListCategory($strCat);
                    foreach ($lstCatLast as $item)
                    {
                        if($i==count($lstCatLast)){
                            $priority=$item->priority;
                        }
                        $i=$i+1;
                    }
                }
//                echo $priority; die;
                //Lay danh sach category đứng sau vị trí cần chèn
                $lstParent=AdCategoryTable::getCategoryByPriority($priority);

                //Chèn danh sách category vào cuối danh sách cùng mức được dịch trái
                foreach ($lstCategory as $ad_category){
                    if ($ad_category->id!=$category_id){
                        $ad_category->level=$ad_category->level-1;
                    }elseif($ad_category->id==$category_id){
                        $objCat=AdCategoryTable::getCategoryById($ad_category->parent_id);
                        $ad_category->parent_id=$objCat->parent_id;
                        $ad_category->level=$ad_category->level-1;
                    }
                    $priority=$priority+1;
                    $ad_category->priority=$priority;
                    $ad_category->save();
                }

                //cập nhật lại thứ tự của các category đứng sau parent
                foreach ($lstParent as $item){
                    $priority=$priority+1;
                    $item->priority=$priority;
                    $item->save();
                }

                break;
            case "RIGHT":
                //Nếu là bản ghi đầu tiên trong danh sách thì không thực hiện dịch phải
                if($catFirst==$category_id){
                    break;
                }
                foreach ($lstCateLevel as $item ){
                    if ($item->id!==$category_id){
                        $catRight=$item->id;
                    }
                    if ($item->id==$category_id){
                        break;
                    }
                }

                foreach ($lstCategory as $ad_category){
                    if ($ad_category->id!=$category_id){
                        $ad_category->level=$ad_category->level+1;
                        $ad_category->save();
                    }elseif($ad_category->id==$category_id){
                        $ad_category->parent_id=$catRight;
                        $ad_category->level=$ad_category->level+1;
                        $ad_category->save();
                    }
                }
                break;
        }

    }

    public function executeBatchList(sfWebRequest $request)
    {
        $i18n = sfContext::getInstance()->getI18N();
        $form = new BaseForm();
        $form->bind($form->isCSRFProtected() ? array($form->getCSRFFieldName() => $request->getParameter($form->getCSRFFieldName())) : array());
        if (!$form->isValid())
        {
            $this->getUser()->setFlash('error', $i18n->__('Token không hợp lệ'));
            $this->redirect('@ad_category');
        }

        if (!$ids = $request->getParameter('ids'))
        {
            $this->getUser()->setFlash('error', 'You must at least select one item.');

            $this->redirect('@ad_category');
        }

        if (!$action = $request->getParameter('batch_action'))
        {
            $this->getUser()->setFlash('error', 'You must select an action to execute on the selected items.');

            $this->redirect('@ad_category');
        }

        if (!method_exists($this, $method = 'execute'.ucfirst($action)))
        {
            throw new InvalidArgumentException(sprintf('You must create a "%s" method for action "%s"', $method, $action));
        }

        if (!$this->getUser()->hasCredential($this->configuration->getCredentials($action)))
        {
            $this->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
        }

        $validator = new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'adCategory'));
        try
        {
            // validate ids
            $ids = $validator->clean($ids);

            // execute batch
            $this->$method($request);
        }
        catch (sfValidatorError $e)
        {
            $this->getUser()->setFlash('error','A problem occurs when deleting the selected items some items do not exist anymore.');
        }

        $this->redirect('@ad_category');
    }

    public function executeIndex(sfWebRequest $request)
    {
        // sorting
        if ($request->getParameter('sort') && $this->isValidSortColumn($request->getParameter('sort')))
        {
            $this->setSort(array($request->getParameter('sort'), $request->getParameter('sort_type')));
        }

        // pager
        if ($request->getParameter('page'))
        {
            $this->setPage($request->getParameter('page'));
        }
        else
        {
            $this->setPage(1);
        }

        // max per page
        if ($request->getParameter('max_per_page'))
        {
            $this->setMaxPerPage($request->getParameter('max_per_page'));
        }

        $this->sidebar_status = $this->configuration->getListSidebarStatus();
        $this->pager = $this->getCategoryListBox();

        //Start - thongnq1 - 03/05/2013 - fix loi xoa du lieu tren trang danh sach
        if ($request->getParameter('current_page'))
        {
            $current_page = $request->getParameter('current_page');
            $this->setPage($current_page);
            $this->pager = $this->getPager();
        }
        //end thongnq1

        $this->sort = $this->getSort();
    }

    protected function executeBatchDelete(sfWebRequest $request)
    {
        $ids = $request->getParameter('ids');
        $i18n=sfContext::getInstance()->getI18N();
        $records = Doctrine_Query::create()
            ->from('adCategory')
            ->whereIn('id', $ids)
            ->andWhere('lang=?',sfContext::getInstance()->getUser()->getCulture())
            ->execute();

        foreach ($records as $record)
        {
            //Kiem tra chuyen muc duoc xoa co chuyen muc con hay khong
            $check=AdCategoryTable::getCategoryByParentID($record->id);

            if(count($check)>0){
                $this->getUser()->setFlash('notice', $i18n->__('Bạn phải xóa các chuyên mục con trước.'));
            }else{
                $checkArticle= AdArticleTable::getArticleByCategoryId($record->id);
                if(count($checkArticle)>0){
                    $this->getUser()->setFlash('notice', $i18n->__('Bạn phải xóa các các bài viết bên trong chuyên mục trước.'));
                }else{
                    try{
                        AdCategoryPermissionTable::deletePermissionByCategory($record->id);
                        $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $record)));
                        $record->delete();
                        $this->getUser()->setFlash('success', 'The selected items have been deleted successfully.');
                    } catch (Exception $ex) {

                    }
                }
            }
        }
        $this->redirect('@ad_category');
    }

    public function executeDelete(sfWebRequest $request)
    {
        $i18n = sfContext::getInstance()->getI18N();
        $form = new BaseForm();
        $form->bind($form->isCSRFProtected() ? array($form->getCSRFFieldName() => $request->getParameter($form->getCSRFFieldName())) : array());
        if (!$form->isValid())
        {
            $this->getUser()->setFlash('error', $i18n->__('Token không hợp lệ'));
        }
        else{
            $check=AdCategoryTable::getCategoryByParentID($this->getRoute()->getObject()->getId());
            if(count($check)>0){
                $this->getUser()->setFlash('notice', $i18n->__('Bạn phải xóa các chuyên mục con trước.'));
                $this->redirect(array('sf_route' => 'ad_category_edit', 'sf_subject' => $this->getRoute()->getObject()));
            }else{
                $checkArticle=adArticleTable::getArticleByCategoryId($this->getRoute()->getObject()->getId());
                if(count($checkArticle)>0){
                    $this->getUser()->setFlash('notice', $i18n->__('Bạn phải xóa các các bài viết bên trong chuyên mục trước.'));
                    $this->redirect(array('sf_route' => 'ad_category_edit', 'sf_subject' => $this->getRoute()->getObject()));
                }else{
                    try{
                        adCategoryPermissionTable::deletePermissionByCategory($this->getRoute()->getObject()->getId());
                        $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));
                        $this->getRoute()->getObject()->delete();
                        $this->getUser()->setFlash('success', 'The item was deleted successfully.');
                    } catch (Exception $ex) {
                        $this->getUser()->setFlash('notice', $i18n->__('Có lỗi xảy ra trong quá trình xóa!'));
                    }

                }
            }
        }
        $this->redirect('@ad_category');
    }

    public function executeBatch(sfWebRequest $request)
    {
        $i18n = sfContext::getInstance()->getI18N();
        $form = new BaseForm();
        $form->bind($form->isCSRFProtected() ? array($form->getCSRFFieldName() => $request->getParameter($form->getCSRFFieldName())) : array());
        if (!$form->isValid())
        {
            $this->getUser()->setFlash('error', $i18n->__('Token không hợp lệ'));
            $this->redirect('@ad_category');
        }

        if (!$ids = $request->getParameter('ids'))
        {
            $this->getUser()->setFlash('error', 'You must at least select one item.');

            $this->redirect('@ad_category');
        }

        if (!$action = $request->getParameter('batch_action'))
        {
            $this->getUser()->setFlash('error', 'You must select an action to execute on the selected items.');

            $this->redirect('@ad_category');
        }

        if (!method_exists($this, $method = 'execute'.ucfirst($action)))
        {
            throw new InvalidArgumentException(sprintf('You must create a "%s" method for action "%s"', $method, $action));
        }

        if (!$this->getUser()->hasCredential($this->configuration->getCredentials($action)))
        {
            $this->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
        }

        $validator = new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'adCategory'));
        try
        {
            // validate ids
            $ids = $validator->clean($ids);

            // execute batch
            $this->$method($request);
        }
        catch (sfValidatorError $e)
        {
            $this->getUser()->setFlash('error','A problem occurs when deleting the selected items some items do not exist anymore.');
        }

        $this->redirect('@ad_category');
    }

    protected function getCategoryListBox()
    {
        $query = $this->buildQuery();
        $query->andWhere('lang=?',sfContext::getInstance()->getUser()->getCulture());
        $query->orderby('priority asc');
        $arrCat= $query->execute();

        $arrResult=array();

        foreach ($arrCat as $cat){
            $strTab='';
            if ($cat->level>0){
                for ($i=0;$i<$cat->level;$i++){
                    $strTab=$strTab.'...';
                }
            }
            $arrResult[$cat->id] = $strTab. $cat->name;
        }

        return $arrResult;
    }

    protected function processForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        if ($form->isValid())
        {
            $notice = $form->getObject()->isNew() ? 'The item was created successfully.' : 'The item was updated successfully.';
            $isNew= $form->getObject()->isNew();
            $oldParent_id=$form->getObject()->getParentId();
            $vals = $form->getValues();
            $oldLevel=$form->getObject()->getLevel();
            $strCat='';
            $newLevel=0;
            if( $isNew || $oldParent_id != $vals['parent_id']){
                $strCat=self::getCategoryByParent($vals['parent_id']);
                if(!$isNew && $oldParent_id != $vals['parent_id']){
                    //Lay ra danh sach category chuyen muc con bị thay doi
                    $strCatNew=self::getCategoryByParent($form->getObject()->getId());
                    $listChild=AdCategoryTable::getListCategory($strCatNew);
                }
            }
            try {
                $ad_category = $form->save();

                adCategoryPermissionTable::deletePermissionByCategory($ad_category->id);
                //ngoctv cap nhat quyen cho chuyen muc

                if (!empty($vals['permission'])){
                    $permission=explode(",",$vals['permission']);
                    if(is_array($permission)){
                        foreach ($permission as $val) {
                            if ($val!=''){
                                $ad_category_permission= new adCategoryPermission();
                                $ad_category_permission->category_id=$ad_category->id;
                                $ad_category_permission->permission_id=$val;
                                $ad_category_permission->save();
                            }
                        }
                    }
                }
//                var_dump($oldParent_id.'-'.$vals['parent_id']);die;
                if ($isNew || $oldParent_id != $vals['parent_id']){
                    //Sắp xếp lại thứ tự hiển thị
                    $i=1;
                    $priority=0;
                    //Lấy các category con cùng mức
//                    $strCat=self::getCategoryByParent($ad_category->parent_id);
                    $lstCategory=AdCategoryTable::getListCategory($strCat);
                    //Lấy thứ tự của category cuối cùng trowng danh sách
                    $count=count($lstCategory);
                    if ($count>0){
                        foreach($lstCategory as $item){
                            if($i==$count){
                                $priority=$item->priority;
                            }
//                            var_dump($item->priority);
                            $i=$i+1;
                        }
                    }
//                    die;
                    $level=0;
                    if($ad_category->parent_id>0){
                        $objParent=  AdCategoryTable::getCategoryById($ad_category->parent_id);
                        $newLevel=$objParent->level;
                        $level=$newLevel+1;
                        if($priority==0){
                            $priority=$objParent->priority;
                        }
                    }
                    //Lay danh sach category đứng sau parent
                    $lstParent=AdCategoryTable::getCategoryByPriority($priority);
                    $ad_category->priority=$priority+1;
                    $ad_category->level=$level;
                }
                else{
                    if($ad_category->parent_id>0){
                        $objParent=   AdCategoryTable::getCategoryById($ad_category->parent_id);
                        $newLevel=$objParent->level;
                        $level=$newLevel+1;
                        $ad_category->level=$level;
                    }
                }
                //
                $ad_category->lang=sfContext::getInstance()->getUser()->getCulture();
                $slug=removeSignClass::removeSign($ad_category->name);
                if ($slug==''){
                    $slug=VtHelper::generateString(5);
                }
                $objCat = count(AdCategoryTable::checkSlug($slug, $ad_category->id));
                while ($objCat>0){
                    $slug=$slug.'_'.VtHelper::generateString(5);
                    $objCat = count(AdCategoryTable::checkSlug($slug,$ad_category->id));
                }
                $ad_category->slug=$slug;

                //save category
                $ad_category->save();
                $priority=$ad_category->priority;
                //Edit thay doi cha: Cap nhat lai thu thu cua chuyen muc con ben trong
                if (!$isNew && $oldParent_id != $vals['parent_id']){
                    if(isset($listChild)){

                        foreach ($listChild as $item){
                            if($ad_category->id!=$item->id){
                                if($ad_category->level<$oldLevel){
                                    //giam level
                                    $item->level=($item->level-1)?:0;
                                }
                                if($ad_category->level>$oldLevel){
                                    //tang level
                                    $item->level=$item->level+$newLevel+1;
                                }
                                $priority =$priority+1;
                                $item->priority=$priority;
                                $item->save();
                            }
                        }
                    }
                }

                //cập nhật lại thứ tự của các category đứng sau parent
                if(isset($lstParent)){
                    foreach ($lstParent as $item){
                        if (!isset($strCatNew) || !in_array($item->id,explode(',',$strCatNew))) {
                            $priority=$priority+1;
                            $item->priority=$priority;
                            $item->save();
                        }

                    }
                }
            } catch (Doctrine_Validator_Exception $e) {

                $errorStack = $form->getObject()->getErrorStack();

                $message = get_class($form->getObject()) . ' has ' . count($errorStack) . " field" . (count($errorStack) > 1 ?  's' : null) . " with validation errors: ";
                foreach ($errorStack as $field => $errors) {
                    $message .= "$field (" . implode(", ", $errors) . "), ";
                }
                $message = trim($message, ', ');

                $this->getUser()->setFlash('error', $message);
                return sfView::SUCCESS;
            }

            $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('form' => $form, 'object' => $ad_category)));

            if ($request->hasParameter('_save_and_exit'))
            {
                $this->getUser()->setFlash('success', $notice);
                $this->redirect('@ad_category');
            } elseif ($request->hasParameter('_save_and_add'))
            {
                $this->getUser()->setFlash('success', $notice.' You can add another one below.');

                $this->redirect('@ad_category_new');
            }
            else
            {
                $this->getUser()->setFlash('success', $notice);

                $this->redirect(array('sf_route' => 'ad_category_edit', 'sf_subject' => $ad_category));
            }
        }
        else
        {
            $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
        }
    }
}
