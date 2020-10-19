<?php
namespace app\admin\controller;

use app\common\controller\Admin;
use app\admin\model\Dept as DeptModel;
use app\admin\model\DeptGrade as DeptGradeModel;



class Org extends Admin
{
    public function list_to_tree($list, $root = 0, $pk = 'id', $pid = 'pid', $child = '_child') {
        // 创建Tree
        $tree = array();
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = &$list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = 0;
                if (isset($data[$pid])) {
                    $parentId = $data[$pid];
                }
                if ((string)$root == $parentId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    public function popup_tree_menu($tree, $level = 0) {
        $level++;
        $html = "";
        if (is_array($tree)) {
            $html = "<ul class=\"tree_menu\">\r\n";
            foreach ($tree as $val) {
                if (isset($val["name"])) {
                    $title = $val["name"];
                    $id = $val["id"];
                    if (empty($val["id"])) {
                        $id = $val["name"];
                    }
                    if (!empty($val["is_del"])) {
                        $del_class = "is_del";
                    } else {
                        $del_class = "";
                    }
                    if (isset($val['_child'])) {
                        $html = $html . "<li>\r\n<a class=\"$del_class\" node=\"$id\" ><i class=\"fa fa-angle-right level$level\"></i><span>$title</span></a>\r\n";
                        $html = $html . popup_tree_menu($val['_child'], $level);
                        $html = $html . "</li>\r\n";
                    } else {
                        $html = $html . "<li>\r\n<a class=\"$del_class\" node=\"$id\" ><i class=\"fa fa-angle-right level$level\"></i><span>$title</span></a>\r\n</li>\r\n";
                    }
                }
            }
            $html = $html . "</ul>\r\n";
        }
        return $html;
    }

    public function index(){
        $org_id=session('org_id');

        $menu = DeptModel::where('org_id',$org_id)->field('id,pid,name,is_del')->order('sort asc')->select();
        $tree = $this->list_to_tree($menu);
        $this -> assign('menu', $this->popup_tree_menu($tree));

        $list = DeptModel::order('sort asc') -> field('id,name')->select();
        $this -> assign('dept_list', $list);

        $list = DeptGradeModel::where('is_del=0') -> order('sort asc') -> field('id,name')->select();
        $this -> assign('dept_grade_list',$list);

        $this -> fetch();
    }



}
