<template>
    <form class="login" @submit.prevent="handleSubmit" novalidate>
        <h2 class="title">Login</h2>

        <label class="field">
            <span class="label-text">Email</span>
            <input
                type="email"
                v-model="email"
                :class="{ invalid: emailError }"
                placeholder="you@example.com"
                autocomplete="username"
                required
            />
            <small v-if="emailError" class="error">{{ emailError }}</small>
        </label>

        <label class="field">
            <span class="label-text">Password</span>
            <div class="password-row">
                <input
                    :type="showPassword ? 'text' : 'password'"
                    v-model="password"
                    :class="{ invalid: passwordError }"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                />
                <button type="button" class="toggle" @click="showPassword = !showPassword" :aria-pressed="String(showPassword)">
                    {{ showPassword ? 'Hide' : 'Show' }}
                </button>
            </div>
            <small v-if="passwordError" class="error">{{ passwordError }}</small>
        </label>

        <div class="actions">
            <button type="submit" :disabled="!isValid" class="submit">Sign in</button>
        </div>

        <p v-if="formError" class="form-error" role="alert">{{ formError }}</p>
    </form>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useEmitter } from 'vue' // placeholder if you use an event bus; not required

// Props/Emit could be added if parent should handle login
const email = ref('')
const password = ref('')
const showPassword = ref(false)
const formError = ref('')

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const emailError = computed(() => {
    if (!email.value) return 'Email is required.'
    if (!emailRegex.test(email.value)) return 'Enter a valid email address.'
    return ''
})

const passwordError = computed(() => {
    if (!password.value) return 'Password is required.'
    if (password.value.length < 6) return 'Password must be at least 6 characters.'
    return ''
})

const isValid = computed(() => !emailError.value && !passwordError.value)

const emit = defineEmits ? defineEmits(['login']) : (() => {}) // safe when using <script setup>

function handleSubmit() {
    formError.value = ''
    if (!isValid.value) {
        formError.value = 'Please fix the errors above.'
        return
    }

    // Emit login event to parent with credentials (avoid logging sensitive info)
    try {
        if (typeof emit === 'function') {
            emit('login', { email: email.value.trim(), password: password.value })
        }
        // Optionally clear password after emit
        password.value = ''
    } catch (err) {
        formError.value = 'An unexpected error occurred.'
    }
}
</script>

<style scoped>
.login {
    max-width: 360px;
    margin: 16px auto;
    padding: 18px;
    border: 1px solid #e6e6e6;
    border-radius: 8px;
    background: #fff;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
}
.title {
    margin: 0 0 12px;
    font-size: 1.25rem;
    text-align: center;
}
.field {
    display: block;
    margin-bottom: 12px;
}
.label-text {
    display: block;
    font-size: 0.9rem;
    margin-bottom: 6px;
    color: #333;
}
input[type="email"],
input[type="password"],
input[type="text"] {
    width: 100%;
    box-sizing: border-box;
    padding: 8px 10px;
    border: 1px solid #cfcfcf;
    border-radius: 6px;
    font-size: 0.95rem;
}
.password-row {
    display: flex;
    gap: 8px;
    align-items: center;
}
.toggle {
    padding: 6px 10px;
    border: 1px solid #cfcfcf;
    background: #fafafa;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85rem;
}
.toggle[aria-pressed="true"] {
    background: #eef6ff;
    border-color: #9fc7ff;
}
.actions {
    margin-top: 8px;
    text-align: center;
}
.submit {
    padding: 9px 16px;
    background: #0078d4;
    border: none;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}
.submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.error {
    color: #b00020;
    font-size: 0.8rem;
    margin-top: 6px;
    display: block;
}
.invalid {
    border-color: #b00020;
}
.form-error {
    margin-top: 10px;
    color: #b00020;
    text-align: center;
    font-size: 0.9rem;
}
</style>