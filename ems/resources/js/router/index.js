import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import Login from '../pages/Login.vue'
import AddEmployee from '../pages/add_employee.vue'
import Event from '../pages/event_page.vue'
import Employees from '../pages/employees_page.vue'
import History from '../pages/delete_page.vue'
import create_event from '../pages/create_event.vue'
import Category from '../pages/category_table.vue'
import reply_form from '../pages/Reply_form.vue'
import UploadFile from '../pages/upload_file.vue'

// [เพิ่ม] หน้าแก้ไข
import EditEmployee from '../pages/edit_employee.vue'

const routes = [
  { path: '/', component: Home, meta: { title: 'Dashboard' } },
  { path: '/login', component: Login, meta: { blank: true, title: 'Login' } },
  { path: '/add-employee', component: AddEmployee, meta: { title: 'Employee' } },
  { path: '/event', component: Event, meta: { title: 'Event' } },
  { path: '/add-event', component: create_event, meta: { title: 'Create Event' } },
  { path: '/employee', component: Employees, meta: { title: 'Employee' } },
  { path: '/history', component: History, meta: { title: 'History' } },
  { path: '/categories', component: Category, meta: { title: 'Category' } },
  { path: '/reply-form', component: reply_form, meta: { blank: true, title: 'Reply Form'}},
  { path: '/employees/upload', name: 'upload-file', component: UploadFile, meta: { title: 'Upload Employees' } },


  // [เพิ่ม] เส้นทางหน้าแก้ไขพนักงาน
  { path: '/edit-employee/:id', name: 'edit-employee', component: EditEmployee, props: true, meta: { title: 'Employee' } },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})
