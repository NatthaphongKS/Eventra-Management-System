import { createRouter, createWebHistory } from 'vue-router'

// Pages
import Home from '../pages/Home.vue'
import Login from '../pages/Login.vue'
import AddEmployee from '../pages/add_employee.vue'
import EventPage from '../pages/event_page.vue'
import Employees from '../pages/employees_page.vue'
import History from '../pages/delete_page.vue'
import CreateEvent from '../pages/create_event.vue'
import Category from '../pages/category_page.vue'
import reply_form from '../pages/Reply_form.vue'
import UploadFile from '../pages/upload_file.vue'
import History_Employee from '../pages/history_employees.vue'
import History_Event from '../pages/History_Event.vue'
import EditEvent from '../pages/edit_event.vue'
import EditEmployee from '../pages/edit_employee.vue'
import EventDetails from '../pages/event_details.vue'

const routes = [
  { path: '/', component: Home, meta: { title: 'Dashboard' } },
  { path: '/login', component: Login, meta: { blank: true, title: 'Login' } },
  { path: '/add-employee', component: AddEmployee, meta: { title: 'Employee' } },
  { path: '/edit-event/:id', component: EditEvent, meta: { title: 'Edit Event' } },

  { path: '/event', component: EventPage, meta: { title: 'Event' } },
  { path: '/add-event', component: CreateEvent, meta: { title: 'Create Event' } },
  { path: '/employee', component: Employees, meta: { title: 'Employee' } },
  { path: '/history', component: History, meta: { title: 'History' } },
  { path: '/categories', component: Category, meta: { title: 'Category' } },
  { path: '/history-category', component: Category, meta: { title: 'History Category' } },
  { path: '/history-employee', component: History_Employee, meta: { title: 'History Employee' } },
  { path: '/history-event', component: History_Event, meta: { title: 'History Event' } },

  { path: '/reply-form', component: reply_form, meta: { blank: true, title: 'Reply Form'} },
  { path: '/employees/upload', name: 'upload-file', component: UploadFile, meta: { title: 'Upload Employees' } },

  { path: '/edit-employee/:id', name: 'edit-employee', component: EditEmployee, props: true, meta: { title: 'Edit Employee' } },
  { path: '/events/:id', name: 'event.details', component: EventDetails, props: true, meta: { title: 'Event Details' } },
  { path: '/event/:id', redirect: to => ({ name: 'event.details', params: { id: to.params.id } }) },


]

export default createRouter({
  history: createWebHistory(),
  routes,
})