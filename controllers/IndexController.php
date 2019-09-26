<?php
namespace Power\Controllers;

use HtmlBuilder\Components;
use HtmlBuilder\Element;
use HtmlBuilder\Forms;
use HtmlBuilder\Layouts;
use HtmlBuilder\Parser\AdminLte\Parser;
use HtmlBuilder\Validate;

class IndexController extends AdminBaseController{
    public function indexAction(){
//        phpinfo();
//        exit;
//        $this->title = 'The PermissionAdmin System.';
        $parser = new Parser();
        $inputs = [];
//        $inputs[] = $parser->parse(
//            Forms::input('c','手机号有验证')->statistics()->inputBeforeIcon('fa fa-users')->validate(
//                Validate::number('请输入数字',1,8888),
//                Validate::regex('必须全是8','^8+$'),
//            )
//        );
//        $inputs[] = $parser->parse(Forms::input('a','用户名')->statistics()->subtype('datetime'));
//        $inputs[] = $parser->parse(Forms::input('a','用户名')->statistics()->subtype('email'));
//        $inputs[] = $parser->parse(Forms::input('b','个人说明')->required()->placeHolder('填写你的说明')->description('xxxx')->labelWidth(3)->tooltip('hhhh'));
//        $inputs[] = $parser->parse(Forms::input('c','手机号')->inputBeforeIcon('fa fa-users')->required()->inputMask("'mask':'(999) 999-9999'"));
//        $inputs[] = $parser->parse(Forms::input('c','手机号')->inputBeforeIcon('fa fa-star')->required()->subtype('date'));
//        $inputs[] = $parser->parse(Forms::input('c','手机号')->inputBeforeIcon('fa fa-users')->required()->subtype('time'));
//        $inputs[] = $parser->parse(Forms::input('c','ABCD')->labelWidth(2)->labelPosition('right-right')->visible(false));
//        $inputs[] = $parser->parse(Forms::input('c','xxx')->labelWidth(3)->labelPosition('right-left'));
//        $inputs[] = $parser->parse(Forms::input('c','xxx')->labelWidth(4)->labelPosition('left-right'));
//        $inputs[] = $parser->parse(Forms::input('c','xxx')->labelWidth(5)->labelPosition('left-left')->enabled(false));
//        $inputs[] = $parser->parse(Forms::input('c','xxx')->inputBeforeIcon('fa fa-users')->labelWidth(5)->labelPosition('left-left')->inputAfterIcon('fa fa-users'));
//        $inputs[] = $parser->parse(Layouts::columns()->column(Forms::input('a'),4)->column(Forms::input('a'),8));
//        $ttt = $parser->parse(Layouts::columns()->column(Forms::input('a','用户名')->labelWidth(3),4)->column(Forms::input('a'),8));
//        $inputs[] = $parser->parse(Layouts::box($ttt,'标题',Forms::input('a','模块名称')->labelWidth(4))->style('Success')->labelIcon('fa fa-users')->canClose()->canMini()->class('ttt'));
//        $inputs[] = $parser->parse(
//            Layouts::columns()->column(
//                Layouts::tabs()->tab('PageA', Forms::input('a','ahhh'))->tab('PageB', Forms::input('b','tttt'),true),
//                3
//            )->column(
//                Forms::input('cccc','kkkk'),
//                9
//            )
//        );
//        $inputs[] = $parser->parse(
//            Forms::button()->label('ssssssssss')->add(
//                Forms::button()->subtype('default')->label('AAA'),
//                Forms::button()->subtype('default')->label('BBB')->style('default'),
//                Forms::button()->subtype('default')->label('CCC')->style('danger'),
//                )
//        );

//        $inputs[] = $parser->parse(
//            Forms::button()->btnBeforeIcon('fa fa-users')->btnAfterIcon(
//                Forms::button()->subtype('default')->label('sss')
////                Forms::button()->add(
////                    Forms::button()->subtype('default')->label('sss')
////                )
//            )->subtype('input')
//        );
//        print_r(Forms::checkbox('asdfsdfa','bb','ssss')->choices([['text'=>'aaa','value'=>'1'],['text'=>'bbb','value'=>'1']]));
//        exit;
//        $inputs[] = $parser->parse(
//            Forms::checkbox('asdfsdfa','bb','1')->choices([['text'=>'aaa','value'=>'1'],['text'=>'bbb','value'=>'2']])->other(
//               '其他选项'
//            )
//        );
//        $inputs[] = $parser->parse(
//            Forms::radio('asdfsdfa','bbx','2')->choices([['text'=>'aaa','value'=>'1'],['text'=>'bbb','value'=>'2']])->other(
//                Forms::input('xxx')->placeHolder('其他')->disabled()
//            )
//        );

//        $inputs[] = $parser->parse(
//            Forms::select('asdfsdfa','bbx','2','select')->choices([['text'=>'aaa','value'=>'1'],['text'=>'bbb','value'=>'2']])->isTags(true)//->multiple(true)
//            ->labelWidth(3)->rows(1)
//        );

//        $inputs[] = $parser->parse(
//            Forms::form($this->url('update'))->add(
//                Forms::input('aaa','用户')->subtype('color'),
//                Forms::input('bbb','秘密')->subtype('password'),
//                Forms::file('xxxx')->accept('image/*')->placeHolder('用户头像'),//->setCorpSize(200,400),//->label('用户头像')->labelWidth(4)->description('头像必须300x234'),
//                Forms::file('yyyy[]')->accept('image/*')->subtype('multiple')->setCorpSize(200,200),//->label('用户头像')->labelWidth(4)->description('头像必须300x234'),
//                Forms::button('重置')->action('reset'),
//                Forms::button('提交')->action('submit'),
//            )
//        );

        $inputs[] = $parser->parse(
            Forms::form($this->url('update'))->add(
                Components::table(),
                Components::timerange('time')->label('时间段')->value(['12:12:12','13:13:13']),
                Components::multiselect($this->url('index',['action'=>'getItems']))
                  ->addSelect('a[]',null,2,$this->url('index',['action'=>'getItems']).'?type=A')
                  ->addSelect('a[]',null,1,$this->url('index',['action'=>'getItems']).'?type=B')
                  ->addSelect('c',null,1,$this->url('index',['action'=>'getItems']).'?type=C')
                  ->addSelect('d',null,1,$this->url('index',['action'=>'getItems']).'?type=D')
                  ->addSelect('e',null,1,$this->url('index',['action'=>'getItems']).'?type=E')
                  ->addSelect('f',null,1)->label('选择地区'),
                Forms::button('提交')->action('submit')
            )
        );

//            Forms::textarea()->subtype('wysihtml5'),
//            Forms::textarea()->subtype('simple')->label('xxxxx')->labelWidth(3),
//            Forms::textarea()->subtype('ckeditor'),
//        );

//print_r($inputs);
//exit;
    
        $this->addStyle($parser->getStyles());
        $this->addScript($parser->getScripts());
        foreach($parser->getJs() as $js) $this->addJs($js);
        foreach($parser->getCss() as $css) $this->addCss($css);
        $this->view->inputs = $inputs;
//        $aaa = (object)['minValue'=>'xxx','maxValue'=>'yyy'];
//        print_r($aaa);
//        exit ($aaa->minValue);
//        exit;
        
        # 方案 2
//        $file  = POWER_BASE_DIR.'library/HtmlBuilder/Parser/AdminLte/templates/form.php';
//        $obj   = Element::create('input')->add(
//            Element::create('aaaabbbb')
//        );
//        $parse = function()use($file,$obj){
//            extract(get_object_vars($obj),EXTR_OVERWRITE);
//            require $file;
//        };
////        $parse->call($obj);
//        $parse();
        
        /* 方案1
        $v = new \Phalcon\Mvc\View();
        $v->aaa = 123;
        $v->bbb = 456;
        $v->ccc = [111,222,333];
        $v->setViewsDir(POWER_BASE_DIR.'library/HtmlBuilder/Parser/AdminLte/templates');
       
        $v->setDi(\PA::$di);
        $v->registerEngines(
            [
                '.volt' => \Phalcon\Mvc\View\Engine\Volt::class
            ]
        );
        $a = $v->getPartial('form');
//        $a = $v->partial('form');
//        $a = $v->getRender('templates','form');
        //$a = $v->getContent();
        echo \Phalcon\Tag::textField(array('name', 'size' => 32));
        var_dump(''.$a);
        */
//        exit;
        
    
    
    
//        $a = //new Parser(
//            Layouts::columns(2)->add(
//                Layouts::box('右边搜索栏')->add(
//                    Forms::form('search')->add(
//                        Forms::input('text'),
//                        Forms::button('Search')
//                    ),
//                ),
//                Layouts::box('左边注册栏')->add(
//                    Forms::form('register')->add(
//                        Forms::input('username'),
//                        Components::datetime('expire_data'),
//                        Forms::button('Register','s')->attr('sdf','')->enabled()->value('ss')
//                            ->labelPosition('')
//                            ->label(''),
//                        Forms::file(),
//                    )
//                )
////            )
//        );
//        print_r($a->parse());
//        $a = FormElement::form('user_name')->add(
//            FormElement::input('user_name')->class("SSS"),
//            FormElement::input('sex'),
//            FormElement::create('input','aa')->template('input')
//        );
//
//        Element::create('form','');
//        FormElement::create('input','user_name');
//
//        Element::form('abc');
//        FormElement::input('user_name');
//
//        $b = AdminLteParser::parse($a);
//
//        $b = AdminLteParser::parse(
//            Element::form('post')->add(
//                FormElement::input('',''),
//                FormElement::checkbox('',''),
//                FormElement::radio(''),
//                LayoutElement::col3()->add(
//                    FormElement::input(''),
//                    FormElement::checkbox()
//                ),
//                ComponentElement::date()
//            )
//        );
//        $b = [
//            'css_files' => [],
//            'styles'    => '',
//            'js_files'  => [],
//            'js_init'   => '',
//            'scripts'   => '',
//        ];
//
//        exit(" this is $a ");

        $this->render();
    }

    public function updateAction(){
        print_r($_POST);
        print_r($_FILES);
        foreach($_FILES as $file){
            if(!is_array($file['name'])){
                foreach($file as $k=>$v){
                    $file[$k] = [$v];
                }
            }
            $count = count($file['name']);
            for($i=0; $i<$count; $i++){
                move_uploaded_file($file['tmp_name'][$i],'d:/tmp/'.$file['name'][$i]);
            }
        }
    }
    
    public function getItemsAction(){
//        $type = $this->getParam('type');
        $type = $_REQUEST['type'] ?? 0;
        $this->jsonOut(
            [
                ['text'=>uniqid().'=>'.$type, 'value'=>random_int(100, 200)],
                ['text'=>uniqid().'=>'.$type, 'value'=>random_int(100, 200)],
                ['text'=>uniqid().'=>'.$type, 'value'=>random_int(100, 200)],
                ['text'=>uniqid().'=>'.$type, 'value'=>random_int(100, 200)],
                ['text'=>uniqid().'=>'.$type, 'value'=>random_int(100, 200)],
            ]
        );
    }
}
