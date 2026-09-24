import { computed, reactive } from 'vue';
import api from '../api';

const state = reactive({
    user: JSON.parse(localStorage.getItem('sharphand.user') || 'null'),
    token: localStorage.getItem('sharphand.token'),
});

function persist(user, token) {
    state.user = user;
    state.token = token || user?.token || state.token;

    if (state.user) {
        localStorage.setItem('sharphand.user', JSON.stringify(state.user));
    } else {
        localStorage.removeItem('sharphand.user');
    }

    if (state.token) {
        localStorage.setItem('sharphand.token', state.token);
    } else {
        localStorage.removeItem('sharphand.token');
    }
}

export function useAuth() {
    const isAuthenticated = computed(() => Boolean(state.user && state.token));
    const isAdmin = computed(() => state.user?.role === 'admin');
    const isWorker = computed(() => state.user?.role === 'worker');
    const initials = computed(() => {
        const parts = (state.user?.name || 'U').split(/\s+/);
        return `${parts[0]?.[0] || 'U'}${parts[1]?.[0] || ''}`.toUpperCase();
    });

    function setSession(payload) {
        persist(payload, payload.token);
    }

    async function logout() {
        try {
            await api.post('/logout');
        } catch {
            // The local session should clear even if the token is already invalid.
        }

        persist(null, null);
    }

    return {
        state,
        isAuthenticated,
        isAdmin,
        isWorker,
        initials,
        setSession,
        logout,
    };
}
