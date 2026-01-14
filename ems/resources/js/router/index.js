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
import HistoryDetailsEvent from '../pages/History/HistoryDetailsEvent.vue'
import HistoryCategory from '../pages/History/HistoryCategory.vue'
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

    { path: '/add-employee', component: AddEmployee, meta: { title: 'Employee', activeMenu: '/employee' } },
    { path: '/EditEvent/:id', component: EditEvent, meta: { title: 'Event', activeMenu: '/event' } },
    { path: '/eventCheckIn/eveId/:eveId', component: EventCheckIn, meta: { title: 'EventCheckIn', activeMenu: '/event' } },

    { path: '/event', component: EventPage, meta: { title: 'Event', activeMenu: '/event' } },
    { path: '/add-event', component: CreateEvent, meta: { title: 'Create Event', activeMenu: '/event' } },
    { path: '/employee', component: Employees, meta: { title: 'Employee', activeMenu: '/employee' } },
    { path: '/history', component: History, meta: { title: 'History' } },
    { path: '/categories', component: Category, meta: { title: 'Category', activeMenu: '/categories' } },
    { path: '/history-employee', component: History_Employee, meta: { title: 'History Employee' } },
    { path: '/history-event', component: History_Event, meta: { title: 'History Event' } },
    {
        path: '/history/event/:id',
        name: 'history-event-detail', // ตั้งชื่อเพื่อให้เรียกใช้ง่ายๆ
        component: HistoryDetailsEvent,
        props: true, // เปิดรับ id เป็น props
        meta: { title: 'History Event' }
    },

    { path: '/history-category', component: HistoryCategory, meta: { title: 'History Category' } },


    { path: '/reply-form', component: reply_form, meta: { blank: true, title: 'Reply Form' } },
    { path: '/employees/upload', name: 'upload-file', component: UploadFile, meta: { title: 'Employees', activeMenu: '/employee' } },


    // [เพิ่ม] เส้นทางหน้าแก้ไขพนักงาน
    { path: '/edit-employee/:id', name: 'edit-employee', component: EditEmployee, props: true, meta: { title: 'Employee', activeMenu: '/employee' } },
    // หน้า Details – ใช้ชื่อ route 'event.details' และส่ง :id เป็น props
    { path: '/events/:id', name: 'event.details', component: EventDetails, props: true, meta: { title: 'Event', activeMenu: '/event' } },
    // เพิ่ม redirect กันพลาด (ถ้าใครไป /event/2 จะถูกส่งมาที่ /events/2)
    { path: '/event/:id', redirect: to => ({ name: 'event.details', params: { id: to.params.id } }) },


]

export default createRouter({
    history: createWebHistory(),
    routes,
})
