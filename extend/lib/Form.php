<?php

namespace lib;
/**
 * 解析成form表单
 * @author myeoa
 * @email  6731834@163.com
 * @date 2015年4月30日 上午10:10:32
 */
/*
 text,单行文本(varchar)     			文本框		字符型
 int,整数类型									文本框		数字型
 float,小数类型								文本框		小数型
 datetime,时间类型						文本框      数字型(10)  	（时间组件）
 img,图片										文本框   	字符型		（上传组件   ）

 multitext,多行文本						文本域		备注型 	(65535)不用传
 htmltext,HTML文本						文本域		备注型  (65535)不用传       	（在线编辑器	 ）
 select,使用select下拉框				下拉框		数组型
 radio,使用radio选项卡					单选框		数组型
 checkbox,checkbox多选框			多选框		数组型
*/

class Form {
	public $forminc=array(
			'text'		=>'text',
			'multitext'	=>'textarea',
			'htmltext'	=>'htmlarea',
			'int'		=>'text',
			'float'		=>'text',
			'datetime'	=>'datetime',
			'img'		=>'img',
			'select'	=>'select',
			'radio'		=>'radio',
			'checkbox'	=>'checkbox',
			'multiimg'  =>'multiimg'
		);
	/**
	 * 字段解析成form 表单
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return mixed
	 */
	public function fieldToForm($field,$class='',$id='',$key=''){

		$ftype=$this->forminc[$field['dtype']];
		if(method_exists($this, $ftype))
		{
			return $this->$ftype($field,$class,$id,$key);
		}else
		{
			return false;
		}
	}
	/**
	 * 解析成文本框
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public function text($field,$class='',$id='',$key=''){
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";

		$read = isset($field['read']) ? 'readonly':'';
		$str="<input name='{$field['fieldname']}' placeholder='{$field['itemname']}' $read type='text' value='{$field['vdefault']}'  {$class}  {$id} />";
		return $str;
	}

	/**
	 * 解析成文本域
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public  function textarea ($field,$class='',$id='',$key=''){
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$str="<textarea name='{$field['fieldname']}' {$class}  {$id}>{$field['vdefault']}</textarea>";
		return $str;
	}

	/**
	 * 解析成下拉框
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public  function select ($field,$class='',$id=''){
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$str="<select name='{$field['fieldname']}'  {$class}  {$id}>";
		if(is_array($field['vdefault']))
        {
            $arrs = $field['vdefault'];
        }else{
            $arrs=explode(',', $field['vdefault']);
        }
		if(is_array($arrs))
		{
			foreach ($arrs as $k=>$value)
			{
			    if(isset($field['val']) && $k == $field['val'])
                {
                    $str.="<option value='$k' selected>$value</option>";
                }else{
                    $str.="<option value='$k'>$value</option>";
                }

			}
		}
		$str.="</select>";
        return $str;
	}

	/**
	 * 解析成单选框
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public function radio ($field,$class='',$id='' ,$key=''){
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$str='';
		$arrs=explode(',', $field['vdefault']);
		if(is_array($arrs))
		{
		    $i=1;
			foreach($arrs as $key=>$value)
			{
			    if(!isset($field['val']) )
                {
                    if($i == 1)
                    {
                        $checked = 'checked';
                    }else{
                        $checked = '';
                    }

                }elseif($key == $field['val']){
                    $checked = 'checked';
                }else{
                    $checked = '';
                }

			    $str.=<<<eoc
			    
			    <div class="rdio rdio-success col-sm-3">
                    <input type="radio" name="{$field['fieldname']}" value="{$key}" id="radio{$key}" {$checked}>
                    <label for="radio{$key}">({$key}){$value}</label>
                </div>
eoc;
                $i++;
			}
		}
		return $str;
	}
	/**
	 * 解析成多选框
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public function checkbox($field,$class='',$id='',$key=''){
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$str='';
		$arrs=explode(',', $field['vdefault']);
		if(is_array($arrs))
		{
			foreach($arrs as $key=>$value)
			{
				//print_r ($value);
				$str.="<input  type='checkbox' name='{$field['fieldname']}[]'  value='{$key}' {$class}  {$id}/>{$value}";
			}
		}
		return $str;
	}
	/**
	 * 解析成上传图片
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public function img ($field,$class='',$id='qcode',$key=''){

	    if(stripos($field['vdefault'], 'http') !== false)
        {
            $header_array = get_headers($field['vdefault'], true);
            $imagesize = $header_array['Content-Length'];
        }else{
            $imagesize =filesize(getcwd().$field['vdefault']);
        }
        $field['vdefault'] = str_replace('\\','/',$field['vdefault']);

		$html = "<input id='{$id}' name='{$field['fieldname']}' value='{$field['vdefault']}' type='hidden'>
                <input id='file-{$id}' name='up{$id}' value='{$field['vdefault']}' type='file' data-min-file-count='1'>
                <script>
	                $('#file-{$id}').fileinput({
                        language: 'zh',
		                uploadUrl: '/upload/index/addImg/f/up{$field['fieldname']}.html',
                        allowedFileExtensions : ['jpg', 'png','gif'],
		                previewFileType:'any',
		                dropZoneEnabled: false,
				";
	    if($field['vdefault'] <> '')
        {
        $html .= "     initialPreview: [
			            \"<img src='{$field['vdefault']}'  class='kv-preview-data file-preview-image' style='width:auto;height:160px;'/>\",

		                ],
		                initialPreviewConfig:[{caption: \"{$field['vdefault']}\", size: {$imagesize}, width: \"120px\",  key: 1},] ,
		                initialCaption:'{$field['vdefault']}'
                    ";
        }
        $html .=    "}).on('fileuploaded', function (event, data, previewId, index){
                            $('#{$id}').val(data.response.data);
                            $('.file-caption-name').eq(0).attr('title',data.response.data);
                            var html ='<i class=\'glyphicon glyphicon-file kv-caption-icon\'></i>'+data.response.data;
                            $('.file-caption-name').eq(0).html(html);
                        
                    });
                </script>";



		return $html;
	}

	/**
	 * 解析成文本编缉器
	 * @param unknown $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public function htmlarea($field,$class='',$id=''){
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";

        $field['vdefault'] = addslashes($field['vdefault']);

        $html = <<<aa
        <script id="{$field['fieldname']}" name="{$field['fieldname']}" type="text/plain"></script>
        <script type="text/javascript">
            //实例化编辑器
            //建议使用工厂方法getEditor创建和引用编辑器实例，如果在某个闭包下引用该编辑器，直接调用UE.getEditor('editor')就能拿到相关的实例
            var ue = UE.getEditor('{$field['fieldname']}');
            
            ue.ready(function() {
               
                ue.setContent('{$field['vdefault']}');
                //ue.setContent('<p><img src="\\public\\uploads\\images\\20190416\\51a8877f736e903647d57915f735ffe4.jpeg"  alt="20190416\51a8877f736e903647d57915f735ffe4.jpeg" title="11.jpeg" width="426" height="374"/>1111111111</p>');
            });
            
        </script>
aa;

		return $html;
	}
	/**
	 * 解析成时间插件
	 * @param string $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public function datetime($field,$class='',$id='')
	{
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$field['vdefault']=$field['vdefault']=='' ?  date('Y-m-d H:m:s'):$field['vdefault'];
		if(stripos($field['vdefault'],'-')===false)
		{
			$field['vdefault']=date('Y-m-d H:m:s',$field['vdefault']);
		}
		$str.='<div class="inline layinput">';
		$str.="<input name='{$field['fieldname']}' type='text' value='{$field['vdefault']}' onclick=\"laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})\"  {$class}  {$id} /> <label class=\"laydate-icon\"></label>";
		$str.='</div>';
		return $str;
	}

	public function multiimg($field,$class='',$id='')
	{
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$timestamp = time();
		$token=md5('unique_salt' . $timestamp);
		$html='
		<script src="uploadify/jquery.min.js" type="text/javascript"></script>
		<script src="uploadify/jquery.uploadify.min.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="uploadify/uploadify.css">
		
		<input id="file_upload" name="file_upload" type="file" multiple="true">
		<div id="sid"></div>
        <div id="img">';
		if(!$field['vdefault']=='')
		{
			$imgarr=json_decode($field['vdefault'],true);

			if(is_array($imgarr))
			{
				foreach($imgarr['url'] as $key=>$value)
				{
				$html.='<li><div class="fk"><img src="/upload.php?do=display&amp;img='.$imgarr['url'][$key].'&amp;width=200&amp;height=200"></div><p><input type="text" value="'.$imgarr['url'][$key].'" style="width:190px;" name="'.$field['fieldname'].'[url][]"><input type="text" style="width:150px;" value="'.$imgarr['name'][$key].'" name="'.$field['fieldname'].'[name][]"><input name="del" type="button" value="删除" onclick="delli(this)" class="del"></p></li>';
				}
			}
		}
		$html.='</div>';
		$html.="
		<script type=\"text/javascript\">
		$(function() {
			$('#file_upload').uploadify({
				'formData'     : {
					'timestamp' : ' $timestamp',
					'token'     : ' $token'
				},
				'buttonText' : '上传图集',
				'queueID' : 'sid',
				'progressData' : 'speed',
				'onUploadSuccess' : function(file, data, response) {
					var obj = JSON.parse(data);
					if(obj.error == 0){
						$('#img').append('<li><div class=\'fk\'><img src=\"/upload.php?do=display&img='+obj.url+'&width=200&height=200\"></div><p><input type=\'text\' value='+obj.url+' style=\'width:190px;\' name=\'{$field['fieldname']}[url][]\'><input type=\'text\' style=\'width:150px;\' value=\"'+obj.name+'\" name=\'{$field['fieldname']}[name][]\'><input name=\'del\' type=\'button\' value=\'删除\' onClick=\'delli(this)\' class=\'del\'></p></li>');
					}else{
						$('#img').append('<li><div class=\'fk\'>'+obj.name+':'+obj.msg+'<input name=\'msg\' type=\'button\' value=\'删除\' onClick=\'delli(this)\' class=\'del\'></p></li>');
					}
					
        		},
				'swf'      : 'uploadify/uploadify.swf',
				'uploader' : '/upload.php?do=upimg&dir=image'
			});
			
			$('#img li .del').click(function() {
				
			});
		});
		//删除按钮事件
		function delli(s)
		{
			$(s).parents('li').remove();
		}
		
		</script>";

		return $html;
	}
}




