import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import About from '../pages/About.vue'
import Login from '../pages/Login.vue'
import AddEmployee from '../pages/add_employee.vue'
import Event from '../pages/event_page.vue'
import Employees from '../pages/employees_page.vue'
import History from '../pages/delete_page.vue'
import create_event from '../pages/create_event.vue'
import Category from '../pages/category_table.vue'
import reply_form from '../pages/Reply_form.vue'
import Edit_event from '../pages/edit_event.vue'


const routes = [
  { path: '/', component: Home, meta: { title: 'Dashboard' } },
  { path: '/about', component: About, meta: { title: 'About' } },
  { path: '/login', component: Login, meta: { blank: true, title: 'Login' } },
  { path: '/add-employee', component: AddEmployee, meta: { title: 'Employee' } },
  { path: '/edit-event/:id', component: Edit_event, meta: { title: 'Event' } },

  // เพิ่มตามเมนูในภาพ (ตัวอย่าง)
  { path: '/event', component: Event, meta: { title: 'Event' } },
  { path: '/create-event', component: create_event, meta: { title: 'Create Event' } },
  { path: '/employee', component: Employees, meta: { title: 'Employee' } },
  { path: '/history', component: History, meta: { title: 'History' } },
  { path: '/categories', component: Category, meta: { title: 'Category' } },
  { path: '/reply-form', component: reply_form, meta: { title: 'Reply Form'}},

]

export default createRouter({
  history: createWebHistory(),
  routes,
})
