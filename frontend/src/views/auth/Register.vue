<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
      <!-- Carte principale -->
      <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- En-tête avec gradient -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-6">
          <div class="flex items-center justify-center gap-3 mb-3">
            <div class="bg-white/20 p-2 rounded-lg">
              <span class="material-symbols-outlined text-white icon-lg">
                person_add
              </span>
            </div>
            <h1 class="text-white text-2xl font-bold">Créer un compte</h1>
          </div>
        </div>

        <!-- Formulaire -->
        <form @submit.prevent="handleSubmit" class="px-8 py-8">
          <!-- Nom complet -->
          <div class="mb-5">
            <label for="name" class="block text-gray-700 text-sm font-medium mb-2 flex items-center gap-2">
              <span class="material-symbols-outlined icon-sm text-gray-500">person</span>
              <span>Nom complet</span>
              <span v-if="errors.name" class="text-red-500 text-xs ml-auto">
                {{ errors.name }}
              </span>
            </label>
            <div class="relative">
              <input
                type="text"
                id="name"
                v-model="form.name"
                :class="[
                  'w-full px-4 py-3 pl-11 border rounded-lg focus:outline-none focus:ring-2 transition-all duration-200 form-input',
                  errors.name 
                    ? 'border-red-300 focus:ring-red-500 focus:border-red-500' 
                    : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                ]"
                placeholder="votre nom"
                @input="clearError('name')"
              >
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                <span class="material-symbols-outlined icon-sm">person</span>
              </div>
              <div v-if="isFieldValid('name')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-green-500">
                <span class="material-symbols-outlined icon-sm">check_circle</span>
              </div>
            </div>
          </div>

          <!-- Email -->
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
                placeholder="votre email"
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
                placeholder="votre mot de passe"
                @input="clearError('password'); validatePasswordStrength()"
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

          <!-- Confirmation mot de passe -->
          <div class="mb-6">
            <label for="confirmPassword" class="block text-gray-700 text-sm font-medium mb-2 flex items-center gap-2">
              <span class="material-symbols-outlined icon-sm text-gray-500">lock</span>
              <span>Confirmer le mot de passe</span>
              <span v-if="errors.confirmPassword" class="text-red-500 text-xs ml-auto">
                {{ errors.confirmPassword }}
              </span>
            </label>
            <div class="relative">
              <input
                :type="showConfirmPassword ? 'text' : 'password'"
                id="confirmPassword"
                v-model="form.confirmPassword"
                :class="[
                  'w-full px-4 py-3 pl-11 border rounded-lg focus:outline-none focus:ring-2 transition-all duration-200 form-input',
                  errors.confirmPassword 
                    ? 'border-red-300 focus:ring-red-500 focus:border-red-500' 
                    : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500'
                ]"
                placeholder="confirmez votre mot de passe"
                @input="clearError('confirmPassword')"
              >
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                <span class="material-symbols-outlined icon-sm">lock</span>
              </div>
              <button
                type="button"
                @click="toggleConfirmPasswordVisibility"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
              >
                <span class="material-symbols-outlined icon-sm">
                  {{ showConfirmPassword ? 'visibility_off' : 'visibility' }}
                </span>
              </button>
            </div>
          </div>

          <!-- Conditions -->
          <div class="mb-6">
            <label class="flex items-start gap-3 cursor-pointer">
              <input
                type="checkbox"
                v-model="form.termsAccepted"
                class="mt-1 w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-gray-300"
                @change="clearError('termsAccepted')"
              >
              <span class="text-sm text-gray-600 flex items-start gap-2">
                <span>
                  J'accepte les 
                  <a href="#" class="text-blue-600 hover:underline font-medium">conditions d'utilisation</a> 
                  et la 
                  <a href="#" class="text-blue-600 hover:underline font-medium">politique de confidentialité</a>
                </span>
              </span>
            </label>
            <p v-if="errors.termsAccepted" class="text-red-500 text-xs mt-2 flex items-center gap-1">
              <span class="material-symbols-outlined icon-xs">error</span>
              {{ errors.termsAccepted }}
            </p>
          </div>

          <!-- Bouton d'inscription -->
          <button
            type="submit"
            :disabled="isSubmitting"
            :class="[
              'w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-3 px-4 rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 flex items-center justify-center gap-2 btn-primary',
              isSubmitting ? 'opacity-75 cursor-not-allowed' : 'hover:from-blue-700 hover:to-indigo-800 hover:transform hover:-translate-y-0.5 cursor-pointer'
            ]"
          >
            <span v-if="isSubmitting" class="material-symbols-outlined animate-spin">refresh</span>
            <span v-else class="material-symbols-outlined icon-md">person_add</span>
            <span>{{ isSubmitting ? 'Inscription en cours...' : 'S\'inscrire' }}</span>
          </button>

          <!-- Séparateur -->
          <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-4 bg-white text-gray-500">Ou continuer avec</span>
            </div>
          </div>

          <!-- Connexion réseaux sociaux -->
          <div class="grid grid-cols gap-3 mb-6">
            <button
              type="button"
              @click="loginWithGoogle"
              class="flex items-center justify-center gap-2 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <span class="material-symbols-outlined text-red-500">mail</span>
              <span class="text-sm font-medium">Google</span>
            </button>
           
          </div>

          <!-- Lien connexion -->
          <div class="text-center">
            <p class="text-sm text-gray-600">
              <span class="material-symbols-outlined icon-sm text-gray-400 mr-1 align-middle">login</span>
              Vous avez déjà un compte ?
              <router-link to="/login" class="text-blue-600 hover:underline font-medium">
                Se connecter
              </router-link>
            </p>
          </div>
        </form>
      </div>

      <!-- Informations supplémentaires -->
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// États réactifs
const form = reactive({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  termsAccepted: false
})

