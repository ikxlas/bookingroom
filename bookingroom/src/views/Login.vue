<template>
  <div class="login-body">
    <div class="split-layout">
        <!-- Panel Gambar / Branding -->
        <div class="split-left">
            <div class="left-content">
                <h1 class="brand-huge">Booking<span class="text-primary-accent">Ruangan</span></h1>
                <p class="brand-tagline mt-2">Ruang kolaborasi tak terbatas untuk ide-ide brilian Anda. Pesan sekarang hanya dengan beberapa klik.</p>
            </div>
        </div>

        <!-- Panel Form Login -->
        <div class="split-right">
            <div class="login-container fade-in">
                <div class="mb-5">
                    <h2 class="auth-title">Selamat Datang</h2>
                    <p class="text-muted mt-2">Masuk ke akun Anda untuk mulai memesan ruangan</p>
                </div>
                
                <form @submit.prevent="handleLogin">
                    <div v-if="errorMsg" class="alert custom-alert-danger py-3" role="alert">
                        {{ errorMsg }}
                    </div>
                    
                    <div class="form-floating mb-4 form-floating-custom">
                        <input v-model="username" type="text" class="form-control" id="username" required placeholder="User / Admin">
                        <label for="username">Username</label>
                    </div>
                    
                    <div class="form-floating mb-5 form-floating-custom">
                        <input v-model="password" type="password" class="form-control" id="password" required placeholder="*****">
                        <label for="password">Password</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom w-100 rounded-pill shadow-sm" :disabled="isLoading">
                        <span v-if="isLoading">Loading...</span>
                        <span v-else>Masuk ke Dashboard</span>
                    </button>
                    
                    <div class="text-center mt-5 small text-muted">
                        <p class="mb-1">Masuk sebagai <strong class="text-dark">admin / admin</strong> untuk panel Admin.</p>
                        <p>Atau ketik nama bebas untuk masuk sebagai Pengguna biasa.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const username = ref('')
const password = ref('')
const errorMsg = ref('')
const isLoading = ref(false)

const router = useRouter()
const { login } = useAuth()

const handleLogin = async () => {
    isLoading.value = true
    errorMsg.value = ''
    
    const result = await login(username.value, password.value)
    
    if (result.success) {
        if (result.role === 'admin') {
            router.push({ name: 'AdminDashboard' })
        } else {
            router.push({ name: 'UserDashboard' })
        }
    } else {
        errorMsg.value = result.message || 'Login Tidak Berhasil! Periksa username/password Anda.'
    }
    
    isLoading.value = false
}
</script>
