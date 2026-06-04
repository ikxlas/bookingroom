<template>
  <div class="bg-light min-vh-100 pb-5">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 px-3 shadow-sm">
        <div class="container-fluid">
            <span class="navbar-brand text-white fw-bold">Booking<span class="text-info">Admin</span></span>
            <button class="btn btn-outline-light rounded-pill px-4" @click="handleLogout">Log Out</button>
        </div>
    </nav>

    <div class="container pb-5">
        
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="header-title">Admin Dashboard</h3>
                <p class="text-muted">Kelola Ruangan dan Persetujuan Peminjaman</p>
                <div id="notificationArea">
                    <div v-if="notification" class="alert alert-success alert-dismissible fade show pb-2 pt-2" role="alert">
                        <i class="me-2">ℹ️</i> {{ notification }}
                        <button type="button" class="btn-close py-2" @click="notification = ''" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-pills mb-4" id="adminTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 me-2" :class="{active: activeTab === 'konfirmasi'}" @click="activeTab = 'konfirmasi'">Konfirmasi Pengajuan</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 me-2" :class="{active: activeTab === 'crud'}" @click="activeTab = 'crud'">CRUD Ruangan</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" :class="{active: activeTab === 'riwayat'}" @click="activeTab = 'riwayat'">Riwayat Keseluruhan</button>
            </li>
        </ul>

        <div class="tab-content" id="nav-tabContent">
            
            <!-- TAB: Konfirmasi Peminjaman -->
            <div v-if="activeTab === 'konfirmasi'" class="tab-pane fade show active">
                <div class="glass-card p-4">
                    <h5 class="mb-3 border-bottom pb-2">Menunggu Persetujuan</h5>
                    <div>
                        <div v-if="pendingBookings.length === 0" class="alert alert-secondary text-center">
                            Tidak ada pengajuan peminjaman menunggu konfirmasi.
                        </div>
                        <div v-for="b in pendingBookings" :key="b.id" class="border p-3 mb-3 rounded-3 shadow-sm bg-white fade-in">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="text-primary">{{ b.user }}</strong>
                                <small class="text-muted">Mengajukan: {{ new Date(b.timestamp).toLocaleString() }}</small>
                            </div>
                            <p class="mb-1"><strong>Ruangan:</strong> {{ b.roomName }}</p>
                            <p class="mb-1"><strong>Tanggal Pakai:</strong> {{ b.date }} ({{ b.duration || '-' }} Jam)</p>
                            <p class="mb-3"><strong>Keperluan:</strong> {{ b.purpose }}</p>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-success btn-sm px-4 rounded-pill flex-fill" @click="processBooking(b.id, 'Disetujui', b.user)">Setujui</button>
                                <button class="btn btn-danger btn-sm px-4 rounded-pill flex-fill" @click="processBooking(b.id, 'Ditolak', b.user)">Tolak</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: CRUD Ruangan -->
            <div v-if="activeTab === 'crud'" class="tab-pane fade show active">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="glass-card p-4">
                            <h5>{{ isEditing ? 'Edit Ruangan' : 'Tambah Ruangan' }}</h5>
                            <form @submit.prevent="submitRoomForm">
                                <div class="mb-2">
                                    <label class="form-label small">Nama Ruangan</label>
                                    <input v-model="roomForm.name" type="text" class="form-control" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Kapasitas</label>
                                    <input v-model="roomForm.capacity" type="number" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Status</label>
                                    <select v-model="roomForm.status" class="form-select">
                                        <option value="available">Tersedia</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary-custom w-100 rounded-pill">Simpan Data</button>
                                <button v-if="isEditing" type="button" class="btn btn-light w-100 mt-2" @click="cancelEdit">Batal Edit</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="glass-card p-4 table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Nama Ruangan</th>
                                        <th>Kapasitas</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="r in rooms" :key="r.id">
                                        <td class="fw-bold">{{ r.name }}</td>
                                        <td>{{ r.capacity }} Orang</td>
                                        <td>
                                            <span class="badge" :class="r.status === 'available' ? 'bg-success' : 'bg-secondary'">
                                                {{ r.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary rounded-pill me-1" @click="editRoom(r)">Edit</button>
                                            <button class="btn btn-sm btn-outline-danger rounded-pill" @click="removeRoom(r.id)">Hapus</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: Riwayat -->
            <div v-if="activeTab === 'riwayat'" class="tab-pane fade show active">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0">Laporan Riwayat Keseluruhan</h5>
                    <button class="btn btn-outline-primary rounded-pill px-4" @click="printReport">
                        <i class="me-1">🖨️</i> Cetak Laporan
                    </button>
                </div>
                <div class="glass-card p-4 table-responsive" id="reportArea">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal Transaksi</th>
                                <th>User</th>
                                <th>Ruangan</th>
                                <th>Waktu Pemakaian</th>
                                <th>Status Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="b in finishedBookings" :key="b.id">
                                <td>{{ new Date(b.timestamp).toLocaleDateString() }}</td>
                                <td>{{ b.user }}</td>
                                <td class="fw-bold">{{ b.roomName }}</td>
                                <td>{{ b.date }} ({{ b.duration || '-' }} Jam)</td>
                                <td>
                                    <span class="badge" :class="b.status === 'Disetujui' ? 'bg-success' : 'bg-danger'">
                                        {{ b.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> <!-- end tab-content -->
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useState } from '@/composables/useState'

const router = useRouter()
const { logout } = useAuth()
const { getRooms, saveRoom, deleteRoom, getBookings, updateBookingStatus } = useState()

const activeTab = ref('konfirmasi')
const notification = ref('')
const rooms = ref([])
const bookings = ref([])

const pendingBookings = computed(() => bookings.value.filter(b => b.status === 'Menunggu Konfirmasi'))
const finishedBookings = computed(() => {
    return bookings.value.filter(b => b.status !== 'Menunggu Konfirmasi').slice().reverse()
})

const roomForm = ref({
    id: null,
    name: '',
    capacity: '',
    status: 'available',
    target: 'Umum'
})

const isEditing = computed(() => roomForm.value.id !== null)

const loadData = async () => {
    rooms.value = await getRooms()
    bookings.value = await getBookings()
}

onMounted(() => {
    loadData()
})

const handleLogout = async () => {
    await logout()
    router.push({ name: 'Login' })
}

const submitRoomForm = async () => {
    await saveRoom(roomForm.value)
    cancelEdit()
    await loadData()
}

const editRoom = (room) => {
    roomForm.value = { ...room, target: 'Umum' }
}

const cancelEdit = () => {
    roomForm.value = { id: null, name: '', capacity: '', status: 'available', target: 'Umum' }
}

const removeRoom = async (id) => {
    if(confirm('Hapus ruangan ini?')) {
        await deleteRoom(id)
        await loadData()
    }
}

const processBooking = async (id, status, user) => {
    let rejectionReason = null
    
    // Alur berdasarkan flowchart: Keputusan Admin (Tolak -> Input Alasan)
    if (status === 'Ditolak') {
        rejectionReason = prompt(`Masukkan alasan penolakan untuk pengajuan dari ${user}:`)
        if (rejectionReason === null || rejectionReason.trim() === '') {
            return // Batalkan proses jika alasan tidak diisi
        }
    } else {
        if(!confirm(`Yakin ingin menyetujui pengajuan ini dari ${user}?`)) return
    }

    await updateBookingStatus(id, status, rejectionReason)
    
    notification.value = `Notifikasi dikirim ke ${user}: Pengajuan telah ${status}.`
    setTimeout(() => notification.value = '', 5000)
    
    await loadData()
}

const printReport = () => {
    window.print()
}
</script>

<style scoped>
@media print {
    /* Hanya cetak area laporan, sembunyikan sisanya */
    nav, .nav-pills, .row.mb-5, button { display: none !important; }
    #reportArea { box-shadow: none !important; border: none !important; }
    .bg-light { background: white !important; }
}
</style>
