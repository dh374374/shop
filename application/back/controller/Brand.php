<?php


namespace app\back\controller;


use app\back\validate\BrandSet;
use think\Config;
use think\Controller;
use think\Request;
use app\back\model\Brand as BrandModel;
use think\Session;
use think\Validate;

/**
 * Class Brand
 * 品牌控制器
 * @package app\back\controller
 */
class Brand extends Controller
{
    /**
     * 添加品牌
     */
    public function create(){
        $request = Request::instance();
        //get请求
        if($request->isGet()){
            //有可能会携带错误信息需要展示，因此得接收错误信息（没有错误信息则接收空数组）
            return $this->fetch('create',
                ['errorInfo'=>Session::get('errorInfo')?:[],
                'errorData' => Session::get('errorData')?:[]]);
        }

        //post请求
        else if ($request->isPost()){
            //1、获取数据
            $data = $request->post();
            //2、完成合理性验证
            $validate = new BrandCreate();
            //batch 批量，check 验证数据
            if(!$validate->batch(true)->check($data)){
                //校验未通过
                return $this->redirect('create',[],302,['errorInfo'=>$validate->getError(),'errorData'=>$data]);//redirect通过第四个参数将错误信息带回create页面，以session的形式
            }

            //3、入库
            $model = new BrandModel();
            $result = $model
                ->allowField(['title','site','sort','logo','create_time','update_time'])
                ->data($data)
                ->save();

            if($result){
                //4、重定向到index动作
                return $this->redirect('index');
            }else{
                //插入错误
                return $this->redirect('create');
            }

        }
    }

    /**
     * 添加和更新
     */
    public function set($id=null){
        //？？？？为什么会考虑get请求和post请求呢？因为从index页面点击更新或者编辑按钮跳到set方法的是get请求过来的，而从set.html页面添加或者修改的信息是通过post请求过来的
        $request = Request::instance();
        //get请求
        if($request->isGet()){
            $row = BrandModel::get($id);
            //有可能会携带错误信息需要展示，因此得接收错误信息（没有错误信息则接收空数组）
            return $this->fetch('set',
                ['id'=>$id,
                    'errorInfo'=>Session::get('errorInfo')?:[],
                    'data' => Session::get('errorData')?:$row,]);
        }

        //post请求
        else if ($request->isPost()){
            //1、获取数据
            $data = $request->post();
            //2、完成合理性验证
            $validate = new BrandSet();
            //batch 批量，check 验证数据
            if(!$validate->batch(true)->check($data)){
                //校验未通过
                return $this->redirect('set',['id'=>$id],302,['errorInfo'=>$validate->getError(),'errorData'=>$data]);//redirect通过第四个参数将错误信息带回create页面，以session的形式
            }

            //3、入库
            if(is_null($id)){
                $model = new BrandModel();
            }else{
                $model = BrandModel::get($id);
            }

            $result = $model
                ->allowField(['title','site','sort','logo','create_time','update_time']) //用于过滤数据，其中的数据可以写入数据库
                ->data($data)
                ->save();

            if($result == 1){
                //4、重定向到index动作
                return $this->redirect('index');
            }else{
                //插入错误
                return $this->redirect('set');
            }
        }
    }

    /**
     *展示列表索引页
     */
    public function index(){
        //考虑过滤条件
        $query = BrandModel::where(null);
        $pageQuery = [];
        $filter_title = input('filter_title',null); //接收搜索框filter_title传输来的值,点击排序时候也有可能会从地址栏提交过来
        if(! is_null($filter_title)){
            $query->where('title','like',$filter_title.'%');
            $pageQuery = ['filter_title'=>$filter_title];
        }
        //将$filter_title分配到模板,用于返显搜索条件
        $this->assign('filter_title',$filter_title);
        //为了点击排序的时候可以把搜索的关键字传递进去
        $this->assign('pageQuery',$pageQuery);

        #考虑排序
        //初始化排序参数字符串
        $orderStr = '';
        //操作请求时携带的参数
        $order_field = input('order_field',null); //地址栏传递过来的待排序字段
        $order_type = input('order_type', 'asc'); //地址栏传递过来的待排序方式
        //如果有传递来排序字段
        if(!is_null($order_field)){
            $orderStr = $order_field . ' ' . $order_type;
        }
        $query->order($orderStr);
        //将排序参数传递到模板，从新作为当前排序参数使用
        $this->assign('order_field',$order_field);
        $this->assign('order_type',$order_type);


        //考虑分页
        $limit = 4;
        $rows = $query->paginate($limit,false,['query' => $pageQuery]); //paginate函数的第三个参数可用query关键字携带数据

        //展示模板
        return $this->fetch('index',['rows'=>$rows]);
    }

    /**
     * 校验Ajax是否校验title唯一
     */
    public function checkTitleUnique(){
        //必须返回字符串
        Config::set('default_ajax_return','html'); //临时修改Ajax的返回格式
        //这里放入id的原因是unique可以排除该id对应的title是否重复，如果有id传入说明是修改的，那么该id下的title可以重复，如果id没有传入说明是新增的，那么title不允许重复
        return Validate::unique(null, 'brand,title',['title'=>input('title'),'id'=>input('id')],'title')?'true':'false';
    }

    /**
     * 批处理操作(删除)
     */
    public function batch(){
        $data = input('selected/a');
//        dump($data);
        $model = new BrandModel();
        if(!$data){
            $this->error('删除失败,未选中');
        }
        $res = $model::destroy($data);
        //判断返回值是否成功
        if($res){
            $this->success('删除成功','index');
        }else{
            $this->error('删除失败');
        }

    }

}