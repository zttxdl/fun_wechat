<?php

namespace app\common\model;

use think\Model;

class ManageCategory extends Model
{
    private $table_name = 'manage_category';

    public function getNameById($id)
    {
        return $this->name($this->table_name)->where('id',$id)->value('name');

    }
}
