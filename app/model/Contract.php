<?php
namespace addon\Contract\app\model;

use core\base\BaseModel;
use think\model\concern\SoftDelete;

/**
 * Contract Model
 * @package addon\Contract\app\model
 */
class Contract extends BaseModel
{
    use SoftDelete;

    /**
     * Data table name
     * @var string
     */
    protected $name = 'addon_contract_contract';

    /**
     * Define primary key
     * @var string
     */
    protected $pk = 'id';

    /**
     * Soft delete field
     * @var string
     */
    protected $deleteTime = 'delete_time';

    /**
     * Default soft delete value
     * @var int
     */
    protected $defaultSoftDelete = 0;

    /**
     * Type conversion
     * @var array
     */
    protected $type = [
        'create_time' => 'integer',
        'update_time' => 'integer',
        'delete_time' => 'integer',
        'sign_time'   => 'integer',
        'site_id'     => 'integer',
        'member_id'   => 'integer',
        'order_id'    => 'integer',
        'status'      => 'integer',
    ];

    /**
     * Get Create Time Attr
     * @param $value
     * @return string
     */
    public function getCreateTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * Get Update Time Attr
     * @param $value
     * @return string
     */
    public function getUpdateTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * Get Sign Time Attr
     * @param $value
     * @return string
     */
    public function getSignTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * Relation with Member
     */
    public function member()
    {
        return $this->hasOne('app\model\member\Member', 'member_id', 'member_id');
    }
}
