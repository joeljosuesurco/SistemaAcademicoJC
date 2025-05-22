<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { mdiAccount, mdiAsterisk } from '@mdi/js'
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

const router = useRouter()

const submit = async () => {
  try {
    const response = await api.post('/login', {
      name_user: form.login,
      password: form.pass,
    })

    const token = response.data.token

    if (!token) {
      modalErrorMessage.value = 'Token no recibido del servidor.'
      modalError.value = true
      return
    }

    localStorage.setItem('token', token)
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    modalSuccess.value = true

    setTimeout(() => {
      router.push('/dashboard')
    }, 2000)
  } catch {
    modalErrorMessage.value = 'Usuario y/o Contraseña incorrecto'
    modalError.value = true
  }
}
</script>

<template>
  <LayoutGuest>
    <SectionFullScreen v-slot="{ cardClass }" bg="purplePink">
      <CardBox :class="cardClass" is-form @submit.prevent="submit">
        <FormField label="Usuario" help="Por favor ingrese su usuario">
          <FormControl
            v-model="form.login"
            :icon="mdiAccount"
            name="login"
            autocomplete="username"
          />
        </FormField>

        <FormField label="Contraseña" help="Porfavor ingrese su contraseña">
          <FormControl
            v-model="form.pass"
            :icon="mdiAsterisk"
            type="password"
            name="password"
            autocomplete="current-password"
          />
        </FormField>

        <template #footer>
          <BaseButtons>
            <BaseButton type="submit" color="info" label="Ingresar" />
          </BaseButtons>
        </template>
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