const errors = reactive({})
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const isSubmitting = ref(false)
const passwordStrength = ref(0)

// Validation du formulaire
const validateForm = () => {
  // Réinitialiser les erreurs
  Object.keys(errors).forEach(key => delete errors[key])
  
  let isValid = true

  // Validation du nom
  if (!form.name.trim()) {
    errors.name = 'Le nom est requis'
    isValid = false
  } else if (form.name.length < 2) {
    errors.name = 'Le nom doit contenir au moins 2 caractères'
    isValid = false
  }

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
  } else if (form.password.length < 8) {
    errors.password = 'Le mot de passe doit contenir au moins 8 caractères'
    isValid = false
  }

  // Validation de la confirmation
  if (!form.confirmPassword) {
    errors.confirmPassword = 'Veuillez confirmer votre mot de passe'
    isValid = false
  } else if (form.password !== form.confirmPassword) {
    errors.confirmPassword = 'Les mots de passe ne correspondent pas'
    isValid = false
  }

  // Validation des conditions
  if (!form.termsAccepted) {
    errors.termsAccepted = 'Vous devez accepter les conditions d\'utilisation'
    isValid = false
  }

  return isValid
}

// Calcul de la force du mot de passe
const validatePasswordStrength = () => {
  let strength = 0
  
  if (form.password.length >= 8) strength++
  if (/[A-Z]/.test(form.password)) strength++
  if (/[0-9]/.test(form.password)) strength++
  if (/[^A-Za-z0-9]/.test(form.password)) strength++
  
  // Limiter à 3 niveaux pour l'affichage
  passwordStrength.value = Math.min(strength, 3)
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

const toggleConfirmPasswordVisibility = () => {
  showConfirmPassword.value = !showConfirmPassword.value
}

const loginWithGoogle = () => {
  console.log('Connexion avec Google')
  // Intégration OAuth ici
}

const loginWithFacebook = () => {
  console.log('Connexion avec Facebook')
  // Intégration OAuth ici
}

// Soumission du formulaire
const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }

  isSubmitting.value = true

  try {
    // Simulation d'un appel API
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    console.log('Inscription réussie:', {
      name: form.name,
      email: form.email,
      // Ne pas logger le mot de passe en production
    })
    
    // Redirection après succès
    router.push('/login')
    
  } catch (error) {
    console.error('Erreur d\'inscription:', error)
    errors.submit = 'Une erreur est survenue. Veuillez réessayer.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
/* Styles spécifiques au composant */
.icon-xs { font-size: 16px; }
.icon-sm { font-size: 20px; }
.icon-md { font-size: 24px; }
.icon-lg { font-size: 28px; }
.icon-xl { font-size: 32px; }

.form-input:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-primary {
  transition: all 0.2s ease-in-out;
}

.btn-primary:hover:not(:disabled) {
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.material-symbols-outlined.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>