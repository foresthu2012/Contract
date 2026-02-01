<?php
namespace addon\Contract\app\service\api\contract;

use addon\Contract\app\model\Contract;
use core\base\BaseApiService;

/**
 * Contract Service for API
 * @package addon\Contract\app\service\api\contract
 */
class ContractService extends BaseApiService
{
    /**
     * Get My Contract Page List
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'id, title, status, sign_time, create_time';
        $site_id = $this->site_id ?? 0;
        $member_id = $this->member_id ?? 0;
        $where[] = ['member_id', '=', $member_id];
        $where[] = ['site_id', '=', $site_id];
        $search_model = new Contract();
        return $this->getPageList($search_model, $where, $field, 'create_time desc');
    }

    /**
     * Get Contract Info
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        $site_id = $this->site_id ?? 0;
        $member_id = $this->member_id ?? 0;
        return (new Contract())
            ->where([['id', '=', $id], ['site_id', '=', $site_id], ['member_id', '=', $member_id]])
            ->findOrEmpty()->toArray();
    }

    /**
     * Sign Contract
     * @param int $id
     * @param string $sign_image
     * @return bool
     * @throws \Exception
     */
    public function sign(int $id, string $sign_image)
    {
        $site_id = $this->site_id ?? 0;
        $member_id = $this->member_id ?? 0;
        $contract = (new Contract())->where([['id', '=', $id], ['site_id', '=', $site_id], ['member_id', '=', $member_id]])->find();
        if ($contract->isEmpty()) {
            throw new \Exception('CONTRACT_NOT_EXIST');
        }
        if ($contract['status'] == 1) {
            throw new \Exception('CONTRACT_SIGNED');
        }
        
        $data = [
            'status' => 1,
            'sign_image' => $sign_image,
            'sign_time' => time(),
            'update_time' => time()
        ];
        return $contract->save($data);
    }
}
