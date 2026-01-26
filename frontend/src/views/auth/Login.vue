<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  termsAccepted: false
})

const errors = reactive({})
const showPassword = ref(false)
const isSubmitting = ref(false)

// Validation du formulaire de connexion
const validateForm = () => {
  // Réinitialiser les erreurs
  Object.keys(errors).forEach(key => delete errors[key])
  
  let isValid = true

  // Validation de l'email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!form.email.trim()) {
    errors.email = 'L\'email est requis'
    isValid = false
  } else if (!emailRegex.test(form.email)) {
    errors.email = 'Veuillez entrer un email valide'
    isValid = false
  }

  // Validation du mot de passe
  if (!form.password) {
    errors.password = 'Le mot de passe est requis'
    isValid = false
  } else if (form.password.length < 6) {
    errors.password = 'Le mot de passe doit contenir au moins 6 caractères'
    isValid = false
  }

  return isValid
}

// Soumission du formulaire de connexion
const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  isSubmitting.value = true

  try {
    const response = await fetch('http://localhost:8000/api/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email: form.email,
        password: form.password
      })
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.errors) {
        // Erreurs de validation
        Object.assign(errors, data.errors)
      } else {
        errors.submit = data.message || 'Erreur de connexion'
      }
      return
    }

    // Stocker le token et les informations utilisateur
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))

    console.log('Connexion réussie:', data.user)

    // Redirection vers le dashboard après connexion
    router.push('/dashboard')

  } catch (error) {
    console.error('Erreur de connexion:', error)
    errors.submit = 'Erreur de réseau. Veuillez réessayer.'
  } finally {
    isSubmitting.value = false
  }
}

// Méthodes
const clearError = (field) => {
  if (errors[field]) {
    delete errors[field]
  }
}

const isFieldValid = (field) => {
  return form[field] && !errors[field]
}

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}
</script>

<template>
  
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full" >
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">

          <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-6">
          <div class="flex items-center justify-center gap-3 mb-3">
            <div class="bg-white/20 p-2 rounded-lg">
              <span class="material-symbols-outlined text-white icon-lg">
                login
              </span>
            </div>
            <h1 class="text-white text-2xl font-bold">Se connecter</h1>
          </div>
        </div>
      <form @submit.prevent="handleSubmit" class="px-8 py-8">
           <div class="mb-5">
            <label for="email" class="block text-gray-700 text-sm font-medium mb-2 flex items-center gap-2">
              <span class="material-symbols-outlined icon-sm text-gray-500">mail</span>
              <span>Adresse email</span>
              <span v-if="errors.email" class="text-red-500 text-xs ml-auto">
                {{ errors.email }}
              </span>
            </label>
            <div class="relative">
              <input
                type="email"
                id="email"
                v-model="form.email"
                :class="[
                  'w-full px-4 py-3 pl-11 border rounded-lg focus:outline-none focus:ring-2 transition-all duration-200 form-input',
                  errors.email 
                    ? 'border-red-300 focus:ring-red-500 focus:border-red-500' 
                    : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                ]"
                placeholder="jean.dupont@example.com"
                @input="clearError('email')"
              >
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                <span class="material-symbols-outlined icon-sm">mail</span>
              </div>
              <div v-if="isFieldValid('email')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-green-500">
                <span class="material-symbols-outlined icon-sm">check_circle</span>
              </div>
            </div>
          </div>

          <!-- Mot de passe -->
          <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-medium mb-2 flex items-center gap-2">
              <span class="material-symbols-outlined icon-sm text-gray-500">lock</span>
              <span>Mot de passe</span>
              <span v-if="errors.password" class="text-red-500 text-xs ml-auto">
                {{ errors.password }}
              </span>
            </label>
            <div class="relative">
              <input
                :type="showPassword ? 'text' : 'password'"
                id="password"
                v-model="form.password"
                :class="[
                  'w-full px-4 py-3 pl-11 pr-11 border rounded-lg focus:outline-none focus:ring-2 transition-all duration-200 form-input',
                  errors.password 
                    ? 'border-red-300 focus:ring-red-500 focus:border-red-500' 
                    : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                ]"
                placeholder="Minimum 6 caractères"
                @input="clearError('password')"
              >
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                <span class="material-symbols-outlined icon-sm">lock</span>
              </div>
              <button
                type="button"
                @click="togglePasswordVisibility"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
              >
                <span class="material-symbols-outlined icon-sm">
                  {{ showPassword ? 'visibility_off' : 'visibility' }}
                </span>
              </button>
            </div>   
          </div>
            <!-- Lien mot de passe oublié -->
        <div class="mb-6 text-right">
          <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
            Mot de passe oublié ?
          </a>
        </div>

        <!-- Bouton de connexion -->
        <button
          type="submit"
          :disabled="isSubmitting"
          :class="[
            'w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-3 px-4 rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 flex items-center justify-center gap-2 btn-primary',
            isSubmitting ? 'opacity-75 cursor-not-allowed' : 'hover:from-blue-700 hover:to-indigo-800 hover:transform hover:-translate-y-0.5 cursor-pointer'
          ]"
        >
          <span v-if="isSubmitting" class="material-symbols-outlined animate-spin">refresh</span>
          <span v-else class="material-symbols-outlined icon-md">login</span>
          <span>{{ isSubmitting ? 'Connexion en cours...' : 'Se connecter' }}</span>
        </button>

        <!-- Lien vers inscription -->
        <div class="mt-6 text-center">
          <p class="text-gray-600 text-sm">
            Pas encore de compte ? 
            <router-link to="/register" class="text-blue-600 hover:text-blue-700 font-medium">
              Créer un compte
            </router-link>
          </p>
        </div>

        <!-- Erreur globale -->
        <div v-if="errors.submit" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-red-600 text-sm text-center">{{ errors.submit }}</p>
        </div>

    </form>
</div>
</div>
</div>
</template>


<style scoped>
/* Animations et styles personnalisés */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.icon-sm {
  font-size: 1.25rem;
}

.icon-md {
  font-size: 1.5rem;
}

.icon-lg {
  font-size: 2rem;
}

.form-input {
  transition: all 0.2s ease;
}

.btn-primary {
  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}
</style>