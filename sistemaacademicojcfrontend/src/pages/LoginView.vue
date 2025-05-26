<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { mdiAccount, mdiAsterisk, mdiEye, mdiEyeOff } from '@mdi/js'
import SectionFullScreen from '@/components/SectionFullScreen.vue'
import CardBox from '@/components/CardBox.vue'
import CardBoxModal from '@/components/CardBoxModal.vue'
import FormField from '@/components/FormField.vue'
import FormControl from '@/components/FormControl.vue'
import BaseButton from '@/components/BaseButton.vue'
import BaseButtons from '@/components/BaseButtons.vue'
import LayoutGuest from '@/layouts/LayoutGuest.vue'
import api from '@/services/api'

const modalSuccess = ref(false)
const modalError = ref(false)
const modalErrorMessage = ref('')

const form = reactive({
  login: '',
  pass: '',
  remember: true,
})

const mostrarPass = ref(false)
const intentosFallidos = ref(0)
const bloqueoActivo = ref(false)
const tiempoRestante = ref(0)
let timer = null

const router = useRouter()

const iniciarTemporizador = () => {
  const ahora = Date.now()
  const desbloqueoEn = ahora + 5 * 60 * 1000
  localStorage.setItem('bloqueo_login', JSON.stringify({ desbloqueoEn }))
  tiempoRestante.value = 300
  bloqueoActivo.value = true

  timer = setInterval(() => {
    const ahora = Date.now()
    const tiempo = Math.floor((desbloqueoEn - ahora) / 1000)
    if (tiempo <= 0) {
      limpiarBloqueo()
    } else {
      tiempoRestante.value = tiempo
    }
  }, 1000)
}

const limpiarBloqueo = () => {
  clearInterval(timer)
  bloqueoActivo.value = false
  tiempoRestante.value = 0
  intentosFallidos.value = 0
  localStorage.removeItem('bloqueo_login')
}

const verificarBloqueoPersistente = () => {
  const data = localStorage.getItem('bloqueo_login')
  if (!data) return
  const { desbloqueoEn } = JSON.parse(data)
  const ahora = Date.now()
  const tiempo = Math.floor((desbloqueoEn - ahora) / 1000)

  if (tiempo > 0) {
    tiempoRestante.value = tiempo
    bloqueoActivo.value = true
    timer = setInterval(() => {
      const ahora = Date.now()
      const restante = Math.floor((desbloqueoEn - ahora) / 1000)
      if (restante <= 0) {
        limpiarBloqueo()
      } else {
        tiempoRestante.value = restante
      }
    }, 1000)
  } else {
    limpiarBloqueo()
  }
}

onMounted(() => {
  verificarBloqueoPersistente()
})

const submit = async () => {
  if (bloqueoActivo.value) return
  try {
    const response = await api.post('/login', {
      name_user: form.login,
      password: form.pass,
    })

    const token = response.data.token
    if (!token) throw new Error('Token no recibido')

    localStorage.setItem('token', token)
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    modalSuccess.value = true
    intentosFallidos.value = 0
    localStorage.removeItem('bloqueo_login')

    setTimeout(() => {
      router.push('/dashboard')
    }, 2000)
  } catch {
    intentosFallidos.value++
    modalErrorMessage.value = 'Usuario y/o Contraseña incorrecto'
    modalError.value = true

    if (intentosFallidos.value >= 3) {
      iniciarTemporizador()
    }
  }
}
</script>

<template>
  <LayoutGuest>
    <SectionFullScreen v-slot="{ cardClass }" bg="turquoise">
      <CardBox :class="cardClass" is-form @submit.prevent="submit">
        <div class="flex flex-col md:flex-row w-full">
          <!-- 🖼 Imagen izquierda -->
          <div class="hidden md:flex w-full md:w-1/2 items-center justify-center bg-white p-4">
            <img src="/login-image.png" alt="Bienvenido" class="max-h-64 object-contain" />
          </div>

          <!-- 📋 Formulario -->
          <div class="w-full md:w-1/2 p-4">
            <p v-if="bloqueoActivo" class="text-center text-orange-500 font-semibold text-sm mb-4">
              ⚠️ Demasiados intentos. Tu acceso ha sido bloqueado por 5 minutos.
            </p>

            <!-- Usuario -->
            <FormField
              label="Usuario"
              :help="
                bloqueoActivo
                  ? `Bloqueado: espera ${tiempoRestante} seg`
                  : 'Por favor ingrese su usuario'
              "
            >
              <FormControl
                v-model="form.login"
                :icon="mdiAccount"
                name="login"
                autocomplete="username"
                :disabled="bloqueoActivo"
              />
            </FormField>

            <!-- Contraseña -->
            <FormField
              label="Contraseña"
              :help="
                bloqueoActivo
                  ? `Bloqueado: espera ${tiempoRestante} seg`
                  : 'Por favor ingrese su contraseña'
              "
            >
              <FormControl
                v-model="form.pass"
                :icon="mdiAsterisk"
                :append-icon="mostrarPass ? mdiEyeOff : mdiEye"
                :type="mostrarPass ? 'text' : 'password'"
                name="password"
                autocomplete="current-password"
                :disabled="bloqueoActivo"
                @append-icon-click="mostrarPass = !mostrarPass"
              />
            </FormField>

            <!-- Botón + progreso -->
            <BaseButtons>
              <BaseButton type="submit" color="info" label="Ingresar" :disabled="bloqueoActivo" />
              <BaseButton color="light" label="Cancelar" @click="router.push('/')" />
            </BaseButtons>

            <div v-if="intentosFallidos > 0 && !bloqueoActivo" class="mt-4 w-full text-center">
              <p class="text-red-500 text-sm mb-1">
                Intentos fallidos: {{ intentosFallidos }} de 3
              </p>
              <div class="w-full bg-gray-200 rounded h-2 overflow-hidden">
                <div
                  class="bg-red-500 h-2 transition-all duration-300"
                  :style="{ width: (intentosFallidos / 3) * 100 + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </CardBox>
    </SectionFullScreen>
  </LayoutGuest>

  <CardBoxModal v-model="modalSuccess" title="¡Bienvenido!" button="success">
    <p>Inicio de sesión exitoso</p>
  </CardBoxModal>

  <CardBoxModal v-model="modalError" title="Error de autenticación" button="danger">
    <p>{{ modalErrorMessage }}</p>
  </CardBoxModal>
</template>
