# 🎯 Button Component - คู่มือการใช้งาน

## 📝 Overview
Button.vue เป็น component เดียวที่รวมทุกประเภทของปุ่มไว้ด้วยกัน โดยทีมสามารถใช้งานได้เหมือนเดิมโดยไม่ต้องแก้ไขโค้ด

## ✅ การใช้งานแบบเดิม (ไม่ต้องแก้โค้ด)

### 1. AddButton (เดิม)
```vue
<!-- เดิม: <AddButton @click="handleAdd">Add</AddButton> -->
<!-- ใหม่: --> 
<Button variant="add" @click="handleAdd">Add</Button>
```

### 2. CreateButton (เดิม)
```vue
<!-- เดิม: <CreateButton @click="handleCreate">Create</CreateButton> -->
<!-- ใหม่: -->
<Button variant="create" @click="handleCreate">Create</Button>
```

### 3. SaveButton (เดิม)
```vue
<!-- เดิม: <SaveButton @click="handleSave">Save</SaveButton> -->
<!-- ใหม่: -->
<Button variant="save" @click="handleSave">Save</Button>
```

### 4. CancelButton (เดิม)
```vue
<!-- เดิม: <CancelButton @click="handleCancel">Cancel</CancelButton> -->
<!-- ใหม่: -->
<Button variant="cancel" @click="handleCancel">Cancel</Button>
```

### 5. BackButton (เดิม)
```vue
<!-- เดิม: <BackButton @click="goBack">Back</BackButton> -->
<!-- ใหม่: -->
<Button variant="back" @click="goBack">Back</Button>
```

### 6. DownloadButton (เดิม)
```vue
<!-- เดิม: <DownloadButton @click="download">Download Template</DownloadButton> -->
<!-- ใหม่: -->
<Button variant="download" @click="download">*Click to download template excel file*</Button>
```

### 7. ImportButton (เดิม)
```vue
<!-- เดิม: <ImportButton @click="importData">Import</ImportButton> -->
<!-- ใหม่: -->
<Button variant="import" @click="importData">Import</Button>
```

### 8. GenerateDataButton (เดิม)
```vue
<!-- เดิม: <GenerateDataButton @click="generate">Generate Data</GenerateDataButton> -->
<!-- ใหม่: -->
<Button variant="generate" @click="generate">Generate Data</Button>
```

### 9. Filter (เดิม)
```vue
<!-- เดิม: <Filter /> (component ปุ่ม filter) -->
<!-- ใหม่: -->
<Button variant="filter" @click="showFilter">Filter</Button>
```

## 🚀 การใช้งานแบบใหม่ (ยืดหยุ่นขึ้น)

### Props ที่รองรับ

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `type` | String | 'button' | HTML button type |
| `label` | String | '' | ข้อความในปุ่ม |
| `icon` | String | '' | ไอคอน Material Symbols |
| `variant` | String | 'primary' | สีและประเภทปุ่ม |
| `size` | String | 'md' | ขนาดปุ่ม |
| `loading` | Boolean | false | แสดง loading spinner |
| `disabled` | Boolean | false | ปิดการใช้งาน |
| `outline` | Boolean | false | สไตล์เส้นขอบ |
| `block` | Boolean | false | เต็มความกว้าง |
| `iconOnly` | Boolean | false | แสดงเฉพาะไอคอน |
| `shape` | String | 'rounded' | รูปแบบมุม |
| `preset` | String | '' | ใช้แทน variant (backward compatibility) |

### Variants ที่รองรับ

#### Action Buttons
- `primary` - ปุ่มหลัก (สีน้ำเงิน)
- `success` - ปุ่มสำเร็จ (สีเขียว)
- `danger` - ปุ่มอันตราย (สีแดง)
- `warning` - ปุ่มเตือน (สีเหลือง)
- `info` - ปุ่มข้อมูล (สีฟ้า)
- `secondary` - ปุ่มรอง (สีเทา)
- `light` - ปุ่มสว่าง (สีขาว)
- `dark` - ปุ่มมืด (สีดำ)

