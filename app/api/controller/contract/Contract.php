<?php
namespace addon\Contract\app\api\controller\contract;

use core\base\BaseApiController;
use addon\Contract\app\service\api\contract\ContractService;
use think\Response;

/**
 * Contract Controller for API
 * @package addon\Contract\app\api\controller\contract
 */
class Contract extends BaseApiController
{
    /**
     * Get My Contract List
     * @return Response
     */
    public function lists()
    {
        $data = $this->request->params([
            ['status', '']
        ]);
        $where = [];
        if ($data['status'] !== '') {
            $where[] = ['status', '=', $data['status']];
        }
        
        $service = new ContractService();
        $res = $service->getPage($where);
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
        if (empty($res)) {
            return fail('CONTRACT_NOT_EXIST');
        }
        return success($res);
    }

    /**
     * Sign Contract
     * @param int $id
     * @return Response
     */
    public function sign(int $id)
    {
        $data = $this->request->params([
            ['sign_image', '']
        ]);
        // Validate sign_image
        if (empty($data['sign_image'])) {
            return fail('SIGN_IMAGE_REQUIRED');
        }
        
        $service = new ContractService();
        try {
            $res = $service->sign($id, $data['sign_image']);
            return success('SIGN_SUCCESS', $res);
        } catch (\Exception $e) {
            return fail($e->getMessage());
        }
    }
}
