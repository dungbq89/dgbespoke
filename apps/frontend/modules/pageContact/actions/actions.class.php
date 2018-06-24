<?php

/**
 * Created by PhpStorm.
 * User: Ta Van Ngoc
 * Date: 6/13/15
 * Time: 11:17 PM
 */
class pageContactActions extends sfActions
{
    public function executeIndex(sfWebRequest $request)
    {
        ini_set('max_execution_time', 300);
        $this->setLayout(false);
        $url = 'http://localhost:9696/data_faby/advance2.txt';
        $urlImage = 'http://localhost:9696/uploads/massage_image';
        $response = json_decode(file_get_contents($url));
        if ($response) {
            if ($response->contents) {
                foreach ($response->contents as $value) {
                    $store = $value->store;
                    $mass = new adMassage();
                    $mass->setName($store->storeName);
                    $mass->setAddress($store->address);
                    $mass->setTimeOpen($store->openTime);
                    $mass->setTimeClose($store->closeTime);
                    $mass->setPhone($store->tel);
                    $detailUrl = 'http://faby.vn/api/store/detail?storeId=' . $store->id;
                    var_dump($detailUrl);
                    $responseDetail = json_decode(file_get_contents($detailUrl));
                    $storeDetail = $responseDetail->contents->store;
                    $mass->setDescription($storeDetail->description);
                    $mass->setLat($storeDetail->latitude);
                    $mass->setLng($storeDetail->longitude);
                    $mass->setPriority(100);
                    $mass->setIsActive(0);
                    //save anh
                    $url = 'http://faby.vn/api//photo/load/' . $storeDetail->poster;
                    $urlImage = 'F:\code\dgbespoke\dgbespoke\web/uploads/article/' . $storeDetail->poster;
                    file_put_contents($urlImage, file_get_contents($url));
                    $mass->setImage($storeDetail->poster);
                    $mass->save();
                    //lưu bộ sưu tập ảnh

                }
            }
        }
        die('ok');
    }
}