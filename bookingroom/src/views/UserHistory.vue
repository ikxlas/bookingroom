<template>
  <div class="bg-light min-vh-100 pb-5">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light glass-card mb-4 mt-2 mx-3 shadow-sm rounded-3 bg-white px-3">
        <div class="container-fluid">
            <span class="navbar-brand header-title fw-bold">Booking<span class="text-dark">User</span></span>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">Riwayat Keseluruhan</span>
                <button class="btn btn-outline-secondary rounded-pill px-4" @click="goBack">Kembali</button>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="fade-in mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Semua Riwayat Peminjaman</h4>
            </div>

            <div v-if="history.length === 0" class="alert alert-secondary">Belum ada pengajuan peminjaman.</div>
            
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
                                    <button class="btn btn-sm btn-outline-primary rounded-pill w-100" @click="goToForm(b)">
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
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useState } from '@/composables/useState'

const router = useRouter()
const { user } = useAuth()
const { getBookingsByUser } = useState()

const history = ref([])
const eTicket = ref(null)

const historyBookings = computed(() => history.value.slice().reverse())

onMounted(async () => {
    history.value = await getBookingsByUser(user.value.username)
})

const goBack = () => {
    router.push({ name: 'UserDashboard' })
}

const goToForm = (b) => {
    const tzOffset = (new Date()).getTimezoneOffset() * 60000; 
    const today = (new Date(Date.now() - tzOffset)).toISOString().split('T')[0];

    if (b.date < today) {
        alert('Tidak bisa ajukan ulang. Tanggal peminjaman ruangan tersebut sudah lewat dari hari ini.');
        return;
    }
    router.push({ name: 'UserDashboard', query: { rebook: b.roomId, roomName: b.roomName } })
}

const openETicket = (booking) => { eTicket.value = booking }
const printTicket = () => { window.print() }

const getBadgeClass = (status) => {
    if (status === 'Disetujui') return 'bg-success'
    if (status === 'Ditolak') return 'bg-danger'
    return 'bg-warning text-dark'
}
</script>
