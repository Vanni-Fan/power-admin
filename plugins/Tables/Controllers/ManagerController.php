<?php
namespace plugins\Tables\Controllers;
use HtmlBuilder\Components;
use HtmlBuilder\Components\Table;
use HtmlBuilder\Element;
use HtmlBuilder\Forms;
use HtmlBuilder\Layouts;
use HtmlBuilder\Parser\AdminLte\Parser;
use HtmlBuilder\Validate;
use PDO;
use Power\Controllers\AdminBaseController;
use PA;
use Power\Models\Rules;
use Tables\PluginsTableSources;

class ManagerController extends AdminBaseController
{
    private $params = [];
    public function initialize()
    {
        PA::$loader->registerNamespaces([
            'Tables' => POWER_DATA . 'TablesPlugins/'
        ]);
        PA::$loader->register();
        $this->params = $this->getParam();
        unset($this->params['Rule']);
        return parent::initialize();
    }

    private function getUrl(array $param, $method='GET'){
        return $this->url($method=='GET'?'display':'update', array_merge($this->params, $param));
    }

    public function settingsAction(){
        $this->title = 'Tables插件设置';
        if($this->getParam('command')){
             return $this->{$this->getParam('command')}();
        }
        
        $parser = new Parser();
        $this->view->content = $parser->parse(
            Components::table('数据源列表')
                ->queryApi($this->getUrl(['command'=>'getList','type'=>'source']))
                ->createApi($this->getUrl(['command'=>'show','type'=>'source','sub_command'=>'new']))
                ->updateApi($this->getUrl(['command'=>'show','type'=>'source','sub_command'=>'edit','id'=>'{id}']))
                ->deleteApi($this->getUrl(['command'=>'delete','type'=>'source','sub_command'=>'show','id'=>'{id}']))
                ->fields(
                    [
                          ['name'=>'id','text'=>'#'],
                          ['name'=>'name','text'=>'名称','sort'=>1,'filter'=>1],
                          ['name'=>'type','text'=>'类型','sort'=>1,'filter'=>1],
                          ['name'=>'host','text'=>'主机','sort'=>1,'filter'=>1],
                          ['name'=>'port','text'=>'端口','sort'=>1,'filter'=>1],
                          ['name'=>'user','text'=>'用户','sort'=>1,'filter'=>1],
                          ['name'=>'password','text'=>'密码','sort'=>1,'filter'=>1],
                    ]
                )->primary('id')
            ,
            Components::table('菜单列表')
                ->queryApi($this->getUrl(['command'=>'getList','type'=>'menu']))
                ->createApi($this->getUrl(['command'=>'show','type'=>'menu','sub_command'=>'new']))
                ->updateApi($this->routerUrl('update',['namespace'=>'Power\\Controllers','controller'=>'rules'],['item_id'=>'{id}']))
                ->deleteApi($this->getUrl(['command'=>'delete','type'=>'menu','sub_command'=>'show','id'=>'{id}']))
                ->fields(
                    [
                        ['name'=>'id','text'=>'#','sort'=>1, 'filter'=>1],
                        ['name'=>'rule_id','text'=>'菜单ID'],
                        ['name'=>'source_id','text'=>'数据源'],
                        ['name'=>'table_name','text'=>'表名','sort'=>1, 'filter'=>1],
                    ]
                )->canMin()
        );
        
        $parser->setResources($this);
        $this->render();
    }

    public function getList(){
        $size  = $_POST['limit']['size']??$this->page_size;
        $page  = $_POST['limit']['page']??1;
        $where = [
            'conditions' => '',
            'limit'      => $size,
            'offset'     => ($page - 1) * $size,
            'bind'       => []
        ];

        # 条件
        if(isset($_POST['filters'])){
            foreach($_POST['filters']??[] as $filter){
                $where['conditions'] .= $filter['name'] . $filter['operation'] . ' :'.$filter['name'].':';
                $where['bind'][$filter['name']] = $filter['value'];
            }
        }

        # 排序
        if(isset($_POST['sort'])){
            $where['order'] = '';
            foreach($_POST['sort'] as $sort){
                $where['order'] .= $sort['name'].' '.$sort['type'].',';
            }
            $where['order'] = substr($where['order'],0,-1);
        }

        $model = $this->params['type'] == 'menu' ? \Tables\PluginsTableMenus::class : \Tables\PluginsTableSources::class;
        $data = call_user_func([$model,'find'], $where);
        if($this->params['type'] == 'menu'){
            $data = [];
            $data = array_map(function($v){
                $rule = Rules::findFirstByRuleId($v['rule_id']);
                $source = PluginsTableSources::findFirstById($v['source_id']);
                $v['rule_id'] = $rule ? $rule->name : $v['rule_id'];
                $v['source_id'] = $source ? $source->name : $v['source_id'];
                return $v;
            },$data->toArray());
        }
        $this->jsonOut(
            ['list'=>$data,'total'=>call_user_func(
                [$model,'count'],
                ['conditions'=>$where['conditions'],'bind'=>$where['bind']]
            ),'page'=>$page,'size'=>$size]
        );
    }

