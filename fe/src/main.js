import { createApp } from 'vue'
import './index.css'
import App from './App.vue'
import router from './router'
import Client_layout from './layout/wrapper/client_index.vue'
import Toast, { createToastInterface } from "vue-toastification"
import "vue-toastification/dist/index.css"

const app = createApp(App)

app.use(router)

const toastOptions = {
  position: "top-right",
  timeout: 3000,
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

app.mount("#app")