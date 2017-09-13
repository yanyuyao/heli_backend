<?php
namespace Common\Model;
use Common\Model\CommonModel;
class OrderModel extends CommonModel{
    protected function _before_write(&$data) {
        parent::_before_write($data);
    }
}