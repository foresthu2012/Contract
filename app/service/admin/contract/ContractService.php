<?php
namespace addon\Contract\app\service\admin\contract;

use addon\Contract\app\model\Contract;
use core\base\BaseAdminService;

/**
 * Contract Service
 * @package addon\Contract\app\service\admin\contract
 */
class ContractService extends BaseAdminService
{
    /**
     * Get Contract Page List
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [], $member_keyword = '')
    {
        $field = 'id, title, file_path, member_id, order_id, status, sign_image, sign_time, create_time';
        $search_model = new Contract();
        // Add member relation to get nickname/mobile
        $with = ['member' => function($query){
            $query->field('member_id, nickname, mobile, headimg');
        }];
        // If member keyword is provided, we need to filter by member relation
        // Note: ThinkPHP 6 ORM doesn't support hasOne filtering in getPageList directly if not handled
        // But we can add a closure to $search_model if needed, or rely on getPageList to handle complex queries if supported.
        // Standard Niucloud getPageList might not support deep relation filtering easily without custom alias.
        // Let's try to add hasWhere if keyword exists.
        
        if (!empty($member_keyword)) {
             $search_model = $search_model->hasWhere('member', function($query) use ($member_keyword) {
                 $query->where('nickname|mobile', 'like', '%' . $member_keyword . '%');
             });
        }
        
        return $this->getPageList($search_model, $where, $field, 'create_time desc', [], $with);
    }

    /**
     * Add Contract
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $data['site_id'] = $this->site_id ?? 0; // Use default or get from context if null
        $data['create_time'] = time();
        $res = (new Contract())->save($data);
        return $res;
    }

    /**
     * Edit Contract
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function edit(int $id, array $data)
    {
        $site_id = $this->site_id ?? 0;
        $contract = (new Contract())->where([['id', '=', $id], ['site_id', '=', $site_id]])->find();
        if ($contract->isEmpty()) {
            throw new \Exception('CONTRACT_NOT_EXIST');
        }
        if ($contract['status'] == 1) {
            throw new \Exception('CONTRACT_SIGNED_CANNOT_EDIT'); // Prevent editing signed contracts
        }
        $data['update_time'] = time();
        return $contract->save($data);
    }

    /**
     * Delete Contract
     * @param int $id
     * @return mixed
     */
    public function delete(int $id)
    {
        $site_id = $this->site_id ?? 0;
        $contract = (new Contract())->where([['id', '=', $id], ['site_id', '=', $site_id]])->find();
        if ($contract->isEmpty()) {
            return false;
        }
        if ($contract['status'] == 1) {
            throw new \Exception('CONTRACT_SIGNED_CANNOT_DELETE'); // Prevent deleting signed contracts
        }
        return $contract->delete();
    }

    /**
     * Get Contract Info
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        $site_id = $this->site_id ?? 0;
        return (new Contract())->with(['member'])->where([['id', '=', $id], ['site_id', '=', $site_id]])->findOrEmpty()->toArray();
    }
}
