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

## [Point 4] fix(profile): allow case-sensitivity username update for same user

- **ปัญหา**: เมื่อผู้ใช้ต้องการเปลี่ยนตัวพิมพ์เล็ก/ใหญ่ของชื่อตัวเอง (เช่น `alex` เป็น `Alex`) ในหน้า Profile ระบบจะแจ้ง Error ว่าชื่อถูกใช้งานแล้ว เนื่องจาก PHP เปรียบเทียบแบบ Case-sensitive แต่ MySQL เปรียบเทียบแบบ Case-insensitive และฟังก์ชัน `isUsernameExists` เดิมไม่ได้รองรับการยกเว้น ID ของผู้ใช้ปัจจุบัน
- **ไฟล์ที่แก้ไข**:
  - `functions/auth.php`: ปรับปรุงฟังก์ชัน `isUsernameExists($conn, $username, $excludeUserId = 0)` ให้รองรับ Parameter คัดกรอง ID ตนเองออก (เหมือน `isEmailExists`)
  - `handlers/setting-handlers.php`: ส่ง `$userId` เข้าไปยัง `isUsernameExists()` เพื่อให้ผู้ใช้สามารถปรับแต่งตัวพิมพ์ชื่อตนเองได้โดยไม่ติดข้อผิดพลาด
- **การทดสอบ**:
  - ตรวจสอบไวยากรณ์ PHP ด้วย `php -l` ผ่านฉลุยทั้งสองไฟล์
- **สถานะ**: สำเร็จ (Fixed)

## [Point 5] fix(ui): handle utf-8 multibyte username initials safely

- **ปัญหา**: โค้ดเดิมใช้ `substr()` ในการตัดตัวอักษรย่อสำหรับ Avatar เมื่อผู้ใช้ไม่มีรูปภาพ ซึ่ง `substr()` ทำงานแบบ Byte-based ทำให้อักขระพิเศษ, ภาษาฟินแลนด์ (`Ä`, `Ö`), ภาษาไทย หรือ Emoji ถูกตัดไบต์ขาดครึ่ง แสดงผลเป็นตัวอักขระเสีย (``) และไม่มีการครอบ `htmlspecialchars()`
- **ไฟล์ที่แก้ไข**:
  - `functions/auth.php`: เพิ่มฟังก์ชันกลาง `getUserInitials($username)` ใช้ `mb_substr()` และ `mb_strtoupper()` รองรับ UTF-8 พร้อมครอบ `htmlspecialchars()` ป้องกัน XSS
  - `components/sidebar.php`, `components/kortit.php`, `components/tekstikenttä.php`, `pages/profile.php`, `pages/setting.php`: ปรับเปลี่ยนให้เรียกใช้ `getUserInitials()` แทนการใช้ `substr()`
- **การทดสอบ**:
  - ตรวจสอบไวยากรณ์ด้วย `php -l` ทุกไฟล์ผ่านฉลุย
  - ทดสอบการตัดชื่อ 'alex' -> 'AL', 'äijä' -> 'ÄI', 'สมชาย' -> 'สม', '<script>' -> '&lt;S' ผ่าน CLI ได้ผลถูกต้องสมบูรณ์
- **สถานะ**: สำเร็จ (Fixed)

## [Point 6] fix(posts): add validation error messages for empty or invalid posts and comments

- **ปัญหา**: เมื่อผู้ใช้ส่งโพสต์หรือคอมเมนต์ที่เป็นค่าว่าง (เช่น เคาะเว้นวรรคล้วน) หรือมีความยาวเกิน 140 ตัวอักษร โค้ดเดิมจะเพิกเฉยเงียบๆ ไม่มีการกำหนดตัวแปร `$error` ทำให้ไม่มีกล่องแจ้งเตือนใดๆ แสดงบนหน้าเว็บ ผู้ใช้จึงไม่ทราบว่าข้อความไม่ถูกบันทึกเพราะเหตุใด
- **ไฟล์ที่แก้ไข**:
  - `handlers/post-handlers.php`: เพิ่มการกำหนด `$error = "Julkaisun teksti ei voi olla tyhjä."` และข้อความเตือนเมื่อเกิน 140 ตัวอักษร ทั้งในการสร้างโพสต์และการแก้ไขโพสต์
  - `handlers/interaction-handlers.php`: เพิ่มการตรวจสอบและกำหนด `$error` สำหรับการเพิ่มคอมเมนต์
- **การทดสอบ**:
  - ตรวจสอบไวยากรณ์ด้วย `php -l` ผ่านฉลุยทั้งสองไฟล์
- **สถานะ**: สำเร็จ (Fixed)

## [Point 7] fix(mail): normalize directory separator for password reset link

- **ปัญหา**: บน Windows บางคอนฟิกูเรชัน `dirname($_SERVER['SCRIPT_NAME'])` คืนค่า Path ที่คั่นด้วย Backslash (`\handlers`) ทำให้ Regex `/(\/pages|\/handlers)$/` จับคู่ไม่ติด ส่งผลให้ `$basePath` ยังติดโฟลเดอร์ handlers เกิดเป็นลิงก์กู้รหัสผ่านที่พาธผิดในอีเมล
- **ไฟล์ที่แก้ไข**:
  - `functions/password-reset.php`: แปลง Backslash ทั้งหมดให้เป็น Slash ด้วย `str_replace('\\', '/', ...)` ก่อนตัดชื่อโฟลเดอร์ และใช้ `rtrim` รับประกันความถูกต้องของ URL
- **การทดสอบ**:
  - ตรวจสอบไวยากรณ์ด้วย `php -l` ผ่านฉลุย
- **สถานะ**: สำเร็จ (Fixed)

## [Point 8] fix(security): secure direct access to pages and fix include paths

- **ปัญหา**: ไฟล์ในโฟลเดอร์ `pages/` (เช่น `home.php`, `profile.php`, `notifications.php`, `selaa.php`, `setting.php`) มีการใช้ `require_once "functions/init.php"` ซึ่งเป็น Relative path จาก Working Directory ทำให้หากมีคนเปิด URL ไฟล์ตรงๆ ผ่านเบราว์เซอร์ จะเกิด PHP Fatal Error เนื่องจากหา Path ไม่เจอ อีกทั้งไฟล์ยังไม่มีการตรวจสอบ Session ในระดับไฟล์ ทำให้เสี่ยงต่อการหลุดของโครงสร้างหน้าเว็บเมื่อไม่มีสิทธิ์เข้าถึง
- **ไฟล์ที่แก้ไข**:
  - `pages/home.php`, `pages/profile.php`, `pages/notifications.php`, `pages/selaa.php`, `pages/setting.php`: ปรับปรุง Include path เป็น `__DIR__ . "/../functions/init.php"`, เพิ่มการตรวจสอบ Session หากยังไม่ได้ล็อกอินให้ Redirect ไปยังหน้าหลักทันที และเริ่มต้น `$conn = dbConnect()` หากยังไม่ได้เปิดการเชื่อมต่อ
- **การทดสอบ**:
  - ตรวจสอบไวยากรณ์ด้วย `php -l` ทุกไฟล์ผ่าน 100%
- **สถานะ**: สำเร็จ (Fixed)

## [Point 9] fix(security): prevent open redirect by validating referer host

- **ปัญหา**: โค้ดเดิมนำค่า `$_SERVER['HTTP_REFERER']` มาใช้ทำ Header Location Redirect ทันทีโดยไม่มีการตรวจสอบ Hostname ทำให้มีช่องโหว่ Open Redirect ซึ่งอาจถูกผู้ไม่ประสงค์ดีหลอกล่อให้ Redirect ผู้ใช้ไปยังเว็บไซต์อันตรายภายนอกได้
- **ไฟล์ที่แก้ไข**:
  - `functions/init.php`: เพิ่มฟังก์ชัน `getSafeRedirectUrl($default = 'index.php')` เพื่อตรวจสอบความปลอดภัยของ Host ใน Referer โดยอนุญาตเฉพาะ Domain/Host เดียวกันกับเซิร์ฟเวอร์เท่านั้น
  - `handlers/post-handlers.php`, `handlers/interaction-handlers.php`: ปรับเปลี่ยนให้ใช้ `getSafeRedirectUrl("index.php")` เพื่อป้องกันการถูก Redirect ออกนอกระบบ
- **การทดสอบ**:
  - ตรวจสอบไวยากรณ์ด้วย `php -l` ผ่านฉลุยทุกไฟล์
  - ทดสอบจำลอง Referer ปลอม ('http://evil.com/phishing') ระบบบล็อกและคืนค่า 'index.php' อย่างปลอดภัย ส่วน Referer ภายในระบบทำงานได้ตามปกติ
- **สถานะ**: สำเร็จ (Fixed)