#### Specific Buttons  
- `add` - ปุ่มเพิ่ม (สีแดง + ไอคอน add)
- `create` - ปุ่มสร้าง (สีเขียว + ไอคอน add_circle)
- `save` - ปุ่มบันทึก (สีเขียว + ไอคอน save)
- `cancel` - ปุ่มยกเลิก (สีแดง + ไอคอน close)
- `back` - ปุ่มกลับ (สีเทา + ไอคอน arrow_back)
- `delete` - ปุ่มลบ (สีแดง + ไอคอน delete)
- `edit` - ปุ่มแก้ไข (ไอคอน edit)
- `download` - ปุ่มดาวน์โหลด (สีขาว + ไอคอน download)
- `import` - ปุ่มนำเข้า (สีขาว + ไอคอน download)
- `generate` - ปุ่มสร้างข้อมูล (สีขาว + ไอคอน upload)
- `filter` - ปุ่มกรอง (สีขาว + ไอคอน filter_list)

### Sizes
- `xs` - เล็กสุด
- `sm` - เล็ก  
- `md` - กลาง (default)
- `lg` - ใหญ่
- `xl` - ใหญ่สุด

### Shapes
- `rounded` - มุมโค้ง (default)
- `pill` - รูปแคปซูล
- `square` - มุมแหลม
- `circle` - วงกลม

## 📋 ตัวอย่างการใช้งาน

### 1. ปุ่มพื้นฐาน
```vue
<Button label="Click Me" @click="handleClick" />
<Button variant="success" label="Success" />
<Button variant="danger" outline label="Danger Outline" />
```

### 2. ปุ่มที่มีไอคอน
```vue
<Button label="Save" variant="success" />  <!-- ไอคอนอัตโนมัติ -->
<Button icon="star" label="Favorite" />    <!-- ไอคอนกำหนดเอง -->
<Button label="delete" variant="danger" /> <!-- ไอคอนจาก label -->
```

### 3. ปุ่มขนาดต่างๆ
```vue
<Button size="sm" label="Small" />
<Button size="md" label="Medium" />
<Button size="lg" label="Large" />
```

### 4. ปุ่มสถานะพิเศษ
```vue
<Button loading label="Loading..." />
<Button disabled label="Disabled" />
<Button block label="Full Width" />
```

### 5. ปุ่มเฉพาะไอคอน
```vue
<Button variant="edit" icon-only />
<Button variant="delete" icon-only size="sm" />
<Button icon="settings" icon-only />
```

### 6. การจัดการ Event
```vue
<Button 
  variant="primary" 
  label="Submit" 
  @click="submitForm"
  :loading="isSubmitting"
  :disabled="!isValid"
/>
```

## 🔧 Migration Guide

### ขั้นตอนการเปลี่ยน

1. **Import Button component**
```js
import Button from './components/Button.vue'
```

2. **เปลี่ยน component เดิม**
```vue
<!-- เดิม -->
<CreateButton @click="create">Create</CreateButton>
<SaveButton @click="save">Save</SaveButton>

<!-- ใหม่ -->
<Button variant="create" @click="create">Create</Button>
<Button variant="save" @click="save">Save</Button>
```

3. **ลบ import เดิม** (ถ้าต้องการ)
```js
// ลบบรรทัดเหล่านี้
import CreateButton from './components/CreateButton.vue'
import SaveButton from './components/SaveButton.vue'
import Filter from './components/Button/Filter.vue'
// ... buttons อื่นๆ
```

## ⚠️ หมายเหตุสำคัญ

1. **Backward Compatibility**: ทุก prop และ event ยังใช้งานได้เหมือนเดิม
2. **Auto Icons**: ไอคอนจะถูกเลือกอัตโนมัติตาม variant หรือ label
3. **Custom Styling**: ยังคงรองรับ class และ style ภายนอก
4. **Event Handling**: @click และ event อื่นๆ ทำงานเหมือนเดิม

## 🎨 Style Customization

### CSS Variables (ถ้าต้องการปรับแต่ง)
```css
.button-custom {
  --button-primary-bg: #your-color;
  --button-border-radius: 8px;
  --button-font-family: 'Your Font';
}
```

### Class Override
```vue
<Button 
  variant="primary" 
  label="Custom Style"
  class="my-custom-button-class"
/>
```

## 🚀 สรุป

Button.vue ใหม่จะ:
- ✅ ทำงานเหมือน component เดิมทุกตัว
- ✅ ไม่ต้องแก้ไขโค้ดที่มีอยู่
- ✅ เพิ่มความยืดหยุ่นในการใช้งาน
- ✅ ลดจำนวน component ที่ต้อง maintain
- ✅ รองรับการขยายตัวในอนาคต

แค่เปลี่ยน import และ component name เท่านั้น! 🎉