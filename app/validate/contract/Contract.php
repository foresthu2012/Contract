<?php
namespace addon\Contract\app\validate\contract;

use core\base\BaseValidate;

/**
 * Contract Validator
 * @package addon\Contract\app\validate\contract
 */
class Contract extends BaseValidate
{
    protected $rule = [
        'title'     => 'require',
        'member_id' => 'require|number',
        'file_path' => 'require',
        'order_id'  => 'number',
    ];

    protected $message = [
        'title.require'     => 'CONTRACT_TITLE_REQUIRED',
        'member_id.require' => 'CONTRACT_MEMBER_ID_REQUIRED',
        'member_id.number'  => 'CONTRACT_MEMBER_ID_NUMBER',
        'file_path.require' => 'CONTRACT_FILE_PATH_REQUIRED',
        'order_id.number'   => 'CONTRACT_ORDER_ID_NUMBER',
    ];

    protected $scene = [
        'add'  => ['title', 'member_id', 'order_id'],
        'edit' => ['title', 'member_id', 'order_id'],
    ];
}
