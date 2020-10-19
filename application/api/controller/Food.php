<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\api\model\Category as CategoryModel;
use app\api\model\Order as OrderModel;
use app\api\model\OrderFood as OrderFoodModel;
use app\api\model\User as UserModel;
use app\common\library\facade\Setting as Setting;
use app\api\model\Incubatorinfo as IncubModel;
use app\api\model\OrderIncub as OrderIncubModel;

class Food extends Api
{
  //  protected $checkLoginExclude = ['paymentlist'];
    public function index()
    {
        $urlFix = function ($url) {
            if (preg_match('/^https?:\/\//', $url)) {
                return $url;
            } elseif ($url[0] === '/') {
                return $this->request->domain() . $url;
            } else {
                return $this->request->domain() . '/' .substr($url, 1);
            }
        };
        $img_swiper = json_decode(Setting::get('img_swiper'), true);
        foreach ($img_swiper as $k => $v) {
            $img_swiper[$k] = $urlFix($v);
        }
        $img_ad = $urlFix(Setting::get('img_ad'));
        $img_category = json_decode(Setting::get('img_category'), true);
        foreach ($img_category as $k => $v) {
            $img_category[$k] = $urlFix($v);
        }
        return json([
            'img_swiper' => $img_swiper,
//            'img_ad' => $img_ad,
            'img_category' => $img_category
        ]);
    }

    public function list2()
    {
        $url = $this->request->domain() . '/static/uploads/';
        $department=$this->request->get('department/d', 0);
        $category = CategoryModel::field('id,name')->order('sort', 'asc')->select()->toArray();
        switch($department){
            case 1 :
                $room='口腔一';
                break;
            case 2 :
                $room='口腔二';
                break;
            case 3 :
                $room='神经外科';
                break;
            case 4 :
                $room='胸科';
                break;
            case 5 :
                $room='普外';
                break;
            default:
                $room='全部';
        }
        if($room=='全部'){
            $food = IncubModel::field('id,category_id,type,price,image_url,seriesno')->where(['available'=>'启用','org_id'=>$this->org['id']])->order('id', 'asc')->select()->toArray();
        }else{
            $food = IncubModel::field('id,category_id,type,price,image_url,seriesno')->where(['available'=>'启用','org_id'=>$this->org['id'],'room'=>$room])->order('id', 'asc')->select()->toArray();
        }
         foreach ($food as $k => $v) {
            $food[$k]['image_url'] = $url . $v['image_url'];
        }
        $data = [];
        foreach ($category as $v) {
            $data[$v['id']] = array_merge($v, ['food' => []]);
            foreach ($food as $vv) {
                if ($v['id'] === $vv['category_id']) {
                    $data[$v['id']]['food'][$vv['id']] = $vv;
                }
            }
        }
        return json([
            'list' => $data,
            'promotion' => json_decode(Setting::get('promotion'), true)
        ]);
    }

    public function order()
    {
        if ($this->request->isPost()) {
            if ($this->request->post('id/d', 0)) {
                return $this->commentOrder();
            }
            return $this->createOrder();
        }
        $id = $this->request->get('id/d', 0);
        $order = OrderModel::get($id, 'OrderIncub');
        if (!$order || $order->user_id !== $this->user['id']) {
            $this->error('订单不存在');
        }
        $order['sn'] = $this->sn($order->id);
        $order['code'] = $this->code($order->id);
        $url = $this->request->domain() . '/static/uploads/';
        foreach ($order['order_incub'] as $k => $v) {
            $food = IncubModel::field('type,image_url,seriesno')->where(['id' => $v['incub_id']])->find();
            if (!$food) {
                continue;
            }
            $order['order_incub'][$k]['image_url'] = $url . $food['image_url'];
            $order['order_incub'][$k]['name'] = $food['type'];
            $order['order_incub'][$k]['seriesno'] = $food['seriesno'];
        }
        return json($order);
    }

    public function pay()
    {
        $id = $this->request->post('id/d', 0);
        $order = OrderModel::get($id, 'OrderIncub');
        if ($order && !$order->is_pay) {
            $order->is_pay = 1;//1：已经报修，未接修 0：abandan
            $order->pay_time = date('Y-m-d H:i:s');
            $order->save();
            UserModel::where('id', $this->user['id'])->inc('price', $order->price)->update();
            foreach ($order['order_incub'] as $k => $v) {
                IncubModel::where('id' ,$v['incub_id'])->update(['status'=>'报修待接修']);
            }
            $this->success('报修成功');
        }
        $this->error('报修失败');
    }

