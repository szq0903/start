<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/12
 * Time: 15:09
 */

namespace app\upload\controller;
use think\Controller;
use think\Request;
use think\File;

class Index extends Controller
{
    private $stateMap = array( //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE" => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED" => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED" => "文件类型不允许",
        "ERROR_CREATE_DIR" => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE" => "目录没有写权限",
        "ERROR_FILE_MOVE" => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND" => "找不到上传文件",
        "ERROR_WRITE_CONTENT" => "写入文件内容错误",
        "ERROR_UNKNOWN" => "未知错误",
        "ERROR_DEAD_LINK" => "链接不可用",
        "ERROR_HTTP_LINK" => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE" => "链接contentType不正确",
        "INVALID_URL" => "非法 URL",
        "INVALID_IP" => "非法 IP"
    );

    public function addimg($f) {

        if(!empty(request() -> file($f)))
        {
            $file = request() -> file($f);
        }

        if(!isset($file))
        {
            // 上传失败获取错误信息
            echo "{\"code\":-1, \"error\":\"Invalid file format\"}";
            exit;
        }

        // 移动到框架应用根目录/public/uploads/ 目录下
        $file->validate(['size'=>1024*1024*2,'ext'=>'jpg,png,gif']);
        $info = $file->rule('date')->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'images');

        if($info){
            $re =array(
                'code'=> 0,
                'message'=> '上传成功',
                'data'=>DS ."public" . DS . 'uploads'. DS .'images' . DS .$info->getSaveName()
            );
            echo json_encode($re);
        }else{
            // 上传失败获取错误信息
            echo "{\"code\":-1, \"error\":\"Invalid file format\"}";
        }
    }

    public function getConfig()
    {
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(getcwd()."/template/ueditor/config.json")), true);
        return $CONFIG;
    }


    public function index()
    {
        header("Content-Type: text/html; charset=utf-8");


        $CONFIG = $this->getConfig();

        $action = $_GET['action'];

        switch ($action) {
            case 'config':
                $result =  json_encode($CONFIG);
                break;

            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                //$result = include("action_upload.php");
                $result = $this->upload(htmlspecialchars($_GET['action']));
                break;

            /* 列出图片 */
            case 'listimage':
                //$result = include("action_list.php");
                $result = $this->filelist(htmlspecialchars($_GET['action']));
                break;
            /* 列出文件 */
            case 'listfile':
                //$result = include("action_list.php");
                $result = $this->filelist(htmlspecialchars($_GET['action']));
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = include("action_crawler.php");
                break;

            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }

    public function upload($action)
    {

        $CONFIG = $this->getConfig();

        $file = request() -> file('upfile');

        /* 上传配置 */
        $base64 = "upload";
        switch ($action) {
            case 'uploadimage':
                $config = array(
                    "pathFormat" => $CONFIG['imagePathFormat'],
                    "maxSize" => $CONFIG['imageMaxSize'],
                    "allowFiles" => $CONFIG['imageAllowFiles'],
                    "type" => 'images'
                );
                $fieldName = $CONFIG['imageFieldName'];
                break;
            case 'uploadscrawl':
                $config = array(
                    "pathFormat" => $CONFIG['scrawlPathFormat'],
                    "maxSize" => $CONFIG['scrawlMaxSize'],
                    "allowFiles" => $CONFIG['scrawlAllowFiles'],
                    "oriName" => "scrawl.png"
                );
                $fieldName = $CONFIG['scrawlFieldName'];
                $base64 = "base64";
                break;
            case 'uploadvideo':
                $config = array(
                    "pathFormat" => $CONFIG['videoPathFormat'],
                    "maxSize" => $CONFIG['videoMaxSize'],
                    "allowFiles" => $CONFIG['videoAllowFiles'],
                    "type" => 'video'
                );
                $fieldName = $CONFIG['videoFieldName'];
                break;
            case 'uploadfile':
            default:
                $config = array(
                    "pathFormat" => $CONFIG['filePathFormat'],
                    "maxSize" => $CONFIG['fileMaxSize'],
                    "allowFiles" => $CONFIG['fileAllowFiles'],
                    "type" => 'file'
                );
                $fieldName = $CONFIG['fileFieldName'];
                break;
        }

        if(!isset($file))
        {
            $stateInfo = $this->stateMap['ERROR_FILE_NOT_FOUND'];
            return;
        }
        if ($file->getInfo()['error'] <> 0) {
            $stateInfo = $this->stateMap[$file['error']];
            return;
        } else if (!file_exists($file->getInfo()['tmp_name'])) {
            $stateInfo = $this->stateMap["ERROR_TMP_FILE_NOT_FOUND"];
            return;
        } else if (!is_uploaded_file($file->getInfo()['tmp_name'])) {
            $stateInfo = $this->stateMap["ERROR_TMPFILE"];
            return;
        }


        //检查文件大小是否超出限制
        if($file->getInfo()['size'] > $config['maxSize'])
        {
            $stateInfo = $this->stateMap['ERROR_SIZE_EXCEED'];
            return;
        }

        //检查是否不允许的文件格式
        if(in_array($this->getFileExt(), $this->config["allowFiles"]))
        {
            $stateInfo = $this->stateMap['ERROR_TYPE_NOT_ALLOWED'];
            return;

        }
        //创建目录失败





        $info = $file->rule('date')->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .$config['type']);

        if($info){
            $data = array(
                "state" => 'SUCCESS',
                "url" => DS ."public" . DS . 'uploads'. DS .$config['type'] . DS .$info->getSaveName(),
                "title" => $file->getInfo()['name'],
                "original" => $info->getSaveName(),
                "type" => $file->getInfo()['type'],
                "size" => $file->getInfo()['size']
            );
        }
        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
         *     "url" => "",            //返回的地址
         *     "title" => "",          //新文件名
         *     "original" => "",       //原始文件名
         *     "type" => ""            //文件类型
         *     "size" => "",           //文件大小
         * )
         */

        /* 返回数据 */
        return json_encode($data);
    }

    //查看图片
    public function filelist($action)
    {
        $CONFIG = $this->getConfig();

        /* 判断类型 */
        switch ($action) {
            /* 列出文件 */
            case 'listfile':
                $allowFiles = $CONFIG['fileManagerAllowFiles'];
                $listSize = $CONFIG['fileManagerListSize'];
                $path = $CONFIG['fileManagerListPath'];
                $type  = 'file';
                break;
            /* 列出图片 */
            case 'listimage':
            default:
                $allowFiles = $CONFIG['imageManagerAllowFiles'];
                $listSize = $CONFIG['imageManagerListSize'];
                $path = $CONFIG['imageManagerListPath'];
                $type  = 'images';
        }

        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

        /* 获取参数 */
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $end = $start + $size;

        /* 获取文件列表 */
        $path = $_SERVER['DOCUMENT_ROOT'] . DS ."public" . DS . 'uploads'. DS .$type . DS;
        $files = $this->getfiles($path, $allowFiles);
        if (!count($files)) {
            return json_encode(array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => count($files)
            ));
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }
//倒序
//for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
//    $list[] = $files[$i];
//}

        /* 返回数据 */
        $result = json_encode(array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        ));

        return $result;
    }

    /**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    function getfiles($path, $allowFiles, &$files = array())
    {
        if (!is_dir($path)) return null;
        if(substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getfiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
                        $files[] = array(
                            'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                            'mtime'=> filemtime($path2)
                        );
                    }
                }
            }
        }
        return $files;
    }


}