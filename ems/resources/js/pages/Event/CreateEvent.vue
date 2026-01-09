<template>
    <div class="font-[Poppins] pb-20" @pointerdown.capture="onRootPointer">
        <div class="mb-8 flex items-center gap-3">
            <h2 class="text-2xl font-semibold text-neutral-800">
                Create Event
            </h2>
        </div>
        <div class="grid grid-cols-12 gap-8 border-b border-neutral-100 pb-10 mb-10">
            <div class="col-span-12 lg:col-span-8">
                <h3 class="text-xl font-semibold text-neutral-800 mb-6">
                    Event Details
                </h3>
                <!-- Event Title -->
                <div class="grid md:grid-cols-[1fr_240px] gap-6 items-start">
                    <div>
                        <label class="block text-neutral-800 font-semibold text-[15px] mb-2">
                            Event Title <span class="text-red-600">*</span>
                        </label>
                        <InputPill v-model="eventTitle" placeholder="Enter event title" :class="[
                            'w-full h-[52px] font-medium text-[16px] text-neutral-800 border rounded-[20px] px-5 focus:outline-none transition',
                            'placeholder:text-red-300',
                            errors.eventTitle ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300',
                        ]" @input="errors.eventTitle = false" />
                        <p v-if="errors.eventTitle" class="text-red-500 text-xs mt-1 ml-1 font-medium">Required field
                        </p>
                    </div>
                    <div>
                        <!-- Event Category -->
                        <label class="block text-neutral-800 font-semibold text-[15px] mb-2">
                            Category <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <select v-model="eventCategoryId" :class="[
                                'appearance-none border rounded-[20px] px-[20px] w-full h-[52px] font-medium focus:outline-none transition bg-white cursor-pointer',
                                eventCategoryId ? 'text-neutral-800' : 'text-red-300',
                                errors.eventCategoryId ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300',
                            ]" @change="errors.eventCategoryId = false">
                                <!-- Event Category Options -->
                                <option value="" disabled selected>Select</option>
                                <option v-for="cat in selectCategory" :key="cat.id" :value="cat.id"
                                    class="text-neutral-800">
                                    {{ cat.cat_name }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <Icon icon="mdi:chevron-down" class="h-6 w-6 text-red-300" />
                            </div>
                        </div>
                        <p v-if="errors.eventCategoryId" class="text-red-500 text-xs mt-1 ml-1 font-medium">Required
                            field</p>
                    </div>
                </div>
                <!-- Event Description -->
                <div class="mt-6">
                    <label class="block text-neutral-800 font-semibold text-[15px] mb-2">
                        Event Description <span class="text-red-600">*</span>
                    </label>
                    <textarea v-model.trim="eventDescription" placeholder="Write some description... (255 words)"
                        :class="[
                            'w-full h-[160px] rounded-2xl p-5 font-medium text-neutral-800 focus:outline-none transition resize-none border',
                            'placeholder:text-red-300',
                            errors.eventDescription ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300',
                        ]" @input="errors.eventDescription = false"></textarea>
                    <!-- Required -->
                    <p v-if="errors.eventDescription" class="text-red-500 text-xs mt-1 ml-1 font-medium">Required field
                    </p>
                </div>
                <!-- Date, Time, Duration, Location -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div class="relative" ref="datePickerContainer">
                        <label class="block text-neutral-800 font-semibold text-[15px] mb-2">
                            Date <span class="text-red-600">*</span>
                        </label>
                        <div @click="toggleCalendar" :class="[
                            'flex items-center justify-between px-[20px] font-medium rounded-2xl border h-[52px] cursor-pointer transition bg-white select-none',
                            eventDate ? 'text-neutral-800' : 'text-red-300',
                            errors.eventDate
                                ? 'border-red-500 bg-red-50'
                                : 'border-neutral-200 hover:border-rose-400',
                        ]">
                            <span>{{
                                formattedDateDisplay || "dd/mm/yy"
                            }}</span>
                            <svg class="w-6 h-6 text-red-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M8.5 14a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m0 3.5a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m4.75-4.75a1.25 1.25 0 1 1-2.5 0a1.25 1.25 0 0 1 2.5 0M12 17.5a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m4.75-4.75a1.25 1.25 0 1 1-2.5 0a1.25 1.25 0 0 1 2.5 0M12 17.5a1.25 1.25 0 1 0 0-2.5a1.25 1.25 0 0 0 0 2.5m4.75-4.75a1.25 1.25 0 1 1-2.5 0a1.25 1.25 0 0 1 2.5 0" />
                                <path fill="currentColor" fill-rule="evenodd"
                                    d="M8 3.25a.75.75 0 0 1 .75.75v.75h6.5V4a.75.75 0 0 1 1.5 0v.758q.228.006.425.022c.38.03.736.098 1.073.27a2.75 2.75 0 0 1 1.202 1.202c.172.337.24.693.27 1.073c.03.365.03.81.03 1.345v7.66c0 .535 0 .98-.03 1.345c-.03.38-.098.736-.27 1.073a2.75 2.75 0 0 1-1.201 1.202c-.338.172-.694.24-1.074.27c-.365.03-.81.03-1.344.03H8.17c-.535 0-.98 0-1.345-.03c-.38-.03-.736-.098-1.073-.27a2.75 2.75 0 0 1-1.202-1.2c-.172-.338-.24-.694-.27-1.074c-.03-.365-.03-.81-.03-1.344V8.67c0-.535 0-.98.03-1.345c.03-.38.098-.736.27-1.073A2.75 2.75 0 0 1 5.752 5.05c.337-.172.693-.24 1.073-.27q.197-.016.425-.022V4A.75.75 0 0 1 8 3.25m10.25 7H5.75v6.05c0 .572 0 .957.025 1.252c.023.288.065.425.111.515c.12.236.311.427.547.547c.09.046.227.088.514.111c.296.024.68.025 1.253.025h7.6c.572 0 .957 0 1.252-.025c.288-.023.425-.065.515-.111a1.25 1.25 0 0 0 .547-.547c.046-.09.088-.227.111-.515c.024-.295.025-.68.025-1.252zM10.5 7a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <Transition enter-active-class="transition ease-out duration-150"
                            enter-from-class="opacity-0 translate-y-1 scale-95"
                            enter-to-class="opacity-100 translate-y-0 scale-100"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0 scale-100"
                            leave-to-class="opacity-0 translate-y-1 scale-95">
                            <div v-if="showCalendar"
                                class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-[280px] bg-white rounded-[10px] shadow-lg border border-gray-100 z-50 p-4 space-y-2">
                                <div class="flex items-center justify-center mb-2">
                                    <div class="flex gap-2 relative">
                                        <div class="relative">
                                            <button @click.stop="
                                                toggleMonthDropdown
                                            " type="button"
                                                class="text-sm font-semibold text-neutral-800 cursor-pointer flex items-center p-2 rounded-lg border border-gray-200 hover:bg-gray-50">
                                                {{
                                                    currentCalendarMonth.format(
                                                        "MMM"
                                                    )
                                                }}
                                                <svg class="w-4 h-4 inline ml-1 transition-transform duration-200"
                                                    :class="{
                                                        'rotate-180':
                                                            showMonthDropdown,
                                                    }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            <div v-if="showMonthDropdown"
                                                class="absolute z-20 w-24 bg-white border border-gray-200 rounded-md shadow-lg h-48 overflow-y-auto scrollbar-hide top-full mt-1 left-0">
                                                <div v-for="(m, i) in monthOptions" :key="i"
                                                    @click.stop="selectMonth(i)" :class="{
                                                        'bg-red-500 text-white':
                                                            currentCalendarMonth.month() ===
                                                            i,
                                                    }"
                                                    class="p-2 text-sm hover:bg-red-100 hover:text-red-900 cursor-pointer text-center">
                                                    {{ m }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="relative">
                                            <button @click.stop="toggleYearDropdown" type="button"
                                                class="text-sm font-semibold text-neutral-800 cursor-pointer flex items-center p-2 rounded-lg border border-gray-200 hover:bg-gray-50">
                                                {{
                                                    currentCalendarMonth.format(
                                                        "YYYY"
                                                    )
                                                }}
                                                <svg class="w-4 h-4 inline ml-1 transition-transform duration-200"
                                                    :class="{
                                                        'rotate-180':
                                                            showYearDropdown,
                                                    }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            <div v-if="showYearDropdown"
                                                class="absolute z-20 w-20 bg-white border border-gray-200 rounded-md shadow-lg h-48 overflow-y-auto scrollbar-hide top-full mt-1 left-0">
                                                <div v-for="y in yearOptions" :key="y" @click.stop="selectYear(y)"
                                                    :class="{
                                                        'bg-red-500 text-white':
                                                            currentCalendarMonth.year() ===
                                                            y,
                                                    }"
                                                    class="p-2 text-sm hover:bg-red-100 hover:text-red-900 cursor-pointer text-center">
                                                    {{ y }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-base font-medium px-1 mb-2">
                                    <span class="font-normal text-sm text-gray-500">{{
                                        currentCalendarMonth.format(
                                            "MMMM YYYY"
                                        )
                                    }}</span>
                                    <div>
                                        <span @click.stop="prevMonth"
                                            class="text-red-500 cursor-pointer p-1 rounded-full hover:bg-gray-100 inline-block mr-1">&lt;</span>
                                        <span @click.stop="nextMonth"
                                            class="text-red-500 cursor-pointer p-1 rounded-full hover:bg-gray-100 inline-block">&gt;</span>
                                    </div>
                                </div>
                                <table class="w-full text-center text-xs border-collapse">
                                    <thead>
                                        <tr class="text-gray-500 font-medium">
                                            <th v-for="day in [
                                                'Su',
                                                'Mo',
                                                'Tu',
                                                'We',
                                                'Th',
                                                'Fr',
                                                'Sa',
                                            ]" :key="day" class="font-medium pb-2">
                                                {{ day }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(week, wIndex) in calendarWeeks" :key="wIndex">
                                            <td v-for="(day, dIndex) in week" :key="dIndex" class="p-0 align-middle"
                                                @click.stop="
                                                    day.isCurrentMonth
                                                        ? selectDate(day.date)
                                                        : null
                                                    ">
                                                <div :class="[
                                                    'w-8 h-8 flex items-center justify-center rounded-lg transition duration-150 mx-auto',

                                                    // 1. ตรวจสอบว่าเป็นเดือนปัจจุบันหรือไม่ (สีเทาเข้ม vs สีเทาจาง)
                                                    day.isCurrentMonth
                                                        ? 'cursor-pointer hover:bg-gray-100 text-gray-800'
                                                        : 'text-gray-300 pointer-events-none',

                                                    // 2. ถ้าเป็นวันที่ถูกเลือก (Selected) -> สีแดง
                                                    day.date === eventDate
                                                        ? '!bg-red-500 !text-white font-bold shadow-sm'
                                                        : '',

                                                    // 3. ถ้าเป็นวันปัจจุบัน (Today) และไม่ได้ถูกเลือก -> สีฟ้า ตัวหนังสือขาว (แก้ไขตรงนี้)
                                                    day.isToday &&
                                                        day.date !== eventDate
                                                        ? 'bg-blue-500 text-white font-bold'
                                                        : '',]">
                                                    {{ day.day }}
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="mt-2 flex justify-center pt-2 border-t border-gray-100">
                                    <button @click.stop="clearDate"
                                        class="text-xs text-gray-500 hover:text-red-500 font-medium px-3 py-1 rounded hover:bg-gray-50 transition">
                                        Clear Date
                                    </button>
                                </div>
                            </div>
                        </Transition>
                        <p v-if="errors.eventDate" class="text-red-500 text-xs mt-1 ml-1 font-medium">
                            Required field
                        </p>
                    </div>
                    <!-- Time Input -->
                    <div>
                        <label class="block text-neutral-800 font-semibold text-[15px] mb-2">Time <span
                                class="text-red-600">*</span></label>
                        <div
                            :class="['flex h-[52px] w-full items-center rounded-2xl border px-2 shadow-sm bg-white transition', errors.eventTime ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus-within:ring-2 focus-within:ring-rose-300 focus-within:border-rose-400']">
                            <div class="relative flex-1 flex items-center justify-center h-full overflow-hidden">
                                <span v-if="!eventTimeStart"
                                    class="absolute pointer-events-none text-red-300 text-[15px] font-medium z-10">Start</span>
                                <input type="time" v-model="eventTimeStart" step="300"
                                    :class="['time-input w-full bg-transparent text-[15px] font-medium outline-none text-center cursor-pointer caret-transparent z-20', eventTimeStart ? 'text-neutral-800' : 'text-transparent']"
                                    @click="$event.target.showPicker()" @change="errors.eventTime = false"
                                    @keydown.prevent />
                            </div>
                            <!-- Time Separator -->
                            <span class="flex-none text-[16px] font-bold text-red-300 px-1">:</span>
                            <div class="relative flex-1 flex items-center justify-center h-full overflow-hidden">
                                <span v-if="!eventTimeEnd"
                                    class="absolute pointer-events-none text-red-300 text-[15px] font-medium z-10">End</span>
                                <input type="time" v-model="eventTimeEnd" step="300"
                                    :class="['time-input w-full bg-transparent text-[15px] font-medium outline-none text-center cursor-pointer caret-transparent z-20', eventTimeEnd ? 'text-neutral-800' : 'text-transparent']"
                                    @click="$event.target.showPicker()" @change="errors.eventTime = false"
                                    @keydown.prevent />
                            </div>
                            <Icon icon="mdi:clock-outline"
                                class="flex-none w-5 h-5 text-red-700 mr-2 pointer-events-none" />
                        </div>
                        <p v-if="errors.eventTime" class="text-red-500 text-xs mt-1 ml-1 font-medium">
                            Required field
                        </p>
                    </div>
                    <div>
                        <!-- Duration -->
                        <label class="block text-neutral-800 font-semibold text-[15px] mb-2 ">Duration</label>
                        <div
                            class="flex h-[52px] w-full items-center gap-3 rounded-2xl border border-neutral-200 px-4 shadow-sm bg-[#F9FAFB]">
                            <input
                                class="w-full h-full bg-transparent font-medium text-neutral-600 outline-none border-0"
                                disabled v-model="eventDurationDisplay" />
                            <Icon icon="lucide:clock-fading" class="w-6 h-6 text-neutral-400" />
                        </div>
                    </div>
                </div>
                <!-- Location -->
                <div class="mt-6">
                    <label class="block text-neutral-800 font-semibold text-[15px] mb-2">Location <span
                            class="text-red-600">*</span></label>
                    <!-- Location Input -->
                    <InputPill v-model="eventLocation" placeholder="Location/Building/Room Name" :class="[
                        'w-full h-[52px] font-medium text-[16px] text-neutral-800 border rounded-[20px] px-5 focus:outline-none transition',
                        'placeholder:text-red-300',
                        errors.eventLocation ? 'border-red-500 bg-red-50' : 'border-neutral-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-300'
                    ]" @input="errors.eventLocation = false" />
                    <p v-if="errors.eventLocation" class="text-red-500 text-xs mt-1 ml-1 font-medium">Required field</p>
                </div>
            </div>
            <!-- Upload attachments -->
            <div class="col-span-4 mx-5 mt-5 mb-2">
                <h3 class="text-[17px] font-semibold text-neutral-800">Upload attachments</h3>
                <p class="text-sm text-neutral-800 mb-2">Drag and drop document to your support task</p>
                <!-- ▼ Drop zone -->
                <div class="group relative rounded-2xl border-2 border-dashed border-red-700 bg-red-100 p-6 transition-all"
                    :class="{ 'ring-2 ring-rose-300 bg-rose-100': dragging }" @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false" @drop.prevent="onDrop">
                    <!-- รายการไฟล์ (เดิม + ใหม่) เต็มความกว้าง เรียงลงมา -->
                    <div v-if="filesNew.length" class="mb-4 space-y-2">
                        <div v-for="(file, index) in filesNew" :key="file.name + index"
                            class="w-full flex items-center justify-between rounded-2xl bg-white border border-neutral-200 px-4 py-3 shadow-sm">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-md">
                                    <Icon icon="basil:file-solid" class="h-10 w-10 text-red-700" />
                                </div>
                                <span class="truncate text-[16px] text-neutral-800">{{ file.name }}</span>
                            </div>
                            <button type="button"
                                class="inline-flex h-7 w-7 items-center justify-center rounded-full text-neutral-600 hover:bg-neutral-100"
                                @click="removeFile(index)" aria-label="Remove file" title="Remove">
                                ✕
                            </button>
                        </div>
                    </div>
                    <!-- เมฆ + ข้อความ: โชว์เฉพาะตอน “ยังไม่มีไฟล์เลย” -->
                    <div v-else class="flex flex-col items-center justify-center text-center min-h-[260px]">
                        <Icon icon="ep:upload-filled" class="w-40 h-28 mb-3 text-red-300" />
                        <p class="text-[16px]  font-medium text-neutral-800">Choose a file or drag &amp; drop it here
                        </p>
                        <p class="mt-1 text-sm text-neutral-800">pdf, txt, docx, jpeg, xlsx</p>
                    </div>
                    <!-- ปุ่ม Browse: อยู่ล่างกลางเสมอ -->
                    <div class="flex justify-center mt-1 mb-12">
                        <button type="button"
                            class="inline-flex items-center rounded-[10px] border  bg-white border-rose-500 px-2 py-1  text-neutral-800 hover:bg-rose-50 active:bg-rose-100 "
                            @click="pickFiles">
                            <span class="text-sm font-medium">Browse files</span>
                        </button>
                    </div>
                    <!-- input file (ซ่อน) -->
                    <input ref="fileInput" type="file" multiple class="hidden"
                        accept=".pdf,.txt,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls" @change="onPick" />
                </div>
            </div>
        </div>
        <!-- Add Guest Section -->
        <div class="mt-4"> <!-- คุมจากด้านบนแทน -->
            <h3 class="text-3xl font-semibold text-neutral-800 mb-4">Add Guest</h3>

            <h4 class="text-xl font-medium text-neutral-800 mb-3">Search</h4>
            <div class="flex flex-wrap items-center gap-4 w-full">
                <div class="flex items-center gap-2 flex-1 min-w-[320px]">
                    <div class="relative w-full">
                        <!-- Search Input -->
                        <input v-model="searchRaw" type="text" placeholder="Search ID / Name / Nickname"
                            class="w-full h-[48px] rounded-[30px] border border-neutral-200 px-6 text-[15px] text-neutral-800 placeholder:text-rose-300 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-200 transition bg-white"
                            @keyup.enter="performSearch" />
                    </div>
                    <!-- Search Button -->
                    <button type="button"
                        class="flex-none grid place-items-center w-[48px] h-[48px] rounded-full bg-[#B91C1C] text-white hover:bg-red-800 transition shadow-sm"
                        @click="performSearch">
                        <Icon icon="ic:baseline-search" class="w-6 h-6" />
                    </button>
                </div>
                <!-- Employee Filters -->
                <div class="flex flex-row flex-wrap items-center gap-2">
                    <EmployeeDropdown label="Company ID" v-model="selectedCompanyIds" :options="companyIdOptions" />
                    <EmployeeDropdown label="Department" v-model="selectedDepartmentIds" :options="departmentOptions" />
                    <EmployeeDropdown label="Team" v-model="selectedTeamIds" :options="teamOptions" />
                    <EmployeeDropdown label="Position" v-model="selectedPositionIds" :options="positionOptions" />
                </div>
            </div>
            <!-- Employee Data Table -->
            <div class="mt-8">
                <DataTable :rows="pagedEmployees" :columns="columns" :loading="loadingEmployees"
                    :totalItems="filteredEmployees.length" v-model:page="page" v-model:pageSize="perPage"
                    :pageSizeOptions="[10, 25, 50]" :selectable="true" :showRowNumber="true" rowKey="id"
                    :modelValue="selectedIdsArr" @update:modelValue="onUpdateSelected">
                    <template #cell-fullname="{ row }">
                        {{
                            (row.emp_firstname || "") +
                            " " +
                            (row.emp_lastname || "")
                        }}
                    </template>
                    <template #empty>
                        <div class="py-8 text-center text-neutral-400">
                            ไม่พบข้อมูลพนักงาน
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>
        <!-- Cancel Buttons -->
        <div class="mt-10 w-full flex flex-row justify-between items-center border-t border-neutral-100 pt-8">
            <div class="flex-none">
                <button type="button" @click="onCancel" :disabled="saving"
                    class="inline-flex items-center justify-center gap-2 rounded-[20px] px-4 bg-[#C10008] text-white font-semibold hover:bg-red-700 w-[140px] h-[48px] transition shadow-sm">
                    <Icon icon="ic:baseline-plus" class="w-5 h-5 text-white rotate-45" />
                    <span>Cancel</span>
                </button>
            </div>

            <!-- Create Button -->
            <div class="flex-none">
                <button type="button" @click="saveEvent" :disabled="saving"
                    class="inline-flex items-center justify-center gap-2 rounded-[20px] px-4 bg-[#00A73D] text-white font-semibold hover:bg-green-700 w-[140px] h-[48px] transition shadow-sm">
                    <Icon icon="ic:baseline-plus" class="w-5 h-5 text-white" />
                    <span>Create</span>
                </button>
            </div>
        </div>
        <!-- Confirm Create Modal -->
        <ModalAlert v-model:open="showConfirmCreate" title="Confirm Creation"
            message="Are you sure you want to create this event?" type="confirm" :showCancel="true"
            @confirm="executeCreateEvent" />

        <!-- Success Alert Modal -->
        <ModalAlert v-model:open="showSuccessAlert" title="Success" message="New event has been created." type="success"
            :showCancel="false" @confirm="onSuccessConfirm" />
    </div>
</template>

<script>
import axios from "axios";
import InputPill from "@/components/Input/InputPill.vue";
import { Icon } from "@iconify/vue";
import DataTable from "@/components/DataTable.vue";
import EmployeeDropdown from "@/components/EmployeeDropdown.vue";
import CancelButton from "@/components/Button/CancelButton.vue";
import ModalAlert from "@/components/Alert/ModalAlert.vue";

// Day.js imports for Calendar Logic
import dayjs from "dayjs";
import "dayjs/locale/en";
import weekday from "dayjs/plugin/weekday";
import weekOfYear from "dayjs/plugin/weekOfYear";
import isBetween from "dayjs/plugin/isBetween";
import customParseFormat from "dayjs/plugin/customParseFormat";

dayjs.extend(weekday);
dayjs.extend(weekOfYear);
dayjs.extend(isBetween);
dayjs.extend(customParseFormat);
dayjs.locale("en");

export default {
    components: {
        InputPill,
        Icon,
        DataTable,
        EmployeeDropdown,
        CancelButton,
        ModalAlert,
    },
    data() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, "0");
        const day = String(now.getDate()).padStart(2, "0");

        return {
            // ฟิลด์ข้อมูลอีเวนต์
            eventTitle: "",
            eventCategoryId: "",
            eventDescription: "",
            eventDate: "",
            eventTimeStart: "",
            eventTimeEnd: "",
            eventDurationDisplay: "",
            eventDurationMinutes: 0,
            eventLocation: "",

            // การตรวจสอบวันที่
            minDate: `${year}-${month}-${day}`,

            // ข้อผิดพลาดในการตรวจสอบฟอร์ม
            errors: {
                eventTitle: false,
                eventCategoryId: false,
                eventDescription: false,
                eventDate: false,
                eventTime: false,
                eventLocation: false
            },

            // การจัดการไฟล์แนบ
            selectCategory: [],
            filesNew: [],
            dragging: false,

            // ข้อมูลพนักงานและสถานะการโหลด
            employees: [],
            loadingEmployees: false,

            // ฟังก์ชันการค้นหา
            search: "",    // ค่าตัวกรองที่ใช้งานจริง
            searchRaw: "", // ค่า v-model ของช่องค้นหา

            // ตัวกรองพนักงานที่เลือก
            selectedCompanyIds: [],
            selectedDepartmentIds: [],
            selectedTeamIds: [],
            selectedPositionIds: [],

            // ตัวเลือกสำหรับ dropdown ตัวกรอง
            companyIdOptions: [],
            departmentOptions: [],
            teamOptions: [],
            positionOptions: [],

            // สถานะตารางและการเลือก
            selectedIds: new Set(),
            page: 1,
            perPage: 10,
            saving: false,
            showConfirmCreate: false,
            showSuccessAlert: false,

            // Calendar Logic State
            showCalendar: false,
            showMonthDropdown: false,
            showYearDropdown: false,
            currentCalendarMonth: dayjs(),
        };
    },

    computed: {
        formattedDateDisplay() {
            if (!this.eventDate) return "";
            return dayjs(this.eventDate).format("DD/MM/YY");
        },

        // --- Calendar Computed ---
        monthOptions() {
            return Array.from({ length: 12 }, (v, i) =>
                dayjs().month(i).format("MMM")
            );
        },

        yearOptions() {
            const currentYear = dayjs().year();
            const years = [];
            for (let i = currentYear - 10; i <= currentYear + 10; i++)
                years.push(i);
            return years;
        },

        calendarWeeks() {
            const startOfMonth = this.currentCalendarMonth.startOf("month");
            const endOfMonth = this.currentCalendarMonth.endOf("month");
            const startOfWeek = startOfMonth.startOf("week");
            const endOfWeek = endOfMonth.endOf("week");

            const calendar = [];
            let day = startOfWeek;
            const today = dayjs();

            while (day.isBefore(endOfWeek) || day.isSame(endOfWeek, "day")) {
                const week = [];
                for (let i = 0; i < 7; i++) {
                    week.push({
                        day: day.date(),
                        date: day.format("YYYY-MM-DD"),
                        isCurrentMonth: day.isSame(
                            this.currentCalendarMonth,
                            "month"
                        ),
                        isToday: day.isSame(today, "day"),
                    });
                    day = day.add(1, "day");
                }
                calendar.push(week);
            }
            return calendar;
        },

        hasAnyFiles() {
            return this.filesNew.length > 0; // ถ้ามีของเดิมด้วย ค่อย OR เพิ่มภายหลัง
        },

        uploadItems() {
            // ตอนนี้เอาเฉพาะไฟล์ใหม่ก่อน
            return this.filesNew.map((f, index) => ({
                kind: "new",
                key: `new-${index}-${f.name}`,
                name: f.name,
                index,
            }));
        },
        // -------------------------

        columns() {
            return [
                {
                    key: "emp_id",
                    label: "ID",
                    class: "text-left min-w-[100px]",
                },
                {
                    key: "fullname",
                    label: "Name",
                    class: "text-left min-w-[200px]",
                },
                {
                    key: "nickname",
                    label: "Nickname",
                    class: "text-left min-w-[100px]",
                },
                {
                    key: "department",
                    label: "Department",
                    class: "text-left min-w-[180px]",
                },
                {
                    key: "team",
                    label: "Team",
                    class: "text-left min-w-[140px]",
                },
                {
                    key: "position",
                    label: "Position",
                    class: "text-left min-w-[240px]",
                },
            ];
        },

        // กรองพนักงานตามคำค้นหาและตัวเลือกตัวกรอง
        filteredEmployees() {
            const q = (this.search || "").toLowerCase().trim();
            let list = this.employees;

            if (q) {
                list = list.filter(
                    (e) =>
                        (e.emp_firstname || "").toLowerCase().includes(q) ||
                        (e.emp_id || "").toLowerCase().includes(q) ||
                        (e.nickname || "").toLowerCase().includes(q)
                );
            }

            if (this.selectedCompanyIds.length) list = list.filter(r => this.selectedCompanyIds.includes(r.companyId));
            if (this.selectedDepartmentIds.length) list = list.filter(r => this.selectedDepartmentIds.includes(r.department));
            if (this.selectedTeamIds.length) list = list.filter(r => this.selectedTeamIds.includes(r.team));
            if (this.selectedPositionIds.length) list = list.filter(r => this.selectedPositionIds.includes(r.position));

            return list;
        },

        // แบ่งหน้าพนักงานตามจำนวนรายการต่อหน้า
        pagedEmployees() {
            const start = (this.page - 1) * this.perPage;
            return this.filteredEmployees.slice(start, start + this.perPage);
        },

        // แปลง Set เป็น Array สำหรับ DataTable
        selectedIdsArr: {
            get() { return Array.from(this.selectedIds); },
            set(arr) { this.selectedIds = new Set(arr); }
        }
    },

    watch: {
        eventTimeStart() {
            this.calDuration();
        },
        eventTimeEnd() {
            this.calDuration();
        },
    },

    mounted() {
        this.fetchInfo();
        document.addEventListener("click", this.closeCalendarOnClickOutside);
    },

    beforeUnmount() {
        document.removeEventListener("click", this.closeCalendarOnClickOutside);
    },

    methods: {
        // --- Calendar Methods ---
        closeCalendarOnClickOutside(e) {
            if (
                this.$refs.datePickerContainer &&
                !this.$refs.datePickerContainer.contains(e.target)
            ) {
                this.showCalendar = false;
                this.showMonthDropdown = false;
                this.showYearDropdown = false;
            }
        },

        toggleCalendar() {
            this.showCalendar = !this.showCalendar;
        },

        toggleMonthDropdown() {
            this.showMonthDropdown = !this.showMonthDropdown;
            this.showYearDropdown = false;
        },

        toggleYearDropdown() {
            this.showYearDropdown = !this.showYearDropdown;
            this.showMonthDropdown = false;
        },

        selectMonth(index) {
            this.currentCalendarMonth = this.currentCalendarMonth.month(index);
            this.showMonthDropdown = false;
        },

        selectYear(year) {
            this.currentCalendarMonth = this.currentCalendarMonth.year(year);
            this.showYearDropdown = false;
        },

        prevMonth() {
            this.currentCalendarMonth = this.currentCalendarMonth.subtract(
                1,
                "month"
            );
        },

        nextMonth() {
            this.currentCalendarMonth = this.currentCalendarMonth.add(
                1,
                "month"
            );
        },

        selectDate(dateStr) {
            this.eventDate = dateStr;
            this.errors.eventDate = false;
            this.showCalendar = false;
        },

        clearDate() {
            this.eventDate = "";
            this.showCalendar = false;
        },

        // ------------------------
        performSearch() {
            this.search = this.searchRaw;
            this.page = 1;
        },

        // คำนวณระยะเวลาอีเวนต์จากเวลาเริ่มและเวลาสิ้นสุด
        calDuration() {
            if (!this.eventTimeStart || !this.eventTimeEnd) {
                this.eventDurationDisplay = "";
                return;
            }
            const [sh, sm] = this.eventTimeStart.split(":").map(Number);
            const [eh, em] = this.eventTimeEnd.split(":").map(Number);
            let diff = eh * 60 + em - (sh * 60 + sm);
            if (diff < 0) diff += 24 * 60;
            this.eventDurationMinutes = diff;
            const h = Math.floor(diff / 60);
            const m = diff % 60;
            this.eventDurationDisplay =
                h > 0 ? `${h} Hour ${m} Min` : `${m} Min`;
        },

        // ตรวจสอบฟิลด์ที่จำเป็นและแสดง modal ยืนยัน
        saveEvent() {
            this.errors.eventTitle = !this.eventTitle;
            this.errors.eventCategoryId = !this.eventCategoryId;
            this.errors.eventDescription = !this.eventDescription;
            this.errors.eventDate = !this.eventDate;
            this.errors.eventTime = !this.eventTimeStart || !this.eventTimeEnd;
            this.errors.eventLocation = !this.eventLocation;
            if (Object.values(this.errors).some((v) => v)) return;
            this.showConfirmCreate = true;
        },

        // สร้างอีเวนต์และส่งข้อมูลไปยัง API
        async executeCreateEvent() {
            this.showConfirmCreate = false;
            this.saving = true;
            try {
                // สร้าง FormData สำหรับส่งข้อมูลพร้อมไฟล์
                const formData = new FormData();
                // เพิ่มข้อมูลอีเวนต์พื้นฐาน
                formData.append("event_title", this.eventTitle.trim());
                formData.append("event_category_id", this.eventCategoryId);
                formData.append("event_description", this.eventDescription);
                formData.append("event_date", this.eventDate);
                formData.append("event_timestart", this.eventTimeStart);
                formData.append("event_timeend", this.eventTimeEnd);
                formData.append("event_duration", this.eventDurationMinutes);
                formData.append("event_location", this.eventLocation);
                // เพิ่มไฟล์แนบทั้งหมด
                this.filesNew.forEach(f => formData.append("attachments[]", f));
                // เพิ่ม ID พนักงานที่เลือกทั้งหมด
                this.selectedIds.forEach(id => formData.append("employee_ids[]", id));
                // ส่งข้อมูลไปยัง API
                await axios.post("/event-save", formData, { headers: { "Content-Type": "multipart/form-data" } });
                this.showSuccessAlert = true;
            } catch (err) {
                console.error(err);
                alert("Failed to create event.");
            } finally {
                this.saving = false;
            }
        },

        // ปิด modal และกลับไปหน้ารายการอีเวนต์
        onSuccessConfirm() {
            this.showSuccessAlert = false;
            this.$router.push("/event");
        },

        // ดึงข้อมูลหมวดหมู่และพนักงานจาก API
        async fetchInfo() {
            try {
                this.loadingEmployees = true;
                const res = await axios.get("/event-info");
                // เก็บข้อมูลหมวดหมู่อีเวนต์
                this.selectCategory = res.data?.categories || [];
                // แปลงข้อมูลพนักงานและสร้าง companyId จากรหัสพนักงาน
                this.employees = (res.data?.employees || []).map(e => {
                    // ดึงตัวอักษรนำหน้าจากรหัสพนักงานเป็นรหัสบริษัท (เช่น ABC123 -> ABC)
                    const rawId = String(e.emp_id || "").trim();
                    const prefixMatch = rawId.match(/^[A-Za-z]+/);
                    const companyPrefix = prefixMatch
                        ? prefixMatch[0].toUpperCase()
                        : "";
                    return {
                        id: e.id,
                        emp_id: e.emp_id || "",
                        emp_firstname: e.emp_firstname || "",
                        emp_lastname: e.emp_lastname || "",
                        nickname: e.emp_nickname || "",
                        department: e.department_name || "",
                        companyId: companyPrefix || e.company_id || "",
                        team: e.team_name || "",
                        position: e.position_name || "",
                    };
                });
                // สร้างตัวเลือกสำหรับตัวกรอง
                this.buildFilterOptions();
            } catch (err) {
                console.error(err);
            } finally {
                this.loadingEmployees = false;
            }
        },

        // สร้างตัวเลือกสำหรับ dropdown ตัวกรองจากข้อมูลพนักงาน
        buildFilterOptions() {
            const toOpt = (arr) => [...new Set(arr.filter(Boolean))].sort().map(v => ({ label: v, value: v }));
            this.companyIdOptions = toOpt(this.employees.map(r => r.companyId));
            this.departmentOptions = toOpt(this.employees.map(r => r.department));
            this.teamOptions = toOpt(this.employees.map(r => r.team));
            this.positionOptions = toOpt(this.employees.map(r => r.position));

        },

        // เปิด file picker dialog
        pickFiles() { this.$refs.fileInput.click(); },
        // จัดการเมื่อเลือกไฟล์จาก file picker
        onPick(e) { this.addFiles([...e.target.files]); e.target.value = ""; },
        // จัดการเมื่อ drop ไฟล์ลงใน drop zone
        onDrop(e) { this.dragging = false; this.addFiles([...e.dataTransfer.files]); },
        // เพิ่มไฟล์ที่มีขนาดไม่เกิน 50MB
        addFiles(files) { files.forEach(f => { if (f.size <= 50 * 1024 * 1024) this.filesNew.push(f); }); },
        // ลบไฟล์ออกจากรายการ
        removeFile(idx) { this.filesNew.splice(idx, 1); },
        // แปลงขนาดไฟล์เป็น MB
        prettySize(byte) { return (byte / (1024 * 1024)).toFixed(2) + " MB"; },
        // อัปเดต ID พนักงานที่เลือก
        onUpdateSelected(ids) { this.selectedIds = new Set(ids); },
        // ยกเลิกและกลับหน้าก่อนหน้า
        onCancel() { this.$router.back(); },
        onRootPointer() { }
    }
};
</script>

<style scoped>
input[type="time"]::-webkit-datetime-edit,
input[type="time"]::-webkit-datetime-edit-fields-wrapper,
input[type="time"]::-webkit-datetime-edit-hour-field,
input[type="time"]::-webkit-datetime-edit-minute-field,
input[type="time"]::-webkit-datetime-edit-text {
    color: inherit;
    padding: 0;
}

input[type="time"]::-webkit-datetime-edit-hour-field:focus,
input[type="time"]::-webkit-datetime-edit-minute-field:focus {
    background-color: transparent !important;
    color: inherit;
    outline: none;
}

input[type="time"]::selection {
    background: transparent;
}

input[type="time"]::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    cursor: pointer;
    opacity: 0;
}

.caret-transparent {
    caret-color: transparent;
}

/* Utility for hiding scrollbar in Dropdown */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
