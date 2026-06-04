<template>
  <div class="bg-light min-vh-100 pb-5">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light glass-card mb-4 mt-2 mx-3 shadow-sm rounded-3 bg-white px-3">
        <div class="container-fluid">
            <span class="navbar-brand header-title fw-bold">Booking<span class="text-dark">User</span></span>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">Halo, {{ user?.username }}!</span>
                <button class="btn btn-outline-danger rounded-pill px-4" @click="handleLogout">Log Out</button>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        
        <!-- View: Daftar Ruangan -->
        <div v-if="!showForm" class="fade-in">
            <h3 class="mb-4 text-center">Cari & Pilih Ruangan</h3>
            
            <div class="row justify-content-center mb-4">
                <div class="col-md-6">
                    <input v-model="searchQuery" type="text" class="form-control form-control-lg rounded-pill px-4 shadow-sm" placeholder="🔍 Cari nama ruangan...">
                </div>
            </div>

            <div class="row g-4">
                <div v-if="availableRooms.length === 0" class="col-12">
                    <div class="alert alert-warning text-center">Tidak Ada Ruangan Tersedia saat ini.</div>
                </div>
                
                <div v-for="room in availableRooms" :key="room.id" class="col-md-4">
                    <div class="card room-card h-100 p-3 bg-white rounded-3 shadow-sm border-0 border-top border-4 border-primary">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold">{{ room.name }}</h5>
                            <p class="card-text text-muted small mb-2">Kapasitas: {{ room.capacity }} Orang</p>
                            <div class="mb-3">
                                <span class="badge" :class="room.is_in_use ? 'bg-danger' : 'bg-success'">
                                    {{ room.is_in_use ? '🔴 Dipesan: ' + room.used_date : '🟢 Kosong' }}
                                </span>
                            </div>
                            <button class="btn btn-primary-custom rounded-pill mt-auto" @click="openBookingForm(room)">Pilih Ruangan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View: Formulir Peminjaman -->
        <div v-else class="fade-in">
            <button class="btn btn-secondary mb-3 rounded-pill" @click="showForm = false">&larr; Kembali ke Daftar Ruangan</button>
            <div class="glass-card p-4 mx-auto bg-white rounded-3 shadow-sm" style="max-width: 600px;">
                <h4 class="mb-4 header-title text-center">Formulir Peminjaman</h4>
                <div class="alert alert-info py-2"><span class="fw-bold">Mengajukan Peminjaman: {{ selectedRoom.name }}</span></div>
                
                <form @submit.prevent="handleBookingSubmit">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Peminjaman</label>
                        <input v-model="bookingForm.date" type="date" class="form-control form-control-lg" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durasi (Jam)</label>
                        <input v-model="bookingForm.duration" type="number" min="1" max="24" class="form-control form-control-lg" placeholder="Contoh: 2" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keperluan</label>
                        <textarea v-model="bookingForm.purpose" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100 btn-lg rounded-pill">Ajukan Peminjaman</button>
                </form>
            </div>
        </div>

        <!-- View: Status & Riwayat -->
        <div class="fade-in mt-5">
            <hr>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Status Pengajuan & Riwayat Peminjaman</h4>
                <button v-if="history.length > 3" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold shadow-sm" @click="goToHistory">
                    Lihat Semua
                </button>
            </div>
            <div v-if="history.length === 0" class="alert alert-secondary">Belum ada pengajuan.</div>
            
            <div v-else class="table-responsive glass-card p-2 rounded bg-white shadow-sm">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal Diajukan</th>
                            <th>Ruangan</th>
                            <th>Waktu Pemakaian</th>
                            <th>Status Pengajuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in historyBookings" :key="b.id">
                            <td>{{ new Date(b.timestamp).toLocaleDateString() }}</td>
                            <td class="fw-bold">{{ b.roomName }}</td>
                            <td>{{ b.date }} ({{ b.duration || '-' }} Jam)</td>
                            <td>
                                <div class="d-flex flex-column align-items-start">
                                    <span class="badge status-badge mb-1" :class="getBadgeClass(b.status)">
                                        {{ b.status }}
                                    </span>
                                    <small v-if="b.status === 'Ditolak'" class="text-danger mt-1">
                                        <strong>Alasan:</strong> {{ b.rejection_reason || 'Tidak ada alasan.' }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div v-if="b.status === 'Ditolak'">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill w-100" @click="handleAjukanUlang(b)">
                                        Ajukan Ulang
                                    </button>
                                </div>
                                <div v-else-if="b.status === 'Disetujui'">
                                    <button class="btn btn-sm btn-success rounded-pill px-3" @click="openETicket(b)">
                                        🎟️ Lihat E-Ticket
                                    </button>
                                </div>
                                <span v-else class="text-muted small">Menunggu Konfirmasi</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- View: E-Ticket Modal / Display -->
        <div v-if="eTicket" class="modal-backdrop fade show" style="background: rgba(0,0,0,0.5);"></div>
        <div v-if="eTicket" class="modal fade show d-block" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" id="eTicketArea">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">🎟️ E-Ticket Peminjaman</h5>
                        <button type="button" class="btn-close btn-close-white" @click="eTicket = null"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <h3 class="mb-0 fw-bold">{{ eTicket.roomName }}</h3>
                        <p class="text-muted mb-4">ID Booking: #{{ eTicket.id }}</p>
                        
                        <div class="border rounded p-3 text-start bg-light mb-4">
                            <p class="mb-2"><strong>Nama Peminjam:</strong> {{ eTicket.user }}</p>
                            <p class="mb-2"><strong>Tanggal:</strong> {{ eTicket.date }}</p>
                            <p class="mb-2"><strong>Durasi:</strong> {{ eTicket.duration }} Jam</p>
                            <p class="mb-0"><strong>Keperluan:</strong> {{ eTicket.purpose }}</p>
                        </div>
                        
                        <div class="text-center">
                            <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=BOOKING_${eTicket.id}`" alt="QR Code" class="mb-3">
                            <p class="text-success fw-bold">Disetujui</p>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button class="btn btn-primary rounded-pill px-4" @click="printTicket">Cetak Tiket</button>
                        <button class="btn btn-secondary rounded-pill px-4" @click="eTicket = null">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useState } from '@/composables/useState'

const router = useRouter()
const route = useRoute()
const { user, logout } = useAuth()
const { getRooms, getBookingsByUser, submitBooking } = useState()

const rooms = ref([])
const history = ref([])
const showForm = ref(false)
const selectedRoom = ref({})
const searchQuery = ref('')
const eTicket = ref(null)

const bookingForm = ref({
    date: '',
    duration: '',
    purpose: ''
})

const availableRooms = computed(() => {
    let filtered = rooms.value.filter(r => r.status === 'available')
    if (searchQuery.value) {
        filtered = filtered.filter(r => r.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
    }
    return filtered
})
const historyBookings = computed(() => {
    return history.value.slice().reverse().slice(0, 3)
})

const goToHistory = () => {
    router.push({ name: 'UserHistory' })
}

const openETicket = (booking) => {
    eTicket.value = booking
}

const printTicket = () => {
    window.print()
}

const loadData = async () => {
    rooms.value = await getRooms()
    history.value = await getBookingsByUser(user.value.username)
}

onMounted(async () => {
    await loadData()
    if (route.query.rebook) {
        openBookingForm({ id: route.query.rebook, name: route.query.roomName })
        router.replace({ name: 'UserDashboard' }) // clear query params from url
    }
})

const handleLogout = async () => {
    await logout()
    router.push({ name: 'Login' })
}

const openBookingForm = (room) => {
    selectedRoom.value = room
    bookingForm.value = {
        date: '',
        duration: '',
        purpose: ''
    }
    showForm.value = true
}

const handleAjukanUlang = (b) => {
    const tzOffset = (new Date()).getTimezoneOffset() * 60000; 
    const today = (new Date(Date.now() - tzOffset)).toISOString().split('T')[0];

    if (b.date < today) {
        alert('Tidak bisa ajukan ulang. Tanggal peminjaman ruangan tersebut sudah lewat dari hari ini.');
        return;
    }
    openBookingForm({ id: b.roomId, name: b.roomName })
}

const handleBookingSubmit = async () => {
    const payload = {
        roomId: selectedRoom.value.id,
        ...bookingForm.value
    }
    
    try {
        await submitBooking(payload)
        alert('Pengajuan berhasil dikirim! Menunggu konfirmasi admin.')
        showForm.value = false
        await loadData()
    } catch (e) {
        alert(e.message)
    }
}

const getBadgeClass = (status) => {
    if (status === 'Disetujui') return 'bg-success'
    if (status === 'Ditolak') return 'bg-danger'
    return 'bg-warning text-dark'
}
</script>