    public function orderlist()
    {
        $last_id = $this->request->get('last_id/d', 0);
        $row = min(max($this->request->get('row/d', 1), 1), 99);
        $order = OrderModel::where(['user_id' => $this->user['id'], 'is_pay' => 1]);
        if ($last_id) {
            $order->where('id', '<', $last_id);
        }
        $list = $order->order('id', 'desc')->limit($row)->select();
        $last_id = 0;
        if (!$list->isEmpty()) {
            $last_id = $list[count($list) - 1]['id'];
        }
        foreach ($list as $k => $v) {
            $food_id = OrderIncubModel::where('order_id', $v['id'])->limit(1)->value('incub_id');
            $list[$k]['first_food_name'] = IncubModel::where('id', $food_id)->value('type');
        }
        return json(['list' => $list, 'last_id' => $last_id]);
    }

    public function paymentlist()
    {
        $last_id = $this->request->get('last_id/d', 0);
        $type = $this->request->get('type', 0);
        switch ($type){
            case 1 :
                $is_taken=0;
                break;
            case 2 :
                $is_taken=2;
                break;
            case 3 :
                $is_taken=3;
                break;
            case 4 :
                $is_taken=-1;
         }
        $row = min(max($this->request->get('row/d', 1), 1), 99);
        $order = OrderModel::where(['user_id' => $this->user['id'], 'is_pay' => 1]);
        if ($last_id) {
            $order->where('id', '<', $last_id);
        }
        if($is_taken>=0){
            $order->where('is_taken', $is_taken);
        }
        $list = $order->order('id', 'desc')->limit($row)->select();
        $last_id = 0;
        if (!$list->isEmpty()) {
            $last_id = $list[count($list) - 1]['id'];
        }
        foreach ($list as $k => $v) {
            $food_id = OrderIncubModel::where('order_id', $v['id'])->limit(1)->value('incub_id');
            $list[$k]['first_food_name'] = IncubModel::where('id', $food_id)->value('type');
        }
        return json(['list' => $list, 'last_id' => $last_id]);
    }

    public function record()
    {
        $list = OrderModel::field('id,price,pay_time')->where(['user_id' => $this->user['id'], 'is_pay' => 1])->order('id', 'desc')->select();
        return json(['list' => $list]);
    }

    protected function commentOrder()
    {
        $id = $this->request->post('id/d', 0);
        $comment = $this->request->post('comment/s', '', 'trim');
        $order = OrderModel::get($id);
        if ($order ) {
            $order->comment = $comment;
            $order->save();
            $this->success('订单备注添加成功');
        }
        $this->error('订单备注添加失败');
    }

    protected function createOrder()
    {
        $order = $this->request->post('order/a', []);
        $comment = $this->request->post('comment/s', '', 'trim');
        $food_ids = [];
        foreach ($order as $v) {
            $food_ids[(int)$v['id']] = (int)$v['number'];
        }
        $price = 0;
        $number = 0;
        $order_food = [];
        $food_data = IncubModel::field('id,category_id,type,price,image_url,seriesno')->where('id', 'in', array_keys($food_ids))->where('available', '启用')->select()->toArray();
        foreach ($food_data as $v) {
            $order_food[$v['id']] = [
                'incub_id' => $v['id'],
                'number' => $food_ids[$v['id']],
                'price' => $v['price']
            ];
            $price += $v['price'] * $order_food[$v['id']]['number'];
            $number += $order_food[$v['id']]['number'];
        }
        $promotion = json_decode(Setting::get('promotion'), true);
        $promotion_price = 0;
        $promotion_diff = 0;
        foreach ($promotion as $v) {
            $promotion_diff2 = $price - $v['k'];
            if ($promotion_diff2 > 0 && $promotion_diff2 > $promotion_diff) {
                $promotion_price = $v['v'];
            } else {
                $promotion_diff = $promotion_diff2;
            }
        }
        $order = [
            'user_id' => $this->user['id'],
            'price' => $price - $promotion_price,
            'promotion' => $promotion_price,
            'number' => $number,
            'comment' => $comment,
            'create_time' => date('Y-m-d H:i:s')
        ];
        $order = OrderModel::create($order);
        foreach ($order_food as $k => $v) {
            $order_food[$k]['order_id'] = $order->id;
        }
        OrderModel::get($order->id)->orderIncub()->saveAll($order_food);
        return json(['order_id' => $order->id]);
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
