<?php
/**
 * Created by PhpStorm.
 * User: code
 * Date: 2019/4/22
 * Time: 17:49
 */

namespace app\admin\controller;

use app\admin\model\Order;
use app\admin\model\Orderitem;
use app\admin\model\Category;
use think\Request;

class Orders extends BaseMould
{
    //模型名字
    public $mouldname = 'order';
    //自身模型实例
    public $m;
    //添加数据


    public function _initialize()
    {
        //调用父类的构造函数
        parent::_initialize();
        $this->m = new Order;
    }



    public function edit($id)
    {
        foreach ($this->field as $k=>$val) {
            if($val['fieldname'] == 'mid' || $val['fieldname'] == 'pid' || $val['fieldname'] == 'unit' || $val['fieldname'] == 'specs')
            {
                $this->field[$k]['read'] = '1';
            }
        }
        return parent::edit($id); // TODO: Change the autogenerated stub
    }

    //添加订单
    public function order($id=0)
    {
        $m = $this->m->where('id', $id)->find();


        if (Request::instance()->isPost())
        {
            $or = new Order;
            $or->ordernum = Request::instance()->post('ordernum');
            $or->mid = $m['mid'];
            $or->update = time();
            $or->save();

            $pids =  Request::instance()->post('pid/a');
            $specss =  Request::instance()->post('specs/a');
            $units =  Request::instance()->post('unit/a');
            $nums =  Request::instance()->post('num/a');


            foreach ($pids as $k=>$v)
            {
                $oitem = new Orderitem;
                $oitem->oid = $or->id;
                $oitem->pid = $pids[$k];
                $oitem->specs = $specss[$k];
                $oitem->unit = $units[$k];
                $oitem->num = $nums[$k];
                $oitem->update = time();
                $oitem->save();
            }
            $this->success('添加成功！','/admin/members/index');
        }

        //初始化订单
        $temp['id'] = $m['id'];
        $temp['ordernum'] = $m['ordernum'];
        $temp['mid'] = $m['mid'];
        $this->assign('temp',$temp);
        $this->assign('mid',$m->getData('mid'));

        $orderitem = Orderitem::all(['oid'=>$m['id']]);
        $this->assign('orderitem',$orderitem);


        //准备产品分类
        $catearr = array();
        $cate = new Category;
        $cate->getProTree(0, $catearr);
        $catearr = $cate->getSelectArray($catearr);
        $this->assign('catearr',$catearr);


        $this->assign('title','添加订单-'.$this->title);
        return view('');
    }

    public function additem()
    {
        if (Request::instance()->isPost())
        {
            $pid =  Request::instance()->post('pid');
            $specs =  Request::instance()->post('specs');
            $unit =  Request::instance()->post('unit');
            $num =  Request::instance()->post('num');
            $oid =  Request::instance()->post('oid');

            $oitem = new Orderitem;
            $oitem->oid = $oid;
            $oitem->pid = $pid;
            $oitem->specs = $specs;
            $oitem->unit = $unit;
            $oitem->num = $num;
            $oitem->update = time();
            $oitem->save();

            return json_encode(array('id'=>$oitem['id'], 'pid'=>$oitem->pid, 'title'=>$oitem->pid, 'specs'=> $oitem->specs, 'unit'=>$oitem->unit, 'num'=>$oitem->num, 'oid'=>$oitem->oid));

        }
    }

    public function delitem($id)
    {
        $oitem = Orderitem::get($id);
        $oitem ->delete();
        $re = array(
            'status'=>1,
        );
        return json_encode($re);
    }

    public function daochu($id=0){
        $xlsName  = "order";
        //获取订单
        $m = $this->m->where('id', $id)->find();

        $xlsCell  = array(
            array('id','序号'),
            array('pname','商品名称'),
            array('specs','规格'),
            array('unit','单位'),
            array('num','数量'),
            array('supplier_id','单价'),
            array('original_price','金额')
        );
        $orderitem = Orderitem::all(['oid'=>$m['id']]);

        $xlsData = array();
        if(!empty($orderitem))
        {
            foreach ($orderitem as $k=>$val)
            {
                $ls = array(
                    'id'=>$k+1,
                    'pname'=>$val['pid'],
                    'specs'=>$val['specs'],
                    'unit'=>$val['unit'],
                    'num'=>$val['num'],
                    'supplier_id'=>'',
                    'original_price'=>''
                );
                $xlsData[] = $ls;
            }
        }

        $this->exportExcel($xlsName,$xlsCell,$xlsData);;
    }


