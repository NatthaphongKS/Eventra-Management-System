<template>
  <div class="relative" ref="dropdownContainer">
    <button 
      @click="showMenu = !showMenu"
      class="inline-flex h-11 items-center gap-2 rounded-lg bg-white border border-gray-300 px-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors"
      :disabled="disabled"
      :class="{'opacity-50 cursor-not-allowed': disabled}"
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
export default {
  name: 'ExportDropdown',
  props: {
    data: {
      type: Array,
      required: true,
      default: () => []
    },
    disabled: {
      type: Boolean,
      default: false
    },
    formatDate: {
      type: Function,
      default: (date) => date || ''
    },
    timeText: {
      type: Function,
      default: (start, end) => `${start || ''} - ${end || ''}`
    }
  },
  data() {
    return {
      showMenu: false
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

    exportToPDF() {
      this.showMenu = false;
      
      if (this.data.length === 0) {
        alert('ไม่มีข้อมูลที่จะ Export');
        return;
      }

      try {
        const printWindow = window.open('', '_blank');
        if (!printWindow) {
          alert('กรุณาอนุญาตให้เปิด popup window');
          return;
        }

        const now = new Date();
        const dateStr = now.toLocaleDateString('th-TH', { 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });

        let htmlContent = `
          <!DOCTYPE html>
          <html>
          <head>
            <meta charset="UTF-8">
            <title>Events Dashboard Report</title>
            <style>
              @page { 
                size: A4 landscape; 
                margin: 15mm; 
              }
              body {
                font-family: 'Sarabun', 'TH Sarabun New', 'Cordia New', 'Angsana New', Arial, sans-serif;
                margin: 0;
                padding: 20px;
                font-size: 11pt;
              }
              .header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 3px solid #b91c1c;
                padding-bottom: 10px;
              }
              .header h1 {
                margin: 0;
                color: #b91c1c;
                font-size: 24pt;
                font-weight: bold;
              }
              .header .date {
                margin-top: 5px;
                color: #666;
                font-size: 10pt;
              }
              table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
              }
              th {
                background-color: #b91c1c;
                color: white;
                padding: 10px 8px;
                text-align: center;
                font-weight: 600;
                border: 1px solid #991b1b;
                font-size: 11pt;
              }
              td {
                padding: 8px;
                text-align: center;
                border: 1px solid #ddd;
                font-size: 10pt;
              }
              td.left {
                text-align: left;
                padding-left: 12px;
              }
              tr:nth-child(even) {
                background-color: #f9fafb;
              }
              tr:nth-child(odd) {
                background-color: #ffffff;
              }
              .status-done { 
                color: #059669; 
                font-weight: 600; 
                text-transform: capitalize;
              }
              .status-ongoing { 
                color: #dc2626; 
                font-weight: 600; 
                text-transform: capitalize;
              }
              .status-upcoming { 
                color: #f59e0b; 
                font-weight: 600; 
                text-transform: capitalize;
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
            <div class="header">
              <h1>Events Dashboard Report</h1>
              <div class="date">สร้างเมื่อ: ${dateStr}</div>
            </div>
            <table>
              <thead>
                <tr>
                  <th style="width: 5%;">#</th>
                  <th style="width: 28%;">Event</th>
                  <th style="width: 15%;">Category</th>
                  <th style="width: 12%;">Date</th>
                  <th style="width: 13%;">Time</th>
                  <th style="width: 8%;">Invited</th>
                  <th style="width: 8%;">Accepted</th>
                  <th style="width: 11%;">Status</th>
                </tr>
              </thead>
              <tbody>
        `;

        this.data.forEach((event, index) => {
          const statusClass = event.evn_status ? `status-${event.evn_status.toLowerCase()}` : '';
          htmlContent += `
            <tr>
              <td>${index + 1}</td>
              <td class="left">${event.evn_title || ''}</td>
              <td>${event.cat_name || ''}</td>
              <td>${this.formatDate(event.evn_date)}</td>
              <td>${this.timeText(event.evn_timestart, event.evn_timeend)}</td>
              <td>${event.evn_num_guest}</td>
              <td>${event.evn_sum_accept}</td>
              <td class="${statusClass}">${event.evn_status || 'N/A'}</td>
            </tr>
          `;
        });

        htmlContent += '</tbody></table>';
        htmlContent += '<' + 'script>';
        htmlContent += 'window.onload = function() { window.print(); };';
        htmlContent += '</' + 'script>';
        htmlContent += '</body></html>';

        printWindow.document.write(htmlContent);
        printWindow.document.close();

        console.log('PDF Export initiated:', this.data.length, 'events');
      } catch (error) {
        console.error('Export error:', error);
        alert('เกิดข้อผิดพลาดในการ Export ข้อมูล: ' + error.message);
      }
    },

    exportToExcel() {
      this.showMenu = false;
      
      if (this.data.length === 0) {
        alert('ไม่มีข้อมูลที่จะ Export');
        return;
      }

      try {
        const headers = ['#', 'Event', 'Category', 'Date', 'Time', 'Invited', 'Accepted', 'Status'];
        
        const rows = this.data.map((event, index) => [
          index + 1,
          `"${(event.evn_title || '').replace(/"/g, '""')}"`,
          `"${(event.cat_name || '').replace(/"/g, '""')}"`,
          this.formatDate(event.evn_date),
          this.timeText(event.evn_timestart, event.evn_timeend),
          event.evn_num_guest,
          event.evn_sum_accept,
          event.evn_status || 'N/A'
        ]);
        
        const csvContent = [
          headers.join(','),
          ...rows.map(row => row.join(','))
        ].join('\n');
        
        const BOM = '\uFEFF';
        const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        const now = new Date();
        const dateStr = now.toISOString().split('T')[0];
        const timeStr = now.toTimeString().split(' ')[0].replace(/:/g, '-');
        
        link.setAttribute('href', url);
        link.setAttribute('download', `events_dashboard_${dateStr}_${timeStr}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        console.log('Excel Export completed:', this.data.length, 'events');
      } catch (error) {
        console.error('Export error:', error);
        alert('เกิดข้อผิดพลาดในการ Export ข้อมูล');
      }
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
