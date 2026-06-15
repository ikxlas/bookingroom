import { useAuth } from './useAuth'

export function useState() {
    const { getAuthHeaders, API_URL } = useAuth()

    const getRooms = async () => {
        try {
            const res = await fetch(`${API_URL}/rooms`, { headers: getAuthHeaders() })
            return res.ok ? await res.json() : []
        } catch(e) { return [] }
    }

    const saveRoom = async (room) => {
        if (room.id) {
            await fetch(`${API_URL}/rooms/${room.id}`, {
                method: 'PUT',
                headers: getAuthHeaders(),
                body: JSON.stringify(room)
            })
        } else {
            await fetch(`${API_URL}/rooms`, {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(room)
            })
        }
    }

    const deleteRoom = async (id) => {
        await fetch(`${API_URL}/rooms/${id}`, {
            method: 'DELETE',
            headers: getAuthHeaders()
        })
    }

    const getBookings = async () => {
        try {
            const res = await fetch(`${API_URL}/bookings`, { headers: getAuthHeaders() })
            return res.ok ? await res.json() : []
        } catch(e) { return [] }
    }

    const getBookingsByUser = async (username) => {
        return await getBookings() // API filters automatically based on token
    }

    const submitBooking = async (bookingData) => {
        const res = await fetch(`${API_URL}/bookings`, {
            method: 'POST',
            headers: getAuthHeaders(),
            body: JSON.stringify(bookingData)
        })
        if (!res.ok) {
            const errData = await res.json().catch(() => ({}))
            throw new Error(errData.message || 'Gagal mengajukan peminjaman.')
        }
    }

    const updateBookingStatus = async (id, newStatus, rejectionReason = null) => {
        const payload = { newStatus }
        if (rejectionReason) payload.rejection_reason = rejectionReason
        
        await fetch(`${API_URL}/bookings/${id}/status`, {
            method: 'PUT',
            headers: getAuthHeaders(),
            body: JSON.stringify(payload)
        })
    }

    const getBookedDates = async (roomId) => {
        try {
            const res = await fetch(`${API_URL}/rooms/${roomId}/booked-dates`, { headers: getAuthHeaders() })
            return res.ok ? await res.json() : []
        } catch(e) { return [] }
    }

    return {
        getRooms,
        saveRoom,
        deleteRoom,
        getBookings,
        getBookingsByUser,
        submitBooking,
        updateBookingStatus,
        getBookedDates
    }
}
