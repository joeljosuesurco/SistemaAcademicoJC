<script setup>
import { ref, reactive } from 'vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import BaseButton from '@/components/BaseButton.vue'
import api from '@/services/api'
import CardBoxModal from '@/components/CardBoxModal.vue'

const modalConfirmacion = ref(false)
const datosUsuarioGenerado = ref({ usuario: '', password: 'admin123' })

const previewUrl = ref(null)

const nuevoPadre = reactive({
  persona: {
    nombres_persona: '',
    apellidos_pat: '',
    apellidos_mat: '',
    sexo_persona: '',
    fecha_nacimiento: '',
    direccion_persona: '',
    nacionalidad_persona: '',
    celular_persona: '',
    fotografia_persona: null, // ahora es File
  },
  documento: {
    carnet_identidad: '',
    certificado_nacimiento: '',
  },
  padre: {
    estado_padre: 'activo',
    profesion_padre: '',
  },
})

const mensaje = ref('')
const error = ref('')

const registrarPadre = async () => {
  try {
    const formData = new FormData()

    for (const [key, value] of Object.entries(nuevoPadre.persona)) {
      if (value !== null && value !== undefined) {
        formData.append(`persona[${key}]`, value)
      }
    }

    for (const [key, value] of Object.entries(nuevoPadre.documento)) {
      formData.append(`documento[${key}]`, value)
    }

    for (const [key, value] of Object.entries(nuevoPadre.padre)) {
      formData.append(`padre[${key}]`, value)
    }

    const res = await api.post('/registro-padre', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    mensaje.value = res.data.message
    error.value = ''

    // ✅ Capturar usuario y mostrar modal
    datosUsuarioGenerado.value.usuario = res.data.usuario_generado || 'usuario'
    modalConfirmacion.value = true

    resetForm()
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Error al registrar padre'
    mensaje.value = ''
  }
}

const resetForm = () => {
  Object.assign(nuevoPadre.persona, {
    nombres_persona: '',
    apellidos_pat: '',
    apellidos_mat: '',
    sexo_persona: '',
    fecha_nacimiento: '',
    direccion_persona: '',
    nacionalidad_persona: '',
    celular_persona: '',
    fotografia_persona: null,
  })
  Object.assign(nuevoPadre.documento, {
    carnet_identidad: '',
    certificado_nacimiento: '',
  })
  Object.assign(nuevoPadre.padre, {
    estado_padre: 'activo',
    profesion_padre: '',
  })
  previewUrl.value = null
}

const onFotoChange = (event) => {
  const file = event.target.files[0]
  if (file && file.type.startsWith('image/')) {
    nuevoPadre.persona.fotografia_persona = file
    previewUrl.value = URL.createObjectURL(file)
  } else {
    nuevoPadre.persona.fotografia_persona = null
    previewUrl.value = null
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-6">Registrar Nuevo Padre de Familia</h1>

      <form @submit.prevent="registrarPadre" class="space-y-6">
        <!-- Datos personales -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Datos Personales</h2>
          <div class="grid md:grid-cols-2 gap-4">
            <input
              v-model="nuevoPadre.persona.nombres_persona"
              type="text"
              placeholder="Nombres"
              class="w-full px-4 py-2 border rounded"
              required
            />
            <input
              v-model="nuevoPadre.persona.apellidos_pat"
              type="text"
              placeholder="Apellido Paterno"
              class="w-full px-4 py-2 border rounded"
              required
            />
            <input
              v-model="nuevoPadre.persona.apellidos_mat"
              type="text"
              placeholder="Apellido Materno"
              class="w-full px-4 py-2 border rounded"
              required
            />
            <input
              v-model="nuevoPadre.persona.sexo_persona"
              type="text"
              placeholder="Sexo"
              class="w-full px-4 py-2 border rounded"
              required
            />
            <input
              v-model="nuevoPadre.persona.fecha_nacimiento"
              type="date"
              class="w-full px-4 py-2 border rounded"
              required
            />
            <input
              v-model="nuevoPadre.persona.direccion_persona"
              type="text"
              placeholder="Dirección"
              class="w-full px-4 py-2 border rounded"
              required
            />
            <input
              v-model="nuevoPadre.persona.nacionalidad_persona"
              type="text"
              placeholder="Nacionalidad"
              class="w-full px-4 py-2 border rounded"
              required
            />
            <input
              v-model="nuevoPadre.persona.celular_persona"
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
        </div>

        <!-- Documentos -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Documentos</h2>
          <div class="grid md:grid-cols-2 gap-4">
            <input
              v-model="nuevoPadre.documento.carnet_identidad"
              type="text"
              placeholder="Carnet de Identidad"
              class="w-full px-4 py-2 border rounded"
              required
            />
            <input
              v-model="nuevoPadre.documento.certificado_nacimiento"
              type="text"
              placeholder="Certificado de Nacimiento"
              class="w-full px-4 py-2 border rounded"
            />
          </div>
        </div>

        <!-- Datos Padre -->
        <div class="bg-white p-6 rounded shadow-md">
          <h2 class="text-xl font-semibold mb-4">Datos de Padre</h2>
          <div class="grid md:grid-cols-2 gap-4">
            <select
              v-model="nuevoPadre.padre.estado_padre"
              class="w-full px-4 py-2 border rounded"
              required
            >
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
            <input
              v-model="nuevoPadre.padre.profesion_padre"
              type="text"
              placeholder="Profesión"
              class="w-full px-4 py-2 border rounded"
            />
          </div>
        </div>

        <div class="flex gap-4">
          <BaseButton type="submit" label="Registrar" color="success" />
          <BaseButton type="button" label="Cancelar" color="secondary" @click="resetForm" />
        </div>

        <div v-if="mensaje" class="text-green-600">{{ mensaje }}</div>
        <div v-if="error" class="text-red-600">{{ error }}</div>
      </form>
    </div>
    <CardBoxModal
      v-model="modalConfirmacion"
      title="PADRE REGISTRADO EXITOSAMENTE"
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
