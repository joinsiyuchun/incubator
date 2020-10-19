<?php
namespace app\admin\controller;

use app\admin\model\Incubatorinfo as IncubatorinfoModel;
use app\common\controller\Admin;
use app\admin\model\item as itemModel;
use app\admin\model\Order as OrderModel;
use app\admin\model\incubatorinfo as incubModel;
use app\admin\model\OrderIncub as OrderIncubModel;

class Order extends Admin
{
    public function index()
    {
        if ($this->request->isPost()) {
            $action = $this->request->post('action/s', '');
            if ($action === 'taken') {
                $this->taken();
            }
        }
        $param = [
            'is_pay' => $this->request->get('is_pay/d', 1),
            'is_taken' => $this->request->get('is_taken/d', -1),
            'page' => max($this->request->get('page/d', 1), 1),
            'search' => $this->request->get('search/s', '', 'trim'),
            'user_id' => $this->request->get('user_id/d', 0)
        ];
        $order = OrderModel::with('OrderIncub')->order('id', 'desc');
        if ($param['user_id']) {
            $order->where('user_id', $param['user_id']);
        }
        if ($param['is_pay'] >= 0) {
            $order->where('is_pay', $param['is_pay']);
        }
        if ($param['is_taken'] >= 0) {
            $order->where('is_taken', $param['is_taken']);
        }
        if ($param['search'] !== '') {
            $order->where('id', ltrim($param['search'], 'A'));
        }
        $order = $order->paginate(10, false, ['type' => 'bootstrap', 'var_page' => 'page', 'query' => $param]);
        $item_ids = [];
        foreach ($order as $k => $v) {
            $order[$k]['code'] = $this->code($v['id']);
            foreach ($v['order_incub'] as $vv) {
                $item_ids[] = $vv['incub_id'];
            }
        }
        $item = incubModel::field('id,type')->where('id', 'in', array_unique($item_ids))->select();
        foreach ($order as $k => $v) {
            foreach ($v['order_incub'] as $kk => $vv) {
                foreach ($item as $vvv) {
                    if ($vvv['id'] === $vv['incub_id']) {
                        $order[$k]['order_incub'][$kk]['name'] = $vvv['type'];
                        break;
                    }
                }
            }
        }
        $this->assign('order', $order);
        $this->assign('item', $item);
        $this->assign('param', $param);
        return $this->fetch();
    }

    public function confirm() {
        $id = $this->request->get('id/d', 0);
        $confirmtype = $this->request->get('confirmtype/d', 0);
        switch ($confirmtype)
        {
            case 0:
                $val = 1;
                OrderModel::where('id', $id)->update(['is_taken' => $val, 'taken_time' => date('Y-m-d H:i:s')]);
                $this->alert('success', '工单取消成功。');
                break;
            case 1:
                $val = 2;
                OrderModel::where('id', $id)->update(['is_taken' => $val, 'taken_time' => date('Y-m-d H:i:s')]);
                $this->alert('success', '维保内结算。');
                break;
            case 2:
                $val = 3;
                OrderModel::where('id', $id)->update(['is_taken' => $val, 'taken_time' => date('Y-m-d H:i:s')]);
                $this->alert('success', '保外付费结算。');
                break;
        }

        $order = OrderModel::get($id, 'OrderIncub');
        if (!$order ) {
            $this->error('订单不存在');
        }
        $order['sn'] = $this->sn($order->id);
        $order['code'] = $this->code($order->id);

        foreach ($order['order_Incub'] as $j => $d) {
            $incub = IncubModel::where(['id' => $d['incub_id']])->find();
            if (!$incub) {
                continue;
            }
            $incublist[$j] = $incub;
            $incublist[$j]['partcost'] = $d['accessary_cost'];
            $incublist[$j]['servicecost'] = $d['total_cost'];
        }

        $this->assign('order', $order);
        $this->assign('incublist', $incublist);
        return $this->fetch();
    }

    public function updatecost() {
        $action = $this->request->get('action/s');
        $order_id = $this->request->get('order_id/d');
        $incub_id = $this->request->get('incub_id/d');
        $id = $this->request->get('id/d');
        $accessary_cost = $this->request->get('accessary_cost/f');
        $total_cost = $this->request->get('total_cost/f');
        if ($action === "add") {
            $data = [
                'id' => $id,
                'order_id' => $order_id,
                'incub_id' => $incub_id,
                'accessary_cost' => $accessary_cost,
                'total_cost' => $total_cost,
                'price' => $total_cost+$accessary_cost
            ];

            OrderIncubModel::update($data);
            $this->alert('success', '检查成功');
            return $this->redirect(url('admin/order/confirm', ['id' =>$order_id]));
        }
        $incubinfo = OrderIncubModel::where(['order_id'=>$order_id,'incub_id'=>$incub_id])->find();

        $this->assign('order_id', $order_id);
        $this->assign('incub_id', $incub_id);
        $this->assign('id', $incubinfo['id']);
        $this->assign('accessary_cost', $incubinfo['accessary_cost']);
        $this->assign('total_cost', $incubinfo['total_cost']);
        return $this->fetch();
    }

    protected function taken()
    {
        $id = $this->request->post('id/d', 0);
        $val = $this->request->post('val/d', 0) === 1  ?: 0;
        OrderModel::where('id', $id)->update(['is_taken' => $val, 'taken_time' => date('Y-m-d H:i:s')]);
        $this->alert('success', ($val ? '发货' : '取消发货') . '成功。');
    }

    protected function sn($id)
    {
        return 'WX' . str_pad($id, 14, '0', STR_PAD_LEFT);
    }

    protected function code($id)
    {
        return 'A' . str_pad($id, 2, '0', STR_PAD_LEFT);
    }
}
