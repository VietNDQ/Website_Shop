import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './index.css'
import App from './App.vue'
import router from './router'
import Client_layout from './layout/wrapper/client_index.vue'
import Toast, { createToastInterface } from "vue-toastification"
import "vue-toastification/dist/index.css"
import vue3GoogleLogin from 'vue3-google-login'
import DOMPurify from 'dompurify'

const app = createApp(App)
const pinia = createPinia()

// Đăng ký directive v-safe-html để lọc HTML bẩn chống XSS
app.directive('safe-html', {
  mounted(el, binding) {
    el.innerHTML = DOMPurify.sanitize(binding.value || '')
  },
  updated(el, binding) {
    el.innerHTML = DOMPurify.sanitize(binding.value || '')
  }
})

app.use(pinia)
app.use(router)

const toastOptions = {
  position: "bottom-center",
  timeout: 1500,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: "button",
  icon: true,
  rtl: false
}

app.use(Toast, toastOptions)
app.config.globalProperties.$toast = createToastInterface(toastOptions)
app.component("client-layout", Client_layout)
app.component("default-layout", {
  render() {
    return this.$slots.default ? this.$slots.default() : null
  }
})
app.use(vue3GoogleLogin, {
  clientId: '1000023837690-mk7smuucpijk6hvrfhhiq1tbbl56v3ce.apps.googleusercontent.com' 
})
app.mount("#app")