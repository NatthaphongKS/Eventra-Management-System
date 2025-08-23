<template>
  <!-- การ์ดแก้ไขพนักงาน -->
  <section class="p-0">
    <div class="w-full max-w-3xl mx-auto bg-white rounded-2xl shadow border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">Edit Employee</h2>
        <button class="px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200" @click="$router.back()">← Back</button>
      </div>

      <!-- สถานะโหลด/ผิดพลาด -->
      <div v-if="loading" class="py-10 text-center text-gray-500">Loading…</div>
      <div v-else-if="loadError" class="py-10 text-center text-rose-600">{{ loadError }}</div>

      <form v-else @submit.prevent="save" class="space-y-4">
        <!-- รหัส/อีเมล/โทร -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Employee ID</label>
            <input v-model.trim="form.emp_id" type="text"
              class="w-full h-10 px-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-rose-300" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input v-model.trim="form.emp_email" type="email"
              class="w-full h-10 px-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-rose-300" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Phone</label>
            <input v-model.trim="form.emp_phone" type="text" maxlength="10"
              class="w-full h-10 px-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-rose-300" />
          </div>
        </div>

        <!-- คำนำหน้า/ชื่อ/นามสกุล/ชื่อเล่น -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Prefix</label>
            <select v-model="form.emp_prefix"
              class="w-full h-10 px-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-rose-300">
              <option value="">-</option>
              <option>Mr.</option>
              <option>Mrs.</option>
              <option>Ms.</option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">First name</label>
            <input v-model.trim="form.emp_firstname" type="text"
              class="w-full h-10 px-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-rose-300" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Nickname</label>
            <input v-model.trim="form.emp_nickname" type="text"
              class="w-full h-10 px-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-rose-300" />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Last name</label>
            <input v-model.trim="form.emp_lastname" type="text"
              class="w-full h-10 px-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-rose-300" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Permission</label>
            <select v-model="form.emp_permission"
              class="w-full h-10 px-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-rose-300">
              <option value="">-</option>
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>

        <!-- แผนก/ทีม/ตำแหน่ง -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Department</label>
            <select v-model="form.emp_department_id"
              class="w-full h-10 px-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-rose-300">
              <option value="">-</option>
              <option v-for="d in meta.departments" :key="d.id" :value="d.id">{{ d.dpm_name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Team</label>
            <select v-model="form.emp_team_id"
              class="w-full h-10 px-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-rose-300">
              <option value="">-</option>
              <option v-for="t in meta.teams" :key="t.id" :value="t.id">{{ t.tm_name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Position</label>
            <select v-model="form.emp_position_id"
              class="w-full h-10 px-3 rounded-md border border-gray-300 focus:ring-2 focus:ring-rose-300">
              <option value="">-</option>
              <option v-for="p in meta.positions" :key="p.id" :value="p.id">{{ p.pst_name }}</option>
            </select>
          </div>
        </div>

        <!-- ปุ่ม -->
        <div class="pt-2 flex items-center gap-3">
          <button type="button" class="px-4 h-10 rounded-lg bg-gray-100 hover:bg-gray-200" @click="$router.back()">
            Cancel
          </button>
          <button type="submit" class="px-5 h-10 rounded-lg bg-rose-600 text-white hover:bg-rose-700" :disabled="saving">
            {{ saving ? 'Saving…' : 'Save' }}
          </button>
        </div>

        <!-- แจ้งผล -->
        <p v-if="saveError" class="text-rose-600 text-sm pt-2">{{ saveError }}</p>
        <p v-if="saveDone" class="text-emerald-600 text-sm pt-2">Updated successfully.</p>
      </form>
    </div>
  </section>
</template>

<script>
import axios from 'axios'

export default {
  name: 'EditEmployeePage',
  props: { id: { type: String, required: true } },
  data() {
    return {
      loading: true,
      loadError: '',
      saving: false,
      saveError: '',
      saveDone: false,
      meta: { positions: [], departments: [], teams: [] },
      form: {
        emp_id: '',
        emp_prefix: '',
        emp_firstname: '',
        emp_lastname: '',
        emp_nickname: '',
        emp_email: '',
        emp_phone: '',
        emp_position_id: '',
        emp_department_id: '',
        emp_team_id: '',
        emp_permission: '',
      },
    }
  },
  async created() {
    try {
      const meta = await axios.get('/employees-meta') // หรือ /meta
      this.meta = meta.data

      const res = await axios.get(`/employees/${this.id}`)
      const e = res.data.data || res.data
      this.form = {
        emp_id: e.emp_id ?? '',
        emp_prefix: e.emp_prefix ?? '',
        emp_firstname: e.emp_firstname ?? '',
        emp_lastname: e.emp_lastname ?? '',
        emp_nickname: e.emp_nickname ?? '',
        emp_email: e.emp_email ?? '',
        emp_phone: e.emp_phone ?? '',
        emp_position_id: e.emp_position_id ?? '',
        emp_department_id: e.emp_department_id ?? '',
        emp_team_id: e.emp_team_id ?? '',
        emp_permission: e.emp_permission ?? '',
      }
    } catch (err) {
      console.error(err)
      this.loadError = 'Failed to load employee.'
    } finally {
      this.loading = false
    }
  },
  methods: {
    async save() {
      this.saving = true
      this.saveError = ''
      this.saveDone = false
      try {
        await axios.put(`/employees/${this.id}`, this.form)
        this.saveDone = true
      } catch (err) {
        console.error(err)
        this.saveError = 'Failed to save.'
      } finally {
        this.saving = false
      }
    },
  },
}
</script>