    public function exportExcel($expTitle,$expCellName,$expTableData)
    {
        $com ='山东华油新能源科技股份有限公司';
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称

        $fileName = $xlsTitle . date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        import('phpexcel.PHPExcel.Style.PHPExcel_Style_Alignment', EXTEND_PATH);

        // 引入核心文件
        import('phpexcel.PHPExcel', EXTEND_PATH);

        $objPHPExcel = new \PHPExcel();
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(7.48);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30.48);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(8.82);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(9.27);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8.48);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(13.71);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12.38);


        // 设置行高度
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(26); //设置默认行高

        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);


        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');//合并单元格
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(60);
        /*实例化插入图片类*/

        $objDrawing = new \PHPExcel_Worksheet_Drawing();

        /*设置图片路径 切记：只能是本地图片*/

        $objDrawing->setPath(getcwd().'/theme/images/logo.png');

        /*设置图片高度*/

        $objDrawing->setHeight(80);

        /*设置图片要插入的单元格*/
        $objDrawing->setCoordinates("A1");
        /*设置图片所在单元格的格式*/
        $objDrawing->setOffsetX(80);
        $objDrawing->setRotation(20);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(50);
        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

        $objPHPExcel->getActiveSheet(0)->mergeCells('A2:' . $cellName[$cellNum - 1] . '2');//合并单元格





        $styleArray = array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => '000'),
                'size'  => 18,
                'name'  => 'Verdana'
            ));
        $objPHPExcel->getActiveSheet()->getCell('A2')->setValue('Some text');
        $objPHPExcel->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[0]. '2',$com.'销售合同书');

        //第三行
        $objPHPExcel->getActiveSheet(0)->setCellValue('A3','供方：');
        $objPHPExcel->getActiveSheet(0)->mergeCells('B3:C3');//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('B3',$com);
        $objPHPExcel->getActiveSheet(0)->mergeCells('D3:E3');//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('D3','签订地点：');
        $objPHPExcel->getActiveSheet(0)->mergeCells('F3:G3');//合并单元格

        //第四行
        $objPHPExcel->getActiveSheet(0)->setCellValue('A4','需方：');
        $objPHPExcel->getActiveSheet(0)->mergeCells('B4:C4');//合并单元格
        $objPHPExcel->getActiveSheet(0)->mergeCells('D4:E4');//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('D4','签订时间：');
        $objPHPExcel->getActiveSheet(0)->mergeCells('F4:G4');//合并单元格

        //第五行

        $objPHPExcel->getActiveSheet(0)->mergeCells('A5:C5');//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('A5','签订方式：');
        $objPHPExcel->getActiveSheet(0)->mergeCells('D5:E5');//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('D5','结算方式：');
        $objPHPExcel->getActiveSheet(0)->mergeCells('F5:G5');//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('F5','1、现金  2、承兑');

        //第六行
        $objPHPExcel->getActiveSheet(0)->mergeCells('A6:G6');//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('A6','一、配货单');

        //边框
        $style_array = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        //第7行
        $row = 7;
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':G'.$row)->applyFromArray($style_array);

        $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$row,'联系人');
        $objPHPExcel->getActiveSheet(0)->mergeCells('B'.$row.':C'.$row);//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('D'.$row,'地址');
        $objPHPExcel->getActiveSheet(0)->mergeCells('E'.$row.':G'.$row);//合并单元格

        //第8行
        $row = 8;
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':G'.$row)->applyFromArray($style_array);
        $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$row,'手机');
        $objPHPExcel->getActiveSheet(0)->mergeCells('B'.$row.':C'.$row);//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('D'.$row,'电话');
        $objPHPExcel->getActiveSheet(0)->mergeCells('E'.$row.':G'.$row);//合并单元格

        $row = 9;
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':G'.$row)->applyFromArray($style_array);
        for ($i = 0; $i < $cellNum; $i++) {
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] .'9')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '9', $expCellName[$i][1]);
        }

        // Miscellaneous glyphs, UTF-8
        for ($i = 0; $i < $dataNum; $i++) {
            $row =  $i + 10;
            $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':G'.$row)->applyFromArray($style_array);
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$j] .$row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 10), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        //第n行
        $row =$dataNum+9+1;
        $objPHPExcel->getActiveSheet()->getStyle('A'.$row.':G'.$row)->applyFromArray($style_array);
        $objPHPExcel->getActiveSheet(0)->mergeCells('A'.$row.':D'.$row);//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$row,'合计（大写）');


        //第n行
        $row =$dataNum+9+2;
        $objPHPExcel->getActiveSheet(0)->mergeCells('A'.$row.':G'.$row);//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$row,'二、本合同履行中如发生争议，供需双方应协商解决，协商不成，到济宁法院诉讼解决。');

        //第n行
        $row =$dataNum+9+3;
        $objPHPExcel->getActiveSheet(0)->mergeCells('A'.$row.':G'.$row);//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$row,'三、本合同一式两份，供需双方各执一份。双方盖章签字后生效。传真件有效。');

        //第n行
        $row =$dataNum+9+4;
        $objPHPExcel->getActiveSheet(0)->mergeCells('A'.$row.':C'.$row);//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$row,'供方签章：'.$com);
        $objPHPExcel->getActiveSheet(0)->mergeCells('D'.$row.':E'.$row);//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('D'.$row,'需方签章：');

        //第n行
        $row =$dataNum+9+5;
        $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$row,'签字：' );
        $objPHPExcel->getActiveSheet(0)->mergeCells('B'.$row.':C'.$row);//合并单元格

        $objPHPExcel->getActiveSheet(0)->mergeCells('D'.$row.':E'.$row);//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('D'.$row,'签字：');

        //第n行
        $row =$dataNum+9+6;
        $objPHPExcel->getActiveSheet(0)->mergeCells('A'.$row.':G'.$row);//合并单元格
        $objPHPExcel->getActiveSheet(0)->setCellValue('A'.$row,'备注：');

        //第n行
        $row =$dataNum+9+7;
        $objPHPExcel->getActiveSheet(0)->mergeCells('A'.$row.':G'.$row);//合并单元格


        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }


}