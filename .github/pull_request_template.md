# Description
โปรดระบุสรุปของการเปลี่ยนแปลงและ Issue ที่เกี่ยวข้อง

# สำหรับเจ้าของ PR 
## Type of change 

- [ ] Bug fix: แก้ไขข้อผิดพลาด (การแก้ไขที่ไม่กระทบฟีเจอร์เดิม)
- [ ] New feature: เพิ่มความสามารถใหม่ (การเพิ่มฟีเจอร์ที่ไม่กระทบฟีเจอร์เดิม)
- [ ] Breaking change: การเปลี่ยนแปลงที่กระทบระบบเดิม(การแก้ไขหรือเพิ่มฟีเจอร์ที่ทำให้ฟังก์ชันเดิมทำงานไม่เหมือนเดิม หรือหยุดทำงาน)
- [ ] Documentation: การเปลี่ยนแปลงนี้มีการอัปเดตเอกสารประกอบด้วย

# Checklist: 
- [ ] Self-review: ฉันได้ตรวจสอบโค้ดของตัวเองอย่างละเอียดแล้ว
- [ ] Clean Code: โค้ดอ่านง่าย ไม่มี Log ที่ไม่จำเป็น และตั้งชื่อตัวแปรสื่อความหมาย
- [ ] No Warnings: รันแล้วไม่พบข้อผิดพลาดหรือ Warning ใหม่
- [ ] Latest Master: ได้ทำการ Merge หรือ Rebase โค้ดล่าสุดจาก Branch หลักแล้ว

-------------------------------------------------------------
# สำหรับคน review code

# Backend (Laravel / PHP) 
- [ ] มีคอมเมนต์ส่วนหัวครบถ้วน
- [ ] “ชื่อ Class ใช้ PascalCase”
      “Methods → ใช้ camelCase”
      “Constants → ตัวใหญ่ทั้งหมด”
- [ ] ไม่ปิดแท็ก PHP และไม่ทำ class + side effect ในไฟล์เดียว 
      ตัวอย่าง : <?php 
      echo "Hello";   side effect    
      class User { }  declare
- [ ] ตรวจสอบ visibility ของ property/method ครบทุกตัว
- [ ] Control structures (if/foreach/etc.) เว้นวรรคถูกต้อง
- [ ] Operators เว้นวรรคตามมาตรฐาน $x = $y + 2; มี space รอบ binary operator
- [ ] ใช้ Eloquent/Query Builder แทน SQL string
- [ ] ไม่มีการแก้ไข .env และไม่ commit .env
- [ ] ไม่มีการแก้ไขในส่วน Backend

# Frontend (Vue.js)
- [ ] มีคอมเมนต์ส่วนหัวครบถ้วน
- [ ] ชื่อ Component ใช้ PascalCase (เช่น DashboardCard.vue)
- [ ] ชื่อไฟล์ Router/Store/Utility JS/TS ใช้ kebab-case หรือ camelCase สม่ำเสมอ Ex. index.js
- [ ] CSS และ Asset ใช้ kebab-case
- [ ] ไม่มี console.log ที่ไม่จำเป็น
- [ ] ไม่มีการแก้ไขในส่วน Backend

# Routes
- [ ] มีคอมเมนต์ส่วนหัวครบถ้วน “ชื่อไฟล์ / คำอธิบาย / ชื่อผู้เขียน / วันที่แก้ไข”
- [ ] ทุกฟังก์ชันของ Route มีคอมเมนต์ครบ 7 หัวข้อ 
    1.ชื่อฟังก์ชัน (Path) 
    2.Http request 
    3.คำอธิบายฟังก์ชัน 
    4.Input 
    5.Output 
    6.ชื่อผู้เขียน/แก้ไข 
    7.วันที่จัดทำ/แก้ไข
- [ ] URI ใช้ kebab-case, เป็นคำนาม, ใช้พหูพจน์ และ RESTful  /api/events, /api/events/{event}/attendees
- [ ] Route name: ใช้จุดคั่นตามมาตรฐาน Laravel เช่น events.index
- [ ]  ไม่ใช้ /getSomething หรือ camelCase ใน path
- [ ] ไม่มีการแก้ไขในส่วน Route

# Service / Component Comments
- [ ] ไฟล์ Service มีคอมเมนต์หัวไฟล์ครบ “ชื่อไฟล์ / คำอธิบาย / ชื่อผู้เขียน / วันที่แก้ไข”
- [ ] Component (.vue) มีคอมเมนต์ส่วนหัวครบ (ชื่อไฟล์ / คำอธิบาย / Input / Output / คนแก้ไข / วันที่แก้ไข)
- [ ] ตัวแปร/บรรทัดสำคัญมีคอมเมนต์อธิบาย
- [ ] ไม่มีการแก้ไขในส่วน Route

# Database
- [ ] ชื่อตารางเป็น snake_case พหูพจน์
- [ ] Primary key: id
- [ ] Foreign key: <singular>_id  เช่น event_id”
- [ ] ทุกตารางและทุกฟิลด์มีคอมเมนต์อธิบายครบถ้วน
- [ ] Soft delete (deleted_at) ใช้ในตารางที่ต้องกู้คืนข้อมูล
- [ ] เวลาที่บันทึกเป็น UTC
- [ ] ไม่มีการแก้ไขในส่วน Database