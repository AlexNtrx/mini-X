# Mini X (minisome) - Implementation Log

บันทึกประวัติและขั้นตอนการแก้ไขบัคของระบบ Mini X อย่างละเอียดทีละขั้นตอน

---

## [Point 1] fix(auth): restore user avatar upon account reactivation

- **ปัญหา**: เมื่อผู้ใช้ที่ถูก Soft-delete ล็อกอินเข้ามาและทำการกู้คืนบัญชี (`confirm_reactivation`) รูปโปรไฟล์ (`avatar`) ไม่ถูกเก็บลงในตัวแปร Session ชั่วคราว และไม่ถูกเซ็ตลงใน `$_SESSION['avatar']` ทำให้หลังกู้คืนบัญชี Avatar จะไม่แสดงในระบบ
- **ไฟล์ที่แก้ไข**:
  - `handlers/auth-handlers.php`: บันทึก `$_SESSION['pending_reactivation_avatar']` ขณะตรวจพบบัญชีถูกระงับ และกู้คืนลงใน `$_SESSION['avatar']` เมื่อยืนยันการกู้คืน พร้อมทั้งเคลียร์ Session เมื่อกดยืนยันหรือยกเลิก
- **การทดสอบ**:
  - ตรวจสอบไวยากรณ์ด้วย `php -l handlers/auth-handlers.php` (ผ่าน 100%)
- **สถานะ**: สำเร็จ (Fixed)

## [Point 2] fix(js): resolve dynamic base path for notification polling

- **ปัญหา**: ใน `js/script.js` มีการฮาร์ดโค้ดพาธ `/mini-X` ทำให้เวลาติดตั้งโปรเจกต์ในโฟลเดอร์ชื่ออื่น (เช่น `minisome` บน XAMPP) หรือเปิด URL ที่ไม่มี trailing slash การ fetch API ไปยัง `/api/notifications-count.php` และ `/api/get-new-notifications.php` จะกลายเป็น 404 Not Found
- **ไฟล์ที่แก้ไข**:
  - `js/script.js`: เพิ่มฟังก์ชัน `getAppBasePath()` คำนวณ Base URL ของโปรเจกต์จาก `window.location.pathname` แบบไดนามิก รองรับทั้งการเข้าถึงผ่านชื่อโฟลเดอร์ใดๆ หรือ Root path
- **การทดสอบ**:
  - ตรวจสอบความถูกต้องของตรรกะ Path resolution สำหรับ `/minisome/index.php`, `/mini-X/index.php`, และรูท
- **สถานะ**: สำเร็จ (Fixed)

## [Point 3] fix(notifications): cleanup like notifications upon unlike action

- **ปัญหา**: เมื่อผู้ใช้กดยกเลิกถูกใจ (Unlike) ระบบลบเฉพาะข้อมูลในตาราง `likes` แต่ไม่ได้ลบการแจ้งเตือนในตาราง `notifications` ทำให้เจ้าของโพสต์ยังคงเห็นการแจ้งเตือนค้างอยู่ และหากกดถูกใจซ้ำหลายครั้งจะทำให้เกิดการแจ้งเตือนซ้ำซ้อน
- **ไฟล์ที่แก้ไข**:
  - `functions/notifications.php`: เพิ่มฟังก์ชัน `removeNotification($conn, $userId, $actorId, $postId, $type)` และป้องกันการสร้าง Like notification ซ้ำซ้อนใน `addNotification()`
  - `handlers/interaction-handlers.php`: เรียกใช้ `removeNotification()` เพื่อล้างการแจ้งเตือนทันทีเมื่อตรวจพบว่าผู้ใช้ทำการ Unlike
- **การทดสอบ**:
  - ตรวจสอบไวยากรณ์ PHP ด้วย `php -l` ผ่านฉลุยทั้งสองไฟล์
- **สถานะ**: สำเร็จ (Fixed)