    public function show(){
        $model = $this->params['type'] == 'menu' ? \Tables\PluginsTableMenus::class : \Tables\PluginsTableSources::class;
        $default = $this->params['sub_command'] == 'edit' ? call_user_func([$model,'findFirst'], $this->params['id']) : new \stdClass();
        $parser = new Parser();
        if($this->params['type'] == 'source') {
            $this->view->content = $parser->parse(
                Forms::form($this->getUrl(['command' => 'update']))->add(
                    Layouts::box(
                        Layouts::columns()->column(
                            Element::create('div')->add(
                                Forms::input('name', '名称',$default->name??'')->required(),
                                Forms::input('host', '主机', $default->host??'')->required()->inputMask("'alias':'ip'"),
                                Forms::input('user', '用户', $default->user??'')->required(),
                            )
                            , 6
                        )->column(
                            Element::create('div')->add(
                                Forms::select('type','类型', $default->type??'')
                                     ->choices([
                                                   ['text' => 'MySQL', 'value' => 'mysql'],
                                                   ['text' => 'SQLite', 'value' => 'sqlite'],
                                                   ['text' => 'PostgreSQL', 'value' => 'postgresql'],
                                               ])->required(),
                                Forms::input('port', '端口', $default->port??'')->required()->subtype('number'),
                                Forms::input('password', '密码', $default->password??'')->required()->subtype('password'),
                            )
                            , 6
                        ),
                        '编辑数据源',
                        Element::create('div')->add(
                            Forms::button('返回')->on('click','window.history.back()')->style('default'),
                            Forms::button('提交')->action('submit')->class('pull-right')
                        )
                    )
                )
            );
        }else{
            $menus = array_map(function($v){
                return ['text'=>str_repeat('&nbsp;　&nbsp;',$v['level']) . $v['name'], 'value'=>$v['rule_id']];
            },Rules::getFlatMenus());
            $sources = PluginsTableSources::find(['columns'=>'id as value,name as text'])->toArray();
            $this->view->content = $parser->parse(
                Forms::form($this->getUrl(['command' => 'update']))->add(
                    Layouts::box(
                        Element::create('div')->add(
                            Forms::select('rule_id','上级目录', $default->rule_id??'','select2')
                                 ->choices($menus)
                                 ->required()
                                 ->description('如果找不到对应的菜单目录，请在《<a>系统管理->权限管理</a>》中<a>创建菜单</a>。')
                                 ->tooltip('将此功能放到什么菜单位置下面'),
                            Forms::select('source_id','数据源', $default->source_id??'','select2')
                                 ->choices($sources)
                                 ->required()
                                 ->description('数据源信息在<a>数据源管理</a>中创建'),
                            Forms::input('table_name', '表名', $default->table_name??'')->required()->tooltip('表名必须为中文')->placeHolder('表名必须存在')->validate(
                                new Validate('text','不能为空',['max'=>1,'min'=>2])
                            ),
                        ),'编辑',
                        Element::create('div')->add(
                            Forms::button('返回')->on('click','window.history.back()')->style('default'),
                            Forms::button('提交')->action('submit')->class('pull-right')
                        )
                    )
                )
            );
        }
        $parser->setResources($this);
        $this->render('manager/source-edit');
    }

    public function update(){
        $model = $this->params['type'] == 'menu' ? \Tables\PluginsTableMenus::class : \Tables\PluginsTableSources::class;
        $a = new $model();
        if($this->params['sub_command']=='new'){
            $a->create($_POST);
        }else{
            call_user_func([$model,'findFirst'], $this->params['id'])->update($_POST);
            if($a instanceof \Tables\PluginsTableMenus){
                # 修改菜单
                $rule = Rules::findFirstByRuleId($_POST['rule_id']); // todo
                $params = [
                    'source_id'=>1,
                    'table'=>'abcd',
                ];
            }
        }
        $this->response->redirect($this->url('display',['item_id'=>$this->item_id,'action'=>'set','event'=>'setting']));
    }

    public function delete(){
        $model = $this->params['type'] == 'menu' ? \Tables\PluginsTableMenus::class : \Tables\PluginsTableSources::class;
        if(strpos($this->params['id'],',')){
            $ids = explode(',',$this->params['id']);
        }else{
            $ids = [$this->params['id']];
        }
        foreach($ids as $id){
            call_user_func([$model,'findFirst'], $id)->delete();
        }
        return $this->getList();
    }
}