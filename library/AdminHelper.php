<?php
use HtmlBuilder\Forms\Input;
use HtmlBuilder\Forms\Select;
use HtmlBuilder\Forms\TextArea;
use HtmlBuilder\Forms\File;
use HtmlBuilder\Forms\Check;
use Phalcon\Mvc\Controller;
use HtmlBuilder\Parser\AdminLte\Parser;

class AdminHelper{
    /**
     * @param        $menu 要展示的菜单
     * @param string $current_url 当前URL
     * @param bool   $frist 是否是第一个菜单
     * @param bool   $show 是显示还是隐藏ul元素
     *
     */
    public static function outputMenu(&$menu, array $path, int $menu_id=0, int $level=0, array $menuBadges=[])
    {
        if($level){ // 子菜单:打开条件 当前的 parent_id 等于 menu_id
            $show = ($path[$level]['parent_id']??null)==$menu_id? true : false;
            echo '<ul class="treeview-menu"' .($show?' style="display: block;"':''). '>';
        }else{ // 主菜单
            echo '<ul class="sidebar-menu" data-widget="tree">';
        }

        foreach ($menu as $i) {
            $has_sub = !empty($i['sub']);
            if($has_sub){
                $active = ($path[$level]['menu_id']??0)==$i['menu_id']? ' menu-open' : '';
            }else{
                $active = ($path[$level]['menu_id']??0)==$i['menu_id']? ' active' : '';
            }
            #echo "\n<!-- $active -->\n";
//            echo '<li class="', ($has_sub ? 'treeview' : ''), $active, '"><a href="', ($i['url'].'?menu_id='.$i['menu_id']), '">', "\n";
            $url = $i['url_suffix'] ?: (PA_URL_PATH.'menu/'.$i['menu_id'].'/index');
            echo '<li class="', ($has_sub ? 'treeview' : ''), $active, '"><a href="', $url , '">', "\n";
            if ($i['icon']) echo '<i class="', $i['icon'], '"></i>', "\n";
            echo '<span>', $i['name'], '</span>', "\n";
            if ($has_sub || !empty($menuBadges[$i['menu_id']])) {
                echo '<span class="pull-right-container">', "\n";
                echo $menuBadges[$i['menu_id']]??'', "\n";
                if ($i['sub']) echo '<i class="fa fa-angle-left pull-right"></i>', "\n";
                echo '</span>', "\n";
            }
            echo '</a>', "\n";
            if ($has_sub) self::outputMenu($i['sub'], $path, $i['menu_id'], $level+1, $menuBadges);
            echo '</li>', "\n";
        }
        echo '</ul>';
    }
    
    public static function getConfigsHtml_new(array $all_extends, array $default, array $wrapper=['','']):array{
        print_r($all_extends);
        print_r($default);

        exit;
        return $out = []; // todo TODO
        foreach($all_extends as $menu_id => $extends) {
            $tmp = $wrapper[0];
            print_r($extends);
            foreach($extends as $extend) {
                $tmp .= '<div class="form-group">';
//                $tmp .= '<label for="" class="col-sm-4 control-label must-start">' . $extend['name'] . '【' . $extend['var_name'] . '】</label>';
                $tmp .= '<label for="" class="col-sm-4 control-label must-start">' . $extend['name'] . '</label>';
                
                $no_items  = $extend['options_type'] === 'null';                                       # 是否没有备选值
                $single    = $extend['var_type'] === 'text';                                       # 是否为单一值
                $input     = '';                                                                            # 输入的组件
                $form_name = 'extend['.$extend['menu_id'].']['.$extend['var_name'].']';                  # 表单名
                $id        = strtr($form_name,['['=>'_',']'=>'_']);                                         # 表单ID
                if(isset($default[$extend['menu_id']][$extend['var_name']])){
                    $def_value = $default[$extend['menu_id']][$extend['var_name']];
                }else{
                    $def_value = json_decode($extend['var_default'],1);
                }
                
                if($no_items){ # 没有备选值，那么直接让用户输入
                    $input .= '<input name="'.$form_name.'" type="text" class="form-control" id="'.$id.'" value="'.$def_value.'">';
                }else{ # 有备选值，则需要输出 select 或 checkbox 或 radio
                    $items = $extend['options_type']==='callback' ? call_user_func($extend['options']) : json_decode($extend['options'],1);
                    $start = $end = $template = $content = '';
                    if(count($items)<5){ # 使用 checkbox or radio
                        $template = '<label><input value="_KEY_" name="'.$form_name.($single?'':'[]').'" type="'.($single?'radio':'checkbox').'" _CHECKED_>_VAL_ 　</label>';
                    }else{ # 使用 select
                        $start = '<select class="form-control" name="'.$form_name.($single?'':'[]').'" id="'.$id.'" '.($single?'':' multiple="multiple" size="5"').' >';
                        $end   = '</select>';
                        $template   = '<option value="_KEY_" _SELECTED_>_VAL_</option>';
                    }
                    
                    foreach($items as $key=>$val){
                        $replace = ['_KEY_'=>$key, '_VAL_'=>$val];
                        if($single){
                            $matched = $def_value == $key;
                            $replace['_CHECKED_']  = $matched ? 'checked' : '';
                            $replace['_SELECTED_'] = $matched ? 'selected' : '';
                        }else{
                            $matched = in_array($key, $def_value);
                            $replace['_CHECKED_']  = $matched ? 'checked' : '';
                            $replace['_SELECTED_'] = $matched ? 'selected' : '';
                        }
                        $content .= strtr($template,$replace);
                    }
                    $input .= $start.$content.$end;
                }
                $tmp .= '<div class="col-sm-8">' . $input . '</div>';
                $tmp .= '</div>';
            }
            $tmp .= $wrapper[1];

            $out[$menu_id] = $tmp;
        }
        return $out;
    }

    public static function getConfigsHtmlGroup(array $configs, array $default, Controller $controller):array{
        if(empty($configs)) return '';
        $parser = new Parser();
        $contents = [];
        foreach($configs as $config) {
            $menu_id = $config['menu_id'] ?: 0;
            if(!isset($contents[$menu_id])) $contents[$menu_id] = '';
            $contents[$menu_id] .= $parser->parse(self::configToHtmlBuilder($config, $default[$config['menu_id']] ?? null));
        }
        $parser->setResources($controller);
        return $contents;
    }

    public static function getConfigsHtml(array $configs, array $default, Controller $controller):string{
        if(empty($configs)) return '';
        $parser = new Parser();
        $contents = '';
        foreach($configs as $config) {
            $contents .= $parser->parse(self::configToHtmlBuilder($config, $default[$config['menu_id']] ?? null));
        }
        $parser->setResources($controller);
        return $contents;
    }

    # 将 configs 中信息转换成 HtmlBuilder 对象
    public static function configToHtmlBuilder($a_config_of_configs_table, $default_value=null){
        $row = $a_config_of_configs_table;
        switch($row['options_type']){
            case 'Input:text':
            case 'Input:mail':
            case 'Input:url':
            case 'Input:tel':
            case 'Input:mobile':
            case 'Input:currency':
            case 'Input:number':
            case 'Input:password':
            case 'Input:time':
            case 'Input:date':
            case 'Input:color':
                $sub_type = substr($row['options_type'],6);
                return new Input($row['var_name'], $row['name'],$default_value[$row['var_name']]??$row['var_default'],$sub_type);
                break;
            case 'Select:single':
            case 'Select:multiple':
            case 'Select:tags':
                $sub_type = substr($row['options_type'],7);
                return new Select($row['var_name'], $row['name'],$default_value??$row['var_default'],$sub_type);
                break;
            case 'TextArea:simple':
            case 'TextArea:ckeditor':
            case 'TextArea:wyihtml5':
                return new TextArea($row['var_name'], $row['name'],$default_value??$row['var_default']);
                break;
            case 'File:image':
            case 'File:file':
            case 'File:multipeImages':
            case 'File:multipeFiles':
                $obj = new File($row['var_name']);//, $row['name'],$default_value??$row['var_default']);
                if(strrpos($row['options_type'],'mage')){
                    $obj->accept('image/*');
                    $obj->corpWidth = 200;
                }
                return $obj;
                break;
            case 'Check:checkbox':
            case 'Check:radio':
            case 'JSON:array':
            case 'JSON:object':
                $sub_type = substr($row['options_type'],6);
                $obj = new Check($row['var_name'], $row['name'],$default_value??$row['var_default']);
                if(strpos($row['options_type'],'JSON')===0){
                    $val = json_decode($row['options'], 1);
                    $choices = [];
                    foreach($val as $k=>$v){
                        $choices[] = ['value'=>$k, 'text'=>$v];
                    }
                    $obj->choices($choices);
                }else{
                    $obj->subtype = $sub_type;
                    $obj->choices([
                        ['value'=>1,'text'=>'AAAa'],
                        ['value'=>2,'text'=>'BBB'],
                        ['value'=>1,'text'=>'CCC'],
                        ['value'=>1,'text'=>'DDD'],
                    ])->colCount(2);
                }

                return $obj;
                break;
            default:
                throw new \Exception('没有定义'.print_r($row,1));
        }
    }
}
