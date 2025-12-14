import { createRouter, createWebHistory } from 'vue-router'

// Pages
import Home from '../pages/Dashboard/Home.vue'
import Login from '../pages/Login.vue'
import ForgotPassword from '../pages/ForgotPassword.vue' // [เพิ่ม] นำเข้าไฟล์ ForgotPassword
import AddEmployee from '../pages/Employee/AddEmployeePage.vue'
import EventPage from '../pages/Event/EventPage.vue'
import Employees from '../pages/Employee/EmployeePage.vue'
import History from '../pages/History/DeletePage.vue'
import CreateEvent from '../pages/Event/CreateEvent.vue'
import Category from '../pages/Category/CategoryPage.vue'
import reply_form from '../pages/ReplyForm.vue'
import UploadFile from '../pages/Employee/UploadFile.vue'
import History_Employee from '../pages/History/HistoryEmployee.vue'
import History_Event from '../pages/History/HistoryEvent.vue'
import HistoryCategory from '../components/History/DataTableHistoryCategory.vue'
// [เพิ่ม] หน้าแก้ไข
import EditEvent from '../pages/Event/EditEvent.vue'
import EditEmployee from '../pages/Employee/EditEmployeePage.vue'
import EventDetails from '../pages/Event/EventDetail.vue'
import EventCheckIn from '../pages/Event/EventCheckIn.vue'


const routes = [

  { path: '/', component: Home, meta: { title: 'Dashboard' } },
  { path: '/login', component: Login, meta: { blank: true, title: 'Login' } },
  
  // [เพิ่ม] เส้นทาง Forgot Password (ใส่ blank: true เพื่อให้ไม่มี Sidebar)
  { path: '/forgot-password', component: ForgotPassword, meta: { blank: true, title: 'Forgot Password' } },

  { path: '/add-employee', component: AddEmployee, meta: { title: 'Employee' } },
  { path: '/EditEvent/:id', component: EditEvent, meta: { title: 'Event' } },
  { path: '/eventCheckIn/eveId/:eveId', component: EventCheckIn, meta: { title: 'EventCheckIn' } },

  { path: '/event', component: EventPage, meta: { title: 'Event' } },
  { path: '/add-event', component: CreateEvent, meta: { title: 'Create Event' } },
  { path: '/employee', component: Employees, meta: { title: 'Employee' } },
  { path: '/history', component: History, meta: { title: 'History' } },
  { path: '/categories', component: Category, meta: { title: 'Category' } },
  { path: '/history-category', component: Category, meta: { title: 'History Category' } },
  { path: '/history-employee', component: History_Employee, meta: { title: 'History Employee' } },
  { path: '/history-event', component: History_Event, meta: { title: 'History Event' } },
  { path: '/history-category', component: HistoryCategory, meta: { title: 'History Category'}},

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