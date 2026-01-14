<template>
  <div class="relative" ref="dropdownContainer">
    <button 
      @click="showMenu = !showMenu"
      class="flex h-[58px] w-[170px] items-center gap-5 rounded-[20px] text-[20px] bg-white px-4 font-medium text-neutral-700 hover:bg-neutral-50 shadow-[0_4px_4px_rgba(0,0,0,0.25)]"
      :disabled="disabled"
      :class="{'opacity-50 cursor-not-allowed': disabled}"
      :title="disabled ? 'กรุณาเลือกกิจกรรมก่อน Export' : 'Export ข้อมูลกิจกรรม'"
    >
      <Icon :icon="'ci:share-ios-export'" class="h-[30px] w-[30px] text-neutral-700" />
      Export
    </button>
    
    <!-- Dropdown Menu -->
    <div 
      v-if="showMenu"
      class="absolute right-0 mt-2 w-48 bg-white rounded-[20px] shadow-lg border border-neutral-200 z-50"
    >
      <button
        @click="exportToPDF"
        class="w-full px-4 py-3 text-left text-gray-700 hover:bg-red-100 flex items-center gap-3 rounded-[20px] transition-colors"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        <span class="font-medium">Export as PDF</span>
      </button>
      <button
        @click="exportToExcel"
        class="w-full px-4 py-3 text-left text-gray-700 hover:bg-red-100 flex items-center gap-3 rounded-[20px] transition-colors"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="font-medium">Export as .xlsx</span>
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import ExcelJS from 'exceljs';
import { Icon } from '@iconify/vue';

axios.defaults.baseURL = "/api";
axios.defaults.headers.common["Accept"] = "application/json";

export default {
  components: {
    Icon
  },
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
        // ดึงข้อมูลพนักงานทั้งหมดในบริษัท
        const allEmployees = await this.fetchAllEmployees();
        
        // ดึงข้อมูล participants ของ event นี้
        const eventParticipants = await this.fetchEventParticipants(event.id || event.evn_id);
        
        // รวมข้อมูลพนักงานทั้งหมดกับข้อมูลการเข้าร่วมกิจกรรม (LEFT JOIN)
        const participants = this.mergeEmployeesWithEventData(allEmployees, eventParticipants);
        
        // ตรวจสอบว่า participants เป็น array
        if (!Array.isArray(participants)) {
          console.error('Participants is not an array:', participants);
          throw new Error('ไม่สามารถดึงข้อมูลพนักงานได้');
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
                margin: 12mm 10mm; 
              }
              body {
                font-family: 'Sarabun', 'TH Sarabun New', 'Cordia New', 'Angsana New', Arial, sans-serif;
                margin: 0;
                padding: 10px;
                font-size: 10pt;
                color: #374151;
              }
              
              /* Header Section */
              .header {
                text-align: center;
                margin-bottom: 8px;
                border-bottom: 2px solid #b91c1c;
                padding-bottom: 8px;
              }
              .header h1 {
                margin: 0 0 4px 0;
                color: #b91c1c;
                font-size: 20pt;
                font-weight: bold;
              }
              .header .subtitle {
                margin: 2px 0;
                color: #6b7280;
                font-size: 9pt;
              }
              
              /* Event Details Section */
              .section {
                margin-bottom: 12px;
              }
              .section-title {
                font-size: 14pt;
                font-weight: bold;
                color: #b91c1c;
                margin-bottom: 10px;
                padding-bottom: 5px;
                border-bottom: 2px solid #fecaca;
                page-break-after: avoid;
                break-after: avoid;
              }
              
              .details-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px 15px;
                margin-bottom: 10px;
              }
              .details-grid.full {
                grid-template-columns: 1fr;
              }
              
              .detail-item {
                padding: 8px;
                background: #f9fafb;
                border-radius: 6px;
                border-left: 3px solid #b91c1c;
              }
              .detail-label {
                font-weight: 600;
                color: #4b5563;
                font-size: 9pt;
                margin-bottom: 3px;
              }
              .detail-value {
                color: #1f2937;
                font-size: 10pt;
                word-wrap: break-word;
              }
              
              /* Guest List Table */
              table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
              }
              thead {
                background-color: #b91c1c;
                color: white;
                display: table-header-group;
              }
              th {
                padding: 8px 6px;
                text-align: center;
                font-weight: 600;
                border: 1px solid #991b1b;
                font-size: 9pt;
              }
              tbody {
                display: table-row-group;
              }
              tr {
                page-break-inside: avoid;
                break-inside: avoid;
              }
              td {
                padding: 6px 5px;
                text-align: center;
                border: 1px solid #e5e7eb;
                font-size: 9pt;
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
              
              /* Page break styles - removed fixed pagination */
              
              /* Status badges */
              .status-badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 10px;
                font-size: 8pt;
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
                gap: 10px;
                margin: 12px 0;
                justify-content: center;
                flex-wrap: wrap;
              }
              .stat-box {
                flex: 1;
                min-width: 80px;
                padding: 8px;
                background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
                border-radius: 8px;
                border: 2px solid #fecaca;
                text-align: center;
              }
              .stat-label {
                font-size: 8pt;
                color: #6b7280;
                margin-bottom: 3px;
              }
              .stat-value {
                font-size: 16pt;
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
                  <div class="detail-value">${this.formatTime(event.evn_timestart)} - ${this.formatTime(event.evn_timeend)}</div>
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
                  <div class="stat-label">พนักงานทั้งหมด</div>
                  <div class="stat-value">${participants.length}</div>
                </div>
                <div class="stat-box">
                  <div class="stat-label">ผู้ได้รับเชิญ</div>
                  <div class="stat-value">${participants.filter(p => p.__isInvited).length}</div>
                </div>
                <div class="stat-box">
                  <div class="stat-label">ตอบรับเข้าร่วม</div>
                  <div class="stat-value">${participants.filter(p => p.status === 'accepted').length}</div>
                </div>
                <div class="stat-box">
                  <div class="stat-label">เข้าร่วมจริง (เช็คอิน)</div>
                  <div class="stat-value">${participants.filter(p => p.__isCheckedIn).length}</div>
                </div>
                <div class="stat-box">
                  <div class="stat-label">ปฏิเสธ</div>
                  <div class="stat-value">${participants.filter(p => p.status === 'denied').length}</div>
                </div>
              </div>
            </div>
            
            <!-- Guest List Section -->
            ${participants.length > 0 ? `
              <div class="section">
                <div class="section-title">รายชื่อผู้เข้าร่วม (Guest List)</div>
                
                <table>
                  <thead>
                    <tr>
                      <th style="width: 5%;">#</th>
                      <th style="width: 10%;">รหัส (ID)</th>
                      <th style="width: 18%;">ชื่อ-นามสกุล (Name)</th>
                      <th style="width: 10%;">ชื่อเล่น (Nickname)</th>
                      <th style="width: 12%;">เบอร์โทร (Phone)</th>
                      <th style="width: 12%;">แผนก (Department)</th>
                      <th style="width: 11%;">ทีม (Team)</th>
                      <th style="width: 10%;">สถานะ (Status)</th>
                      <th style="width: 10%;">เข้าร่วมจริง</th>
                      <th style="width: 9%;">เหตุผล (Reason)</th>
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
                          <span class="status-badge status-${
                            guest.status === 'accepted' ? 'accepted' : 
                            (guest.status === 'denied' ? 'rejected' : 
                            (guest.status === 'not_invited' ? 'pending' : 'pending'))
                          }">
                            ${
                              guest.status === 'accepted' ? 'เข้าร่วม' : 
                              (guest.status === 'denied' ? 'ปฏิเสธ' : 
                              (guest.status === 'not_invited' ? 'ไม่ได้รับเชิญ' : 'รอตอบรับ'))
                            }
                          </span>
                        </td>
                        <td>${guest.__isCheckedIn ? 'เข้าร่วมจริง' : 'ไม่เข้าร่วม'}</td>
                        <td class="left">${guest.con_reason || '-'}</td>
                      </tr>
                    `).join('')}
                  </tbody>
                </table>
              </div>
            ` : `
              <div class="section">
                <div class="section-title">รายชื่อผู้เข้าร่วม (Guest List)</div>
                <div class="no-data">ไม่มีรายชื่อผู้เข้าร่วม</div>
              </div>
            `}
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
        // ดึงข้อมูลพนักงานทั้งหมดในบริษัท
        const allEmployees = await this.fetchAllEmployees();
        
        // ดึงข้อมูล participants ของ event นี้
        const eventParticipants = await this.fetchEventParticipants(event.id || event.evn_id);
        
        // รวมข้อมูลพนักงานทั้งหมดกับข้อมูลการเข้าร่วมกิจกรรม (LEFT JOIN)
        const participants = this.mergeEmployeesWithEventData(allEmployees, eventParticipants);
        
        // ตรวจสอบว่า participants เป็น array
        if (!Array.isArray(participants)) {
          console.error('Participants is not an array:', participants);
          throw new Error('ไม่สามารถดึงข้อมูลพนักงานได้');
        }
        
        const duration = this.calculateDuration(event.evn_timestart, event.evn_timeend);
        
        // สร้าง workbook ใหม่ด้วย ExcelJS
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Event Details');
        
        // ตั้งค่าความกว้างคอลัมน์ทั้งหมด
        worksheet.columns = [
          { width: 35 },  // # (A)
          { width: 15 },  // รหัส (B)
          { width: 12 },  // คำนำหน้า (C)
          { width: 18 },  // ชื่อ (D)
          { width: 18 },  // นามสกุล (E)
          { width: 12 },  // ชื่อเล่น (F)
          { width: 15 },  // เบอร์โทร (G)
          { width: 25 },  // แผนก (H)
          { width: 20 },  // ทีม (I)
          { width: 25 },  // ตำแหน่ง (J)
          { width: 15 },  // สถานะ (K)
          { width: 35 },  // เข้าร่วมจริง (L)
          { width: 30 }   // ความคิดเห็น (M)
        ];
        
        let currentRow = 1;
        
        // ส่วนที่ 1: รายละเอียดกิจกรรม
        worksheet.mergeCells(`A${currentRow}:M${currentRow}`);
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
          ['เวลา (Time)', `${this.formatTime(event.evn_timestart)} - ${this.formatTime(event.evn_timeend)}`],
          ['ระยะเวลา (Duration)', duration],
          ['สถานที่ (Location)', event.evn_location || '-'],
          ['รายละเอียด (Details)', event.evn_details || '-']
        ];
        
        eventDetails.forEach(([label, value]) => {
          const row = worksheet.getRow(currentRow);
          row.getCell(1).value = label;
          row.getCell(1).font = { bold: true };
          row.getCell(2).value = value;
          worksheet.mergeCells(`B${currentRow}:M${currentRow}`);
          currentRow++;
        });
        
        currentRow += 1;
        
        // ส่วนที่ 2: สถิติ
        worksheet.mergeCells(`A${currentRow}:M${currentRow}`);
        const statsCell = worksheet.getCell(`A${currentRow}`);
        statsCell.value = 'สถิติ (Statistics)';
        statsCell.font = { bold: true, size: 14 };
        currentRow += 1;
        
        const stats = [
          ['พนักงานทั้งหมด', participants.length],
          ['ผู้ได้รับเชิญ', participants.filter(p => p.__isInvited).length],
          ['ตอบรับเข้าร่วม', participants.filter(p => p.status === 'accepted').length],
          ['เข้าร่วมจริง (เช็คอิน)', participants.filter(p => p.__isCheckedIn).length],
          ['ปฏิเสธ', participants.filter(p => p.status === 'denied').length]
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
        worksheet.mergeCells(`A${currentRow}:M${currentRow}`);
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
          'สถานะ (Status)',
          'เข้าร่วมจริง',
          'ความคิดเห็น/เหตุผล (Reason)'
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
          const statusLabel = 
            guest.status === 'accepted' ? 'เข้าร่วม' : 
            (guest.status === 'denied' ? 'ปฏิเสธ' : 
            (guest.status === 'not_invited' ? 'ไม่ได้รับเชิญ' : 'รอตอบรับ'));
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
            statusLabel,
            guest.__isCheckedIn ? 'เข้าร่วมจริง' : 'ไม่เข้าร่วม',
            guest.con_reason || '-'
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

    async fetchAllEmployees() {
      try {
        console.log('📥 Fetching all employees in company');
        const response = await axios.get('/employees');
        
        let employees = [];
        
        if (Array.isArray(response.data)) {
          employees = response.data;
        } else if (response.data && Array.isArray(response.data.data)) {
          employees = response.data.data;
        } else if (response.data && Array.isArray(response.data.employees)) {
          employees = response.data.employees;
        }
        
        // Normalize department, team, position data จาก employee table
        const normalized = employees.map(emp => {
          // จัดการ department (รองรับทั้ง object และ string)
          let department = '';
          if (emp.department && typeof emp.department === 'object') {
            department = emp.department.dept_name || emp.department.name || emp.department.department_name || '';
          } else if (emp.department_name) {
            department = emp.department_name;
          } else if (emp.dept_name) {
            department = emp.dept_name;
          } else if (typeof emp.department === 'string') {
            department = emp.department;
          }
          
          // จัดการ team (รองรับทั้ง object และ string)
          let team = '';
          if (emp.team && typeof emp.team === 'object') {
            team = emp.team.team_name || emp.team.name || '';
          } else if (emp.team_name) {
            team = emp.team_name;
          } else if (typeof emp.team === 'string') {
            team = emp.team;
          }
          
          // จัดการ position (รองรับทั้ง object และ string)
          let position = '';
          if (emp.position && typeof emp.position === 'object') {
            position = emp.position.pos_name || emp.position.name || emp.position.position_name || '';
          } else if (emp.position_name) {
            position = emp.position_name;
          } else if (emp.pos_name) {
            position = emp.pos_name;
          } else if (typeof emp.position === 'string') {
            position = emp.position;
          }
          
          return {
            ...emp,
            department,
            team,
            position
          };
        });
        
        console.log(`Loaded ${normalized.length} employees from company`);
        console.log(`Sample employee data:`, normalized[0]); // Debug: แสดงตัวอย่างข้อมูล
        return normalized;
      } catch (error) {
        console.error('Error fetching all employees:', error);
        return [];
      }
    },

    mergeEmployeesWithEventData(allEmployees, eventParticipants) {
      // สร้าง Map ของผู้เข้าร่วมกิจกรรมเพื่อให้ค้นหาได้เร็ว (LEFT JOIN logic)
      const participantMap = new Map();
      eventParticipants.forEach(participant => {
        const empId = participant.emp_id || participant.id;
        if (empId) {
          participantMap.set(empId, participant);
        }
      });
      
      // รวมข้อมูลพนักงานทั้งหมดกับข้อมูลการเข้าร่วม
      return allEmployees.map(employee => {
        const empId = employee.emp_id || employee.id;
        const participantData = participantMap.get(empId);
        
        // CRITICAL: เก็บข้อมูล department, team, position จาก employee เสมอ
        // ไม่ให้ถูก overwrite โดยข้อมูลจาก participant/con_answer
        const employeeInfo = {
          department: employee.department || '',
          team: employee.team || '',
          position: employee.position || ''
        };
        
        if (participantData) {
          // พนักงานคนนี้ได้รับเชิญและมีข้อมูลการตอบรับ
          return {
            ...employee,
            ...employeeInfo, // ใช้ข้อมูล dept/team/position จาก employee
            status: participantData.status || 'pending',
            con_answer: participantData.con_answer || participantData.status,
            con_reason: participantData.con_reason || '',
            con_checkin_status: participantData.con_checkin_status || 0,
            __isCheckedIn: participantData.__isCheckedIn || false,
            __isInvited: true
          };
        } else {
          // พนักงานคนนี้ไม่ได้รับเชิญในกิจกรรมนี้
          return {
            ...employee,
            ...employeeInfo, // ใช้ข้อมูล dept/team/position จาก employee
            status: 'not_invited',
            con_answer: 'not_invited',
            con_reason: '',
            con_checkin_status: 0,
            __isCheckedIn: false,
            __isInvited: false
          };
        }
      });
    },

    async fetchEventParticipants(eventId) {
      try {
        console.log(`Fetching participants for event ${eventId}`);
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
        
        // Normalize participant data to handle backend variations
        const normalized = participants.map(p => {
          // Normalize status field (backend may use 'status' or 'con_answer')
          const rawStatus = p.status ?? p.con_answer ?? 'invalid';
          const normalizedStatus = String(rawStatus).toLowerCase();
          
          // Normalize check-in status (can be number 1/0 or string "checked_in"/"not_checked_in")
          const isCheckedIn = Number(p.con_checkin_status) === 1 || 
                             String(p.con_checkin_status).toLowerCase() === 'checked_in';
          
          return {
            ...p,
            status: normalizedStatus,
            __isCheckedIn: isCheckedIn
          };
        });
        
        const acceptedCount = normalized.filter(p => p.status === 'accepted').length;
        const deniedCount = normalized.filter(p => p.status === 'denied').length;
        const pendingCount = normalized.filter(p => p.status !== 'accepted' && p.status !== 'denied').length;
        
        console.log(`Loaded ${normalized.length} participants: ${acceptedCount} accepted, ${deniedCount} denied, ${pendingCount} pending`);
        
        return normalized;
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

    // UNUSED - ไม่ได้ใช้งาน (ใช้ inline mapping แทน)
    // getStatusLabel(status) {
    //   const statusMap = {
    //     'accepted': 'ยืนยันเข้าร่วม',
    //     'denied': 'ปฏิเสธ',
    //     'pending': 'รอตอบรับ'
    //   };
    //   return statusMap[status] || 'ไม่ระบุ';
    // },

    formatTime(timeString) {
      if (!timeString) return '-';
      // ตัดวินาทีออก แสดงแค่ชั่วโมง:นาที (HH:MM)
      return timeString.substring(0, 5);
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
