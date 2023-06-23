<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Files\File;
use CodeIgniter\Image\Exceptions\ImageException;
use CodeIgniter\Image\Image;
use CodeIgniter\Images\ImageManipulation;

class UploadController extends Controller
{
    public function upload()
    {
        $file = $this->request->getFile('image');

        if ($file->isValid() && !$file->hasMoved()) {
            // ตรวจสอบขนาดไฟล์ภาพ
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            if ($file->getSize() > $maxFileSize) {
                return $this->fail('File size exceeds the limit.');
            }

            // ตรวจสอบประเภทของไฟล์ภาพ
            $allowedTypes = ['image/jpeg', 'image/gif', 'image/png', 'image/webp'];
            if (!in_array($file->getClientMimeType(), $allowedTypes)) {
                return $this->fail('Invalid file type. Only JPEG, GIF, PNG, and WebP files are allowed.');
            }

            // เช็คแฮช SHA1 ของไฟล์รูป
            $sha1Hash = sha1_file($file->getTempName());

            // เช็คแฮช SHA1 ในฐานข้อมูล
            $imageModel = new ImageModel();
            $existingFile = $imageModel->where('sha1_hash', $sha1Hash)->first();

            if ($existingFile) {
                return $this->response->setJSON([
                    'filename' => $existingFile['filename']
                ]);
            }

            // สร้างโฟลเดอร์ตามรูปแบบตามปีและเดือน
            $yearMonth = date('Y/m');
            $uploadPath = './public/uploads/' . $yearMonth;
            $thumbPath = './public/uploads/thumb/' . $yearMonth;
            $indexFilePath = $uploadPath . '/index.html';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
              if (!file_exists($indexFilePath)) {
              // สร้างไฟล์ index.html ใหม่
              $indexContent = '<html><body><h1>Welcome to the Uploads Folder</h1></body></html>';
              file_put_contents($indexFilePath, $indexContent);
              }
            }

            if (!is_dir($thumbPath)) {
                mkdir($thumbPath, 0777, true);
            }

            // สร้างชื่อไฟล์ใหม่ที่รวมถึงวันและเวลา
            $newName = date('YmdHis') . '_' . $file->getRandomName();

            // สร้างเส้นทางไฟล์ที่เต็มใหม่
            $newFilePath = $uploadPath . '/' . $newName;
            $newThumbPath = $thumbPath . '/' . $newName;

            // ย้ายไฟล์ไปยังโฟลเดอร์ที่ถูกสร้าง
            $file->move($uploadPath, $newName);

            // สร้างรูปย่อขนาด 200x200 พิกเซล
            $image = new Image($newFilePath, true);
            $image->resize(200, 200, true);
            $image->save($newThumbPath);

            // บันทึกชื่อไฟล์ลงในฐานข้อมูล
            $imageModel->insert([
                'filename' => $newName,
                'sha1_hash' => $sha1Hash
            ]);

            return $this->response->setJSON([
                'filename' => $newName
            ]);
        }

        return $this->fail('Failed to upload file.');
    }
}
