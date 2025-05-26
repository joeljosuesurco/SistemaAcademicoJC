<script setup>
import { ref } from 'vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import api from '@/services/api'
import BaseButton from '@/components/BaseButton.vue'
import CardBoxModal from '@/components/CardBoxModal.vue'

const modalConfirmacion = ref(false)
const datosUsuarioGenerado = ref({ usuario: '', password: 'admin123' })

const nuevoProfesor = ref({
  persona: {
    nombres_persona: '',
    apellidos_pat: '',
    apellidos_mat: '',
    sexo_persona: '',
    fecha_nacimiento: '',
    direccion_persona: '',
    nacionalidad_persona: '',
    celular_persona: '',
    fotografia_persona: null, // archivo tipo File
  },
  documento: {
    carnet_identidad: '',
    certificado_nacimiento: '',
  },
  profesor: {
    especialidad_profesor: '',
    estado_profesor: '',
    titulo_provision_nacional: '',
    rda: '',
    cas: '',
  },
})

const previewUrl = ref(null)
const mensaje = ref('')
const error = ref('')

const registrarNuevoProfesor = async () => {
  try {
    const formData = new FormData()

    // Persona
    for (const [key, value] of Object.entries(nuevoProfesor.value.persona)) {
      if (value !== null && value !== undefined) {
        formData.append(`persona[${key}]`, value)
      }
    }

    // Documento
    for (const [key, value] of Object.entries(nuevoProfesor.value.documento)) {
      formData.append(`documento[${key}]`, value)
    }

    // Profesor
    for (const [key, value] of Object.entries(nuevoProfesor.value.profesor)) {
      formData.append(`profesor[${key}]`, value)
    }

    const res = await api.post('/registro-profesor', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    mensaje.value = res.data.message
    error.value = ''

    // Capturar usuario generado y mostrar modal
    datosUsuarioGenerado.value.usuario = res.data.usuario_generado || 'usuario'
    modalConfirmacion.value = true

    resetNuevoProfesor()
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Error al registrar nuevo profesor'
    mensaje.value = ''
  }
}

const resetNuevoProfesor = () => {
  nuevoProfesor.value = {
    persona: {
      nombres_persona: '',
      apellidos_pat: '',
      apellidos_mat: '',
      sexo_persona: '',
      fecha_nacimiento: '',
      direccion_persona: '',
      nacionalidad_persona: '',
      celular_persona: '',
      fotografia_persona: null,
    },
    documento: {
      carnet_identidad: '',
      certificado_nacimiento: '',
    },
    profesor: {
      especialidad_profesor: '',
      estado_profesor: '',
      titulo_provision_nacional: '',
      rda: '',
      cas: '',
    },
  }
  previewUrl.value = null
}

const onFotoChange = (event) => {
  const file = event.target.files[0]
  if (file && file.type.startsWith('image/')) {
    nuevoProfesor.value.persona.fotografia_persona = file
    previewUrl.value = URL.createObjectURL(file)
  } else {
    nuevoProfesor.value.persona.fotografia_persona = null
    previewUrl.value = null
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Registrar Nuevo Profesor</h1>

      <form @submit.prevent="registrarNuevoProfesor" class="space-y-4 bg-white p-6 rounded shadow">
        <!-- Persona -->
        <div class="grid md:grid-cols-2 gap-4">
          <input
            v-model="nuevoProfesor.persona.nombres_persona"
            type="text"
            placeholder="Nombres"
            class="w-full px-4 py-2 border rounded"
            required
          />
          <input
            v-model="nuevoProfesor.persona.apellidos_pat"
            type="text"
            placeholder="Apellido paterno"
            class="w-full px-4 py-2 border rounded"
            required
          />
          <input
            v-model="nuevoProfesor.persona.apellidos_mat"
            type="text"
            placeholder="Apellido materno"
            class="w-full px-4 py-2 border rounded"
            required
          />
          <select
            v-model="nuevoProfesor.persona.sexo_persona"
            class="w-full px-4 py-2 border rounded"
            required
          >
            <option value="">Seleccionar sexo</option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
          </select>
          <input
            v-model="nuevoProfesor.persona.fecha_nacimiento"
            type="date"
            class="w-full px-4 py-2 border rounded"
            required
          />
          <input
            v-model="nuevoProfesor.persona.direccion_persona"
            type="text"
            placeholder="Dirección"
            class="w-full px-4 py-2 border rounded"
            required
          />
          <input
            v-model="nuevoProfesor.persona.nacionalidad_persona"
            type="text"
            placeholder="Nacionalidad"
            class="w-full px-4 py-2 border rounded"
            required
          />
          <input
            v-model="nuevoProfesor.persona.celular_persona"
            type="text"
            placeholder="Celular"
            class="w-full px-4 py-2 border rounded"
          />
          <input
            type="file"
            accept="image/*"
            @change="onFotoChange"
            class="w-full px-4 py-2 border rounded"
          />
          <div v-if="previewUrl" class="md:col-span-2 flex justify-center mt-2">
            <img
              :src="previewUrl"
              alt="Previsualización"
              class="w-32 h-32 object-cover rounded-full border shadow"
            />
          </div>
        </div>

        <!-- Documento -->
        <div class="grid md:grid-cols-2 gap-4">
          <input
            v-model="nuevoProfesor.documento.carnet_identidad"
            type="text"
            placeholder="Carnet de Identidad"
            class="w-full px-4 py-2 border rounded"
            required
          />
          <input
            v-model="nuevoProfesor.documento.certificado_nacimiento"
            type="text"
            placeholder="Certificado de Nacimiento"
            class="w-full px-4 py-2 border rounded"
            required
          />
        </div>

        <!-- Profesor -->
        <div class="grid md:grid-cols-2 gap-4">
          <input
            v-model="nuevoProfesor.profesor.especialidad_profesor"
            type="text"
            placeholder="Especialidad"
            class="w-full px-4 py-2 border rounded"
            required
          />
          <input
            v-model="nuevoProfesor.profesor.estado_profesor"
            type="text"
            placeholder="Estado (ej. activo)"
            class="w-full px-4 py-2 border rounded"
            required
          />
          <input
            v-model="nuevoProfesor.profesor.titulo_provision_nacional"
            type="text"
            placeholder="Título de Provisión Nacional"
            class="w-full px-4 py-2 border rounded"
          />
          <input
            v-model="nuevoProfesor.profesor.rda"
            type="text"
            placeholder="RDA"
            class="w-full px-4 py-2 border rounded"
          />
          <input
            v-model="nuevoProfesor.profesor.cas"
            type="text"
            placeholder="CAS"
            class="w-full px-4 py-2 border rounded"
          />
        </div>

        <div class="flex gap-4">
          <BaseButton type="submit" label="Registrar" color="success" />
          <BaseButton type="button" label="Limpiar" color="secondary" @click="resetNuevoProfesor" />
        </div>

        <div v-if="mensaje" class="text-green-600">{{ mensaje }}</div>
        <div v-if="error" class="text-red-600">{{ error }}</div>
      </form>
    </div>
    <CardBoxModal
      v-model="modalConfirmacion"
      title="PROFESOR REGISTRADO EXITOSAMENTE"
      :hide-footer="true"
    >
      <p class="mb-2">
        Usuario generado: <strong>{{ datosUsuarioGenerado.usuario }}</strong>
      </p>
      <p>
        Contraseña predeterminada: <strong>{{ datosUsuarioGenerado.password }}</strong>
      </p>
      <div class="mt-4 flex justify-end">
        <BaseButton color="primary" label="Aceptar" @click="modalConfirmacion = false" />
      </div>
    </CardBoxModal>
  </LayoutAuthenticated>
</template>
