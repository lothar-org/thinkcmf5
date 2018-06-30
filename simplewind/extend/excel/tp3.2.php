<?php
// 导出exl
    public function look_down(){
        $id = I('get.id');
        $m = M ('offer_goods');
        $where['offer_id'] = $id;
        $data = $m->field('goods_id,goods_sn,goods_name,barcode,goods_type,price')->select();
        
        // 导出Exl
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Worksheet.Drawing");
        import("Org.Util.PHPExcel.Writer.Excel2007");
        $objPHPExcel = new \PHPExcel();
        
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
    
        $objActSheet = $objPHPExcel->getActiveSheet();
        
        // 水平居中（位置很重要，建议在最初始位置）
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('B1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $objActSheet->setCellValue('A1', '商品货号');
        $objActSheet->setCellValue('B1', '商品名称');
        $objActSheet->setCellValue('C1', '商品图');
        $objActSheet->setCellValue('D1', '商品条码');
        $objActSheet->setCellValue('E1', '商品属性');
        $objActSheet->setCellValue('F1', '报价(港币)');
        // 设置个表格宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(80);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        
        // 垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        
        foreach($data as $k=>$v){
            $k +=2;
            $objActSheet->setCellValue('A'.$k, $v['goods_sn']);    
            $objActSheet->setCellValue('B'.$k, $v['goods_name']);    
                
            
            $img = M('goods')->where('goods_id = '.$v['goods_id'])->field('goods_thumb')->find();
            // 图片生成
            $objDrawing[$k] = new \PHPExcel_Worksheet_Drawing();
            $objDrawing[$k]->setPath('./Upload/'.$img['goods_thumb']);
            // 设置宽度高度
            $objDrawing[$k]->setHeight(80);//照片高度
            $objDrawing[$k]->setWidth(80); //照片宽度
            /*设置图片要插入的单元格*/
            $objDrawing[$k]->setCoordinates('C'.$k);
            // 图片偏移距离
            $objDrawing[$k]->setOffsetX(12);
            $objDrawing[$k]->setOffsetY(12);
            $objDrawing[$k]->setWorksheet($objPHPExcel->getActiveSheet());
            
            // 表格内容
            $objActSheet->setCellValue('D'.$k, $v['barcode']);    
            $objActSheet->setCellValue('E'.$k, $v['goods_type']);    
            $objActSheet->setCellValue('F'.$k, $v['price']);
                
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(80);
            
        }
        
        $fileName = '报价表';
        $date = date("Y-m-d",time());
        $fileName .= "_{$date}.xls";
        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        // $objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        // END    
    }