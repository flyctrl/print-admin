<?php

namespace app\api\controller;

use app\api\controller\BaseController;
use FPDF;

class Image2PdfController extends BaseController
{
    /**
     * 多图转PDF
     * @param string $direction 目标方向 (portrait/landscape)
     * @param array $images 图片数组
     * @return \think\Response
     */
    public function convert()
    {
        ini_set('memory_limit', '512M');
        $direction = input('direction', 'portrait');
        $images = input('images');
        
        if (empty($images)) {
            return json(['code' => 0, 'msg' => '请上传图片']);
        }
        $images = explode(',', $images);
        try {
            $pdf = new \FPDF($direction === 'portrait' ? 'P' : 'L', 'mm', 'A4');
            
            // 设置文档信息
            $pdf->SetCreator('Image2PDF Converter');
            $pdf->SetAuthor('System');
            $pdf->SetTitle('Converted Images');
            
            // 设置自动分页
            $pdf->SetAutoPageBreak(TRUE, 20);
            
            // 创建临时目录
            $tempDir = ROOT_PATH . 'public/uploads/temp/' . date('YmdHis');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            foreach ($images as $image) {
                $imagePath = ROOT_PATH . 'public/' . ltrim($image, '/');
                if (!file_exists($imagePath)) continue;

                list($imageWidth, $imageHeight, $imageType) = getimagesize($imagePath);

                // 判断PDF页面方向
                $isPdfPortrait = $direction === 'portrait';
                $isImagePortrait = $imageHeight > $imageWidth;

                $finalImagePath = $imagePath;

                // 如果方向不一致，旋转图片
                if ($isPdfPortrait !== $isImagePortrait) {
                    $src = imagecreatefromstring(file_get_contents($imagePath));
                    if ($src) {
                        $rotated = imagerotate($src, 90, 0);
                        $tempImagePath = $tempDir . '/' . uniqid('rotated_') . '.jpg';
                        imagejpeg($rotated, $tempImagePath, 100);
                        imagedestroy($src);
                        imagedestroy($rotated);
                        $finalImagePath = $tempImagePath;
                        // 重新获取旋转后图片的宽高
                        list($imageWidth, $imageHeight) = getimagesize($finalImagePath);
                    }
                }

                // 获取PDF页面尺寸
                $pageWidth = $pdf->GetPageWidth();
                $pageHeight = $pdf->GetPageHeight();
                
                // 假设DPI为300
                $dpi = 300;
                $imageWidthMM = $imageWidth / $dpi * 25.4;
                $imageHeightMM = $imageHeight / $dpi * 25.4;
                
                // 设置边距
                $margin = 10;

                // 可用区域
                $availableWidth = $pageWidth - 2 * $margin;
                $availableHeight = $pageHeight - 2 * $margin;

                // 最大化等比例缩放
                $ratio = min($availableWidth / $imageWidthMM, $availableHeight / $imageHeightMM);
                $newWidth = $imageWidthMM * $ratio;
                $newHeight = $imageHeightMM * $ratio;

                // 居中（在边距内居中）
                $x = $margin + ($availableWidth - $newWidth) / 2;
                $y = $margin + ($availableHeight - $newHeight) / 2;
                
                $pdf->AddPage();
                $pdf->Image($finalImagePath, $x, $y, $newWidth, $newHeight);
            }
            
            // 生成PDF文件
            $pdfPath = '/uploads/pdf/' . date('YmdHis') . '.pdf';
            $fullPdfPath = ROOT_PATH . 'public' . $pdfPath;
            if (!is_dir(dirname($fullPdfPath))) {
                mkdir(dirname($fullPdfPath), 0777, true);
            }
            $pdf->Output('F', $fullPdfPath);
            
            // 清理临时文件
            $this->deleteDir($tempDir);
            
            return json([
                'code' => 20000,
                'msg' => '转换成功',
                'data' => [
                    'pdf_url' => $pdfPath
                ]
            ]);
            
        } catch (\Exception $e) {
            // 确保清理临时文件
            if (isset($tempDir) && is_dir($tempDir)) {
                $this->deleteDir($tempDir);
            }
            return json(['code' => 40000, 'msg' => '转换失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 递归删除目录
     * @param string $dir 目录路径
     * @return bool
     */
    private function deleteDir($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDir($path) : unlink($path);
        }
        
        return rmdir($dir);
    }
}