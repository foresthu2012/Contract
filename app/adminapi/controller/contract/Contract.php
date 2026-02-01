<?php
namespace addon\Contract\app\adminapi\controller\contract;

use core\base\BaseAdminController;
use addon\Contract\app\service\admin\contract\ContractService;
use think\Response;

/**
 * Contract Controller
 * @package addon\Contract\app\adminapi\controller\contract
 */
class Contract extends BaseAdminController
{
    /**
     * Get Contract List
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['title', ''],
            ['status', ''],
            ['member_id', ''],
            ['member_keyword', ''],
        ]);
        $where = [];
        if (!empty($data['title'])) {
            $where[] = ['title', 'like', '%' . $data['title'] . '%'];
        }
        if ($data['status'] !== '') {
            $where[] = ['status', '=', $data['status']];
        }
        if (!empty($data['member_id'])) {
            $where[] = ['member_id', '=', $data['member_id']];
        }
        
        $service = new ContractService();
        $res = $service->getPage($where, $data['member_keyword']);
        return success($res);
    }

    /**
     * Get Contract Info
     * @param int $id
     * @return Response
     */
    public function info(int $id)
    {
        $service = new ContractService();
        $res = $service->getInfo($id);
        return success($res);
    }

    /**
     * Add Contract
     * @return Response
     */
    public function add()
    {
        $data = $this->request->params([
            ['title', ''],
            ['content', ''],
            ['file_path', ''],
            ['member_id', 0],
            ['order_id', 0],
        ]);
        $this->validate($data, 'addon\Contract\app\validate\contract\Contract.add'); // Optional: Add validator
        
        $service = new ContractService();
        $res = $service->add($data);
        return success('ADD_SUCCESS', $res);
    }

    /**
     * Edit Contract
     * @param int $id
     * @return Response
     */
    public function edit(int $id)
    {
        $data = $this->request->params([
            ['title', ''],
            ['content', ''],
            ['file_path', ''],
            ['member_id', 0],
            ['order_id', 0],
        ]);
        $this->validate($data, 'addon\Contract\app\validate\contract\Contract.edit'); // Optional
        
        $service = new ContractService();
        $res = $service->edit($id, $data);
        return success('EDIT_SUCCESS', $res);
    }

    /**
     * Delete Contract
     * @param int $id
     * @return Response
     */
    public function delete(int $id)
    {
        $service = new ContractService();
        $res = $service->delete($id);
        return success('DELETE_SUCCESS', $res);
    }
}
