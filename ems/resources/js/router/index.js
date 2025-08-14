import { createRouter, createWebHistory } from 'vue-router'
import Home from '../pages/Home.vue'
import About from '../pages/About.vue'
import Login from '../pages/Login.vue'
import AddEmployee from '../pages/add_employee.vue'
import Event from '../pages/event_page.vue'
import Employees from '../pages/employees_page.vue'
import Deleted from '../pages/delete_page.vue'
import create_event from '../pages/create_event.vue'

const routes = [
  { path: '/', component: Home, meta: { title: 'Dashboard' } },
  { path: '/about', component: About, meta: { title: 'About' } },
  { path: '/login', component: Login, meta: { blank: true, title: 'Login' } },
  { path: '/add-employee', component: AddEmployee, meta: { title: 'Employee' } },

  // เพิ่มตามเมนูในภาพ (ตัวอย่าง)
  { path: '/event', component: Event, meta: { title: 'Event' } },
  { path: '/create-event', component: create_event, meta: { title: 'Create Event' } },
  { path: '/employee', component: Employees, meta: { title: 'Employee' } },
  { path: '/deleted', component: Deleted, meta: { title: 'Deleted' } },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})
