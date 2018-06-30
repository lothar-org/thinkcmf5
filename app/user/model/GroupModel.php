<?php
namespace app\user\model;
use think\Model;

class GroupModel extends Model
{
    protected $name = 'member_group';

    public function getGroupSet()
    {
        $groups = $this->field('id,name')->where('status', 1)->order('list_order')->select()->toArray();

        return c2c_get_options(0, '请选择', $groups);
    }

    public function getUsers($group,$opt='')
    {
        $uuu = '';
        if ($opt == 'email') {
            $uuu = $this->name('member_group_user')->alias('a')->where('a.group_id',$group)->join('member b','a.member_id=b.id')->column('b.email');
        } elseif ($opt=='uname') {
            $uuu = $this->name('member_group_user')->alias('a')->where('a.group_id',$group)->join('member b','a.member_id=b.id')->column('b.username','b.id');
        } else {
            $uuu = $this->name('member_group_user')->where('group_id',$group)->column('member_id');
        }
        return $uuu;
    }
}
