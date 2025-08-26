<template>
  <div class="dashboard">
    <h2 class="text-2xl font-bold mb-4">Dashboard</h2>

    <!-- Section: Event Table -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-6">
      <h2 class="text-xl font-bold mb-4">Event Details</h2>
      <table class="table-auto w-full border-collapse border border-gray-300">
        <thead>
          <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">#</th>
            <th class="border border-gray-300 px-4 py-2">Event</th>
            <th class="border border-gray-300 px-4 py-2">Category</th>
            <th class="border border-gray-300 px-4 py-2">Date</th>
            <th class="border border-gray-300 px-4 py-2">Time</th>
            <th class="border border-gray-300 px-4 py-2">Accepted</th>
            <th class="border border-gray-300 px-4 py-2">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="border border-gray-300 px-4 py-2">1</td>
            <td class="border border-gray-300 px-4 py-2">Kick-off Meeting Q3</td>
            <td class="border border-gray-300 px-4 py-2">Meeting</td>
            <td class="border border-gray-300 px-4 py-2">15/08/2025</td>
            <td class="border border-gray-300 px-4 py-2">10:00-12:00</td>
            <td class="border border-gray-300 px-4 py-2">100</td>
            <td class="border border-gray-300 px-4 py-2 text-green-500">Done</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Section: Statistics -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-white shadow-md rounded-lg p-4">
        <h3 class="text-lg font-bold mb-2">Participation Overview</h3>
        <p class="text-4xl font-bold text-purple-500">367</p>
        <p class="text-gray-500">Person</p>
      </div>
      <div class="bg-white shadow-md rounded-lg p-4">
        <h3 class="text-lg font-bold mb-2">Attending</h3>
        <p class="text-4xl font-bold text-green-500">61</p>
        <p class="text-gray-500">Person</p>
      </div>
      <div class="bg-white shadow-md rounded-lg p-4">
        <h3 class="text-lg font-bold mb-2">Not Attending</h3>
        <p class="text-4xl font-bold text-red-500">12</p>
        <p class="text-gray-500">Person</p>
      </div>
    </div>

    <!-- Section: Employee Table -->
    <div class="bg-white shadow-md rounded-lg p-4">
      <h2 class="text-xl font-bold mb-4">Employee Details</h2>
      <table class="table-auto w-full border-collapse border border-gray-300">
        <thead>
          <tr class="bg-gray-200">
            <th class="border border-gray-300 px-4 py-2">ID</th>
            <th class="border border-gray-300 px-4 py-2">Name</th>
            <th class="border border-gray-300 px-4 py-2">Nickname</th>
            <th class="border border-gray-300 px-4 py-2">Phone</th>
            <th class="border border-gray-300 px-4 py-2">Department</th>
            <th class="border border-gray-300 px-4 py-2">Team</th>
            <th class="border border-gray-300 px-4 py-2">Position</th>
            <th class="border border-gray-300 px-4 py-2">Event</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="border border-gray-300 px-4 py-2">CM0001</td>
            <td class="border border-gray-300 px-4 py-2">กิตติ ศรีสุข</td>
            <td class="border border-gray-300 px-4 py-2">กิต</td>
            <td class="border border-gray-300 px-4 py-2">0812345678</td>
            <td class="border border-gray-300 px-4 py-2">Engineering</td>
            <td class="border border-gray-300 px-4 py-2">CodeCraft</td>
            <td class="border border-gray-300 px-4 py-2">Software Engineer</td>
            <td class="border border-gray-300 px-4 py-2">Internal UI/UX Design Workshop</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: "Dashboard",
};
</script>

<style scoped>
/* เพิ่ม CSS ที่เกี่ยวข้อง */
</style>