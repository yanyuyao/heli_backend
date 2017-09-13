<?php
/**
 * Created by PhpStorm.
 * User: 108496
 * Date: 2017/7/26
 * Time: 15:59
 */

namespace Common\Model;
use Common\Model\CommonModel;
class RewardModel extends CommonModel{
    protected function _before_write(&$data) {
        parent::_before_write($data);
    }
}