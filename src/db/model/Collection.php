<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://xmzibi.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MrYe <55585190@qq.com>
// +----------------------------------------------------------------------

namespace og\db\model;

use og\db\Collection as BaseCollection;
use og\db\Model;

class Collection extends BaseCollection
{
    /**
     * 延迟预载入关联查询
     * @access public
     * @param mixed $relation 关联
     * @return $this
     */
    public function load($relation)
    {
        $item = current($this->items);
        $item->eagerlyResultSet($this->items, $relation);

        return $this;
    }

    /**
     * 设置需要隐藏的输出属性
     * @access public
     * @param array $hidden   属性列表
     * @param bool  $override 是否覆盖
     * @return $this
     */
    public function hidden($hidden = [], $override = false)
    {
        $this->each(function ($model) use ($hidden, $override) {
            /** @var Model $model */
            $model->hidden($hidden, $override);
        });

        return $this;
    }

    /**
     * 设置需要输出的属性
     * @param array $visible
     * @param bool  $override 是否覆盖
     * @return $this
     */
    public function visible($visible = [], $override = false)
    {
        $this->each(function ($model) use ($visible, $override) {
            /** @var Model $model */
            $model->visible($visible, $override);
        });

        return $this;
    }

    /**
     * 设置需要追加的输出属性
     * @access public
     * @param array $append   属性列表
     * @param bool  $override 是否覆盖
     * @return $this
     */
    public function append($append = [], $override = false)
    {
        $this->each(function ($model) use ($append, $override) {
            /** @var Model $model */
            $model && $model->append($append, $override);
        });

        return $this;
    }

}
