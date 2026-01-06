<template>
  <div class="relative" ref="dropdownContainer">
    <button 
      @click="showMenu = !showMenu"
      class="inline-flex h-11 items-center gap-2 rounded-lg bg-white border border-gray-300 px-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors"
      :disabled="disabled"
      :class="{'opacity-50 cursor-not-allowed': disabled}"
      :title="disabled ? 'กรุณาเลือกกิจกรรมก่อน Export' : 'Export ข้อมูลกิจกรรม'"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
      </svg>
      Export
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" :class="{'rotate-180': showMenu}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>
    
    <!-- Dropdown Menu -->
    <div 
      v-if="showMenu"
      class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
    >
      <button
        @click="exportToPDF"
        class="w-full px-4 py-3 text-left text-gray-700 hover:bg-gray-50 flex items-center gap-3 rounded-t-lg transition-colors"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        <span class="font-medium">Export as PDF</span>
      </button>
      <button
        @click="exportToExcel"
        class="w-full px-4 py-3 text-left text-gray-700 hover:bg-gray-50 flex items-center gap-3 rounded-b-lg transition-colors"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="font-medium">Export as Excel</span>
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import ExcelJS from 'exceljs';

axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

export default {
  name: 'ExportDropdown',
  props: {
    selectedEvents: {
      type: Array,
      required: true,
      default: () => []
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      showMenu: false,
      isExporting: false,
      exportProgress: {
        current: 0,
        total: 0,
        eventName: ''
      }
    };
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    handleClickOutside(event) {
      const dropdown = this.$refs.dropdownContainer;
      if (dropdown && !dropdown.contains(event.target) && this.showMenu) {
        this.showMenu = false;
      }
    },

    async exportToPDF() {
      this.showMenu = false;
      
      if (this.selectedEvents.length === 0) {
        alert('กรุณาเลือกกิจกรรมก่อน Export');
        return;
      }

      if (this.isExporting) {
        alert('กำลัง Export อยู่ กรุณารอสักครู่...');
        return;
      }

      this.isExporting = true;
      this.$emit('export-start');

      try {
        // Export แยกไฟล์สำหรับแต่ละ event ที่เลือก
        for (let i = 0; i < this.selectedEvents.length; i++) {
          const event = this.selectedEvents[i];
          
          // อัปเดต progress
          this.exportProgress.current = i + 1;
          this.exportProgress.total = this.selectedEvents.length;
          this.exportProgress.eventName = event.evn_title || 'Event';
          
          this.$emit('export-progress', {
            current: this.exportProgress.current,
            total: this.exportProgress.total,
            eventName: this.exportProgress.eventName
          });
          
          await this.exportSingleEventToPDF(event);
          
          // หน่วงเวลาระหว่างการ export แต่ละไฟล์ (เพื่อหลีกเลี่ยง popup blocker)
          if (i < this.selectedEvents.length - 1) {
            await new Promise(resolve => setTimeout(resolve, 1500));
          }
        }
        
        this.$emit('export-complete', {
          type: 'PDF',
          count: this.selectedEvents.length
        });
        
        console.log(`PDF Export completed: ${this.selectedEvents.length} event(s)`);
      } catch (error) {
        console.error('Export error:', error);
        this.$emit('export-error', error);
        alert('เกิดข้อผิดพลาดในการ Export ข้อมูล: ' + error.message);
      } finally {
        this.isExporting = false;
        this.exportProgress = { current: 0, total: 0, eventName: '' };
        this.$emit('export-end');
      }
    },

    async exportSingleEventToPDF(event) {
      try {
        // ดึงข้อมูล participants ของ event นี้
        const participants = await this.fetchEventParticipants(event.id || event.evn_id);
        
        // ตรวจสอบว่า participants เป็น array
        if (!Array.isArray(participants)) {
          console.error('Participants is not an array:', participants);
          throw new Error('ไม่สามารถดึงข้อมูลผู้เข้าร่วมได้');
        }
        
        // สร้าง iframe แทนการใช้ window.open เพื่อหลีกเลี่ยง popup blocker
        const iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
        
        const printWindow = iframe.contentWindow;
        if (!printWindow) {
          document.body.removeChild(iframe);
          throw new Error('ไม่สามารถสร้างหน้าต่าง Print ได้');
        }

        const now = new Date();
        const dateStr = now.toLocaleDateString('th-TH', { 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });

        // คำนวณระยะเวลากิจกรรม
        const duration = this.calculateDuration(event.evn_timestart, event.evn_timeend);

        let htmlContent = `
          <!DOCTYPE html>
          <html>
          <head>
            <meta charset="UTF-8">
            <title>${event.evn_title || 'Event'} - Details Report</title>
            <style>
              @page { 
                size: A4; 
                margin: 15mm; 
              }
              body {
                font-family: 'Sarabun', 'TH Sarabun New', 'Cordia New', 'Angsana New', Arial, sans-serif;
                margin: 0;
                padding: 20px;
                font-size: 11pt;
                color: #374151;
              }
              
              /* Header Section */
              .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 3px solid #b91c1c;
                padding-bottom: 15px;
              }
              .header h1 {
                margin: 0 0 10px 0;
                color: #b91c1c;
                font-size: 28pt;
                font-weight: bold;
              }
              .header .subtitle {
                margin: 5px 0;
                color: #6b7280;
                font-size: 11pt;
              }
              
              /* Event Details Section */
              .section {
                margin-bottom: 30px;
                page-break-inside: avoid;
              }
              .section-title {
                font-size: 18pt;
                font-weight: bold;
                color: #b91c1c;
                margin-bottom: 15px;
                padding-bottom: 8px;
                border-bottom: 2px solid #fecaca;
              }
              
              .details-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px 30px;
                margin-bottom: 15px;
              }
              .details-grid.full {
                grid-template-columns: 1fr;
              }
              
              .detail-item {
                padding: 10px;
                background: #f9fafb;
                border-radius: 8px;
                border-left: 3px solid #b91c1c;
              }
              .detail-label {
                font-weight: 600;
                color: #4b5563;
                font-size: 10pt;
                margin-bottom: 5px;
              }
              .detail-value {
                color: #1f2937;
                font-size: 11pt;
                word-wrap: break-word;
              }
              
              /* Guest List Table */
              table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
                page-break-inside: auto;
              }
              thead {
                background-color: #b91c1c;
                color: white;
              }
              th {
                padding: 12px 8px;
                text-align: center;
                font-weight: 600;
                border: 1px solid #991b1b;
                font-size: 10pt;
              }
              td {
                padding: 10px 8px;
                text-align: center;
                border: 1px solid #e5e7eb;
                font-size: 10pt;
              }
              td.left {
                text-align: left;
              }
              tr:nth-child(even) {
                background-color: #f9fafb;
              }
              tr:nth-child(odd) {
                background-color: #ffffff;
              }
              
              /* Status badges */
              .status-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 9pt;
                font-weight: 600;
                text-transform: capitalize;
              }
              .status-accepted { 
                background: #dcfce7; 
                color: #166534; 
              }
              .status-rejected { 
                background: #fee2e2; 
                color: #991b1b; 
              }
              .status-pending { 
                background: #fef3c7; 
                color: #92400e; 
              }
              
              /* Summary Stats */
              .stats-row {
                display: flex;
                gap: 20px;
                margin: 20px 0;
                justify-content: center;
              }
              .stat-box {
                flex: 1;
                padding: 15px;
                background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
                border-radius: 12px;
                border: 2px solid #fecaca;
                text-align: center;
              }
              .stat-label {
                font-size: 10pt;
                color: #6b7280;
                margin-bottom: 5px;
              }
              .stat-value {
                font-size: 24pt;
                font-weight: bold;
                color: #b91c1c;
              }
              
              .no-data {
                text-align: center;
                color: #9ca3af;
                font-style: italic;
                padding: 30px;
              }
              
              @media print {
                body { 
                  padding: 0; 
                  margin: 0;
                }
                .no-print { 
                  display: none; 
                }
                @page {
                  margin: 10mm;
                }
              }
            </style>
          </head>
          <body>
            <!-- Header -->
            <div class="header">
              <h1>Event Details Report</h1>
              <div class="subtitle">${event.evn_title || 'ไม่ระบุชื่อกิจกรรม'}</div>
              <div class="subtitle">สร้างรายงานเมื่อ: ${dateStr}</div>
            </div>
            
            <!-- Event Details Section -->
            <div class="section">
              <div class="section-title">รายละเอียดกิจกรรม (Event Details)</div>
              
              <div class="details-grid">
                <div class="detail-item">
                  <div class="detail-label">ชื่อกิจกรรม (Event Title)</div>
                  <div class="detail-value">${event.evn_title || '-'}</div>
                </div>
                <div class="detail-item">
                  <div class="detail-label">ประเภท (Category)</div>
                  <div class="detail-value">${event.cat_name || '-'}</div>
                </div>
                <div class="detail-item">
                  <div class="detail-label">วันที่ (Date)</div>
                  <div class="detail-value">${this.formatDateThai(event.evn_date)}</div>
                </div>
                <div class="detail-item">
                  <div class="detail-label">เวลา (Time)</div>
                  <div class="detail-value">${event.evn_timestart || '-'} - ${event.evn_timeend || '-'}</div>
                </div>
                <div class="detail-item">
                  <div class="detail-label">ระยะเวลา (Duration)</div>
                  <div class="detail-value">${duration}</div>
                </div>
                <div class="detail-item">
                  <div class="detail-label">สถานที่ (Location)</div>
                  <div class="detail-value">${event.evn_location || '-'}</div>
                </div>
              </div>
              
              <div class="details-grid full">
                <div class="detail-item">
                  <div class="detail-label">รายละเอียด (Event Details)</div>
                  <div class="detail-value">${event.evn_details || '-'}</div>
                </div>
              </div>
              
              <!-- Statistics -->
              <div class="stats-row">
                <div class="stat-box">
                  <div class="stat-label">ผู้ได้รับเชิญทั้งหมด</div>
                  <div class="stat-value">${participants.length}</div>
                </div>
                <div class="stat-box">
                  <div class="stat-label">เข้าร่วมจริง (เช็คอิน)</div>
                  <div class="stat-value">${participants.filter(p => p.con_checkin_status === 1).length}</div>
                </div>
                <div class="stat-box">
                  <div class="stat-label">ปฏิเสธ</div>
                  <div class="stat-value">${participants.filter(p => p.status === 'denied').length}</div>
                </div>
                <div class="stat-box">
                  <div class="stat-label">รอตอบรับ</div>
                  <div class="stat-value">${participants.filter(p => p.con_checkin_status !== 1 && p.status !== 'denied').length}</div>
                </div>
              </div>
            </div>
            
            <!-- Guest List Section -->
            <div class="section">
              <div class="section-title">รายชื่อผู้เข้าร่วม (Guest List)</div>
              
              ${participants.length > 0 ? `
                <table>
                  <thead>
                    <tr>
                      <th style="width: 5%;">#</th>
                      <th style="width: 10%;">รหัส (ID)</th>
                      <th style="width: 20%;">ชื่อ-นามสกุล (Name)</th>
                      <th style="width: 10%;">ชื่อเล่น (Nickname)</th>
                      <th style="width: 12%;">เบอร์โทร (Phone)</th>
                      <th style="width: 15%;">แผนก (Department)</th>
                      <th style="width: 13%;">ทีม (Team)</th>
                      <th style="width: 15%;">สถานะ (Status)</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${participants.map((guest, index) => `
                      <tr>
                        <td>${index + 1}</td>
                        <td>${guest.emp_id || '-'}</td>
                        <td class="left">${guest.emp_prefix || ''} ${guest.emp_firstname || ''} ${guest.emp_lastname || ''}</td>
                        <td>${guest.emp_nickname || '-'}</td>
                        <td>${guest.emp_phone || '-'}</td>
                        <td class="left">${guest.department || '-'}</td>
                        <td class="left">${guest.team || '-'}</td>
                        <td>
                          <span class="status-badge status-${guest.con_checkin_status === 1 ? 'accepted' : (guest.status === 'denied' ? 'rejected' : 'pending')}">
                            ${guest.con_checkin_status === 1 ? 'เข้าร่วมจริง' : (guest.status === 'denied' ? 'ปฏิเสธ' : 'รอตอบรับ')}
                          </span>
                        </td>
                      </tr>
                    `).join('')}
                  </tbody>
                </table>
              ` : '<div class="no-data">ไม่มีรายชื่อผู้เข้าร่วม</div>'}
            </div>
          </body>
          </html>
        `;

        printWindow.document.write(htmlContent);
        printWindow.document.close();
        
        // รอให้โหลดเสร็จก่อน print แล้วลบ iframe
        await new Promise((resolve) => {
          iframe.onload = function() {
            setTimeout(() => {
              printWindow.print();
              // ลบ iframe หลังจาก print dialog เปิด
              setTimeout(() => {
                if (iframe.parentNode) {
                  document.body.removeChild(iframe);
                }
                resolve();
              }, 1000);
            }, 100);
          };
        });

      } catch (error) {
        console.error('Export single PDF error:', error);
        throw error;
      }
    },

    async exportToExcel() {
      this.showMenu = false;
      
      if (this.selectedEvents.length === 0) {
        alert('กรุณาเลือกกิจกรรมก่อน Export');
        return;
      }

      if (this.isExporting) {
        alert('กำลัง Export อยู่ กรุณารอสักครู่...');
        return;
      }

      this.isExporting = true;
      this.$emit('export-start');

      try {
        // Export แยกไฟล์สำหรับแต่ละ event ที่เลือก
        for (let i = 0; i < this.selectedEvents.length; i++) {
          const event = this.selectedEvents[i];
          
          // อัปเดต progress
          this.exportProgress.current = i + 1;
          this.exportProgress.total = this.selectedEvents.length;
          this.exportProgress.eventName = event.evn_title || 'Event';
          
          this.$emit('export-progress', {
            current: this.exportProgress.current,
            total: this.exportProgress.total,
            eventName: this.exportProgress.eventName
          });
          
          await this.exportSingleEventToExcel(event);
          
          // หน่วงเวลาระหว่างการ export แต่ละไฟล์
          if (i < this.selectedEvents.length - 1) {
            await new Promise(resolve => setTimeout(resolve, 800));
          }
        }
        
        this.$emit('export-complete', {
          type: 'Excel',
          count: this.selectedEvents.length
        });
        
        console.log(`Excel Export completed: ${this.selectedEvents.length} event(s)`);
      } catch (error) {
        console.error('Export error:', error);
        this.$emit('export-error', error);
        alert('เกิดข้อผิดพลาดในการ Export ข้อมูล');
      } finally {
        this.isExporting = false;
        this.exportProgress = { current: 0, total: 0, eventName: '' };
        this.$emit('export-end');
      }
    },

    async exportSingleEventToExcel(event) {
      try {
        // ดึงข้อมูล participants ของ event นี้
        const participants = await this.fetchEventParticipants(event.id || event.evn_id);
        
        // ตรวจสอบว่า participants เป็น array
        if (!Array.isArray(participants)) {
          console.error('Participants is not an array:', participants);
          throw new Error('ไม่สามารถดึงข้อมูลผู้เข้าร่วมได้');
        }
        
        const duration = this.calculateDuration(event.evn_timestart, event.evn_timeend);
        
        // สร้าง workbook ใหม่ด้วย ExcelJS
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Event Details');
        
        // ตั้งค่าความกว้างคอลัมน์ทั้งหมด
        worksheet.columns = [
          { width: 8 },   // # (A)
          { width: 15 },  // รหัส (B)
          { width: 12 },  // คำนำหน้า (C)
          { width: 18 },  // ชื่อ (D)
          { width: 18 },  // นามสกุล (E)
          { width: 12 },  // ชื่อเล่น (F)
          { width: 15 },  // เบอร์โทร (G)
          { width: 25 },  // แผนก (H)
          { width: 20 },  // ทีม (I)
          { width: 25 },  // ตำแหน่ง (J)
          { width: 15 }   // สถานะ (K)
        ];
        
        let currentRow = 1;
        
        // ส่วนที่ 1: รายละเอียดกิจกรรม
        worksheet.mergeCells(`A${currentRow}:K${currentRow}`);
        const titleCell = worksheet.getCell(`A${currentRow}`);
        titleCell.value = 'รายละเอียดกิจกรรม (Event Details)';
        titleCell.font = { bold: true, size: 14 };
        titleCell.alignment = { vertical: 'middle', horizontal: 'left' };
        currentRow += 2;
        
        // รายละเอียดกิจกรรม
        const eventDetails = [
          ['ชื่อกิจกรรม (Event Title)', event.evn_title || '-'],
          ['ประเภท (Category)', event.cat_name || '-'],
          ['วันที่ (Date)', this.formatDateThai(event.evn_date)],
          ['เวลา (Time)', `${event.evn_timestart || '-'} - ${event.evn_timeend || '-'}`],
          ['ระยะเวลา (Duration)', duration],
          ['สถานที่ (Location)', event.evn_location || '-'],
          ['รายละเอียด (Details)', event.evn_details || '-']
        ];
        
        eventDetails.forEach(([label, value]) => {
          const row = worksheet.getRow(currentRow);
          row.getCell(1).value = label;
          row.getCell(1).font = { bold: true };
          row.getCell(2).value = value;
          worksheet.mergeCells(`B${currentRow}:K${currentRow}`);
          currentRow++;
        });
        
        currentRow += 1;
        
        // ส่วนที่ 2: สถิติ
        worksheet.mergeCells(`A${currentRow}:K${currentRow}`);
        const statsCell = worksheet.getCell(`A${currentRow}`);
        statsCell.value = 'สถิติ (Statistics)';
        statsCell.font = { bold: true, size: 14 };
        currentRow += 1;
        
        const stats = [
          ['ผู้ได้รับเชิญทั้งหมด', participants.length],
          ['ยืนยันเข้าร่วม', participants.filter(p => p.con_checkin_status === 1).length],
          ['ปฏิเสธ', participants.filter(p => p.status === 'denied').length],
          ['รอตอบรับ', participants.filter(p => p.con_checkin_status !== 1 && p.status !== 'denied').length]
        ];
        
        stats.forEach(([label, value]) => {
          const row = worksheet.getRow(currentRow);
          row.getCell(1).value = label;
          row.getCell(1).font = { bold: true };
          row.getCell(2).value = value;
          currentRow++;
        });
        
        currentRow += 2;
        
        // ส่วนที่ 3: รายชื่อผู้เข้าร่วม
        worksheet.mergeCells(`A${currentRow}:K${currentRow}`);
        const guestListCell = worksheet.getCell(`A${currentRow}`);
        guestListCell.value = 'รายชื่อผู้เข้าร่วม (Guest List)';
        guestListCell.font = { bold: true, size: 14 };
        currentRow += 1;
        
        // Header row
        const headers = [
          '#',
          'รหัส (ID)',
          'คำนำหน้า (Prefix)',
          'ชื่อ (First Name)',
          'นามสกุล (Last Name)',
          'ชื่อเล่น (Nickname)',
          'เบอร์โทร (Phone)',
          'แผนก (Department)',
          'ทีม (Team)',
          'ตำแหน่ง (Position)',
          'สถานะ (Status)'
        ];
        
        const headerRow = worksheet.getRow(currentRow);
        headers.forEach((header, index) => {
          const cell = headerRow.getCell(index + 1);
          cell.value = header;
          cell.font = { bold: true };
          cell.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FFE0E0E0' }
          };
          cell.alignment = { vertical: 'middle', horizontal: 'center' };
          cell.border = {
            top: { style: 'thin' },
            left: { style: 'thin' },
            bottom: { style: 'thin' },
            right: { style: 'thin' }
          };
        });
        currentRow++;
        
        // Guest rows
        participants.forEach((guest, index) => {
          const statusLabel = guest.con_checkin_status === 1 ? 'เข้าร่วมจริง' : (guest.status === 'denied' ? 'ปฏิเสธ' : 'รอตอบรับ');
          const row = worksheet.getRow(currentRow);
          
          const rowData = [
            index + 1,
            guest.emp_id || '-',
            guest.emp_prefix || '-',
            guest.emp_firstname || '-',
            guest.emp_lastname || '-',
            guest.emp_nickname || '-',
            guest.emp_phone || '-',
            guest.department || '-',
            guest.team || '-',
            guest.position || '-',
            statusLabel
          ];
          
          rowData.forEach((value, colIndex) => {
            const cell = row.getCell(colIndex + 1);
            cell.value = value;
            cell.border = {
              top: { style: 'thin' },
              left: { style: 'thin' },
              bottom: { style: 'thin' },
              right: { style: 'thin' }
            };
            cell.alignment = { vertical: 'middle', horizontal: 'left' };
          });
          
          currentRow++;
        });
        
        // สร้างและดาวน์โหลดไฟล์
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        const now = new Date();
        const dateStr = now.toISOString().split('T')[0];
        const timeStr = now.toTimeString().split(' ')[0].replace(/:/g, '-');
        const eventTitle = (event.evn_title || 'event').replace(/[^a-zA-Z0-9ก-๙]/g, '_').substring(0, 50);
        
        link.setAttribute('href', url);
        link.setAttribute('download', `${eventTitle}_${dateStr}_${timeStr}.xlsx`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
      } catch (error) {
        console.error('Export single Excel error:', error);
        throw error;
      }
    },

    async fetchEventParticipants(eventId) {
      try {
        const response = await axios.get(`/events/${eventId}/connects`);
        
        // จัดการ response ที่อาจมีโครงสร้างต่างกัน
        let participants = [];
        
        if (response.data && Array.isArray(response.data.participants)) {
          // ใช้ข้อมูล participants จาก API ใหม่
          participants = response.data.participants;
        } else if (Array.isArray(response.data)) {
          participants = response.data;
        } else if (response.data && Array.isArray(response.data.data)) {
          participants = response.data.data;
        } else if (response.data && typeof response.data === 'object') {
          // กรณีที่ response เป็น object แต่ไม่ใช่ array
          participants = [response.data];
        }
        
        return participants;
      } catch (error) {
        console.error('Error fetching participants:', error);
        return [];
      }
    },

    formatDateThai(dateString) {
      if (!dateString) return '-';
      
      try {
        const date = new Date(dateString);
        const thaiMonths = [
          'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
          'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
        ];
        
        const day = date.getDate();
        const month = thaiMonths[date.getMonth()];
        const year = date.getFullYear() + 543;
        
        return `${day} ${month} ${year}`;
      } catch (error) {
        return dateString;
      }
    },

    calculateDuration(startTime, endTime) {
      if (!startTime || !endTime) return '-';
      
      try {
        const [startHour, startMin] = startTime.split(':').map(Number);
        const [endHour, endMin] = endTime.split(':').map(Number);
        
        let totalMinutes = (endHour * 60 + endMin) - (startHour * 60 + startMin);
        
        if (totalMinutes < 0) {
          totalMinutes += 24 * 60; // กรณีข้ามวัน
        }
        
        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;
        
        if (hours > 0 && minutes > 0) {
          return `${hours} ชั่วโมง ${minutes} นาที`;
        } else if (hours > 0) {
          return `${hours} ชั่วโมง`;
        } else {
          return `${minutes} นาที`;
        }
      } catch (error) {
        return '-';
      }
    },

    getStatusLabel(status) {
      const statusMap = {
        'accepted': 'ยืนยันเข้าร่วม',
        'rejected': 'ปฏิเสธ',
        'pending': 'รอตอบรับ'
      };
      return statusMap[status] || 'ไม่ระบุ';
    }
  }
};
</script>

<style scoped>
.rotate-180 {
  transform: rotate(180deg);
  transition: transform 0.2s ease;
}

@keyframes dropdown-fade-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.relative > div[class*="absolute"] {
  animation: dropdown-fade-in 0.2s ease-out;
}
</style>
