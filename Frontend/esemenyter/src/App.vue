<template>
  <div id="app">
    <router-view />
    <ToastContainer ref="toastContainer" />
  </div>
</template>

<script>
import ToastContainer from './components/ToastContainer.vue'
import { setToastContainer } from './services/toast'

export default {
  name: 'App',
  components: {
    ToastContainer
  },
  mounted() {
    setToastContainer(this.$refs.toastContainer)
    console.log('ToastContainer regisztrálva:', this.$refs.toastContainer) // Ellenőrzés
  }
}
</script>