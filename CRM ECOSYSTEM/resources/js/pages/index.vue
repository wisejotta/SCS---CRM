<script setup>
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg';
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg';
import { themeConfig } from '@themeConfig';
import { emailValidator, requiredValidator } from '@validators';

localStorage.removeItem('lightThemePrimaryColor')

const snackbar = ref({
  active: false,
  text: null,
  color: null,
})

const loading = ref(false)

const form = ref({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  if(form.value.email.trim() == '' || form.value.password.trim() == '') return
  
  loading.value = true
  axios.post('/user-login', form.value)
    .then((d) => {
      localStorage.setItem('user', JSON.stringify(d.data))
      if(d.data.role == 'CUSTOMER') {
          window.location.href = '/customer'
          localStorage.setItem('lightThemePrimaryColor', '#cf2e2e')
      } else {
        window.location.href = '/';
      }
    })
    .catch((e) => {
      snackbar.value.active = true
      snackbar.value.text = 'Email or password incorrect!'
      snackbar.value.color = 'error'
      console.log(e)
    })
    .then(() => loading.value = false)
}

const isPasswordVisible = ref(false)
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- 👉 Top shape -->
      <VImg
        :src="authV1TopShape"
        class="auth-v1-top-shape d-none d-sm-block"
      />

      <!-- 👉 Bottom shape -->
      <VImg
        :src="authV1BottomShape"
        class="auth-v1-bottom-shape d-none d-sm-block"
      />

      <!-- 👉 Auth Card -->
      <VCard
        class="auth-card pa-4"
        max-width="448"
      >
        <VCardText>
          <VRow>
            <!-- email -->
            <VCol cols="12">
              <VTextField
                v-model="form.email"
                :rules="[requiredValidator, emailValidator]"
                label="Email"
                type="email"
              />
            </VCol>

            <!-- password -->
            <VCol cols="12">
              <VTextField
                v-model="form.password"
                label="Password"
                :rules="[requiredValidator]"
                :type="isPasswordVisible ? 'text' : 'password'"
                :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                @click:append-inner="isPasswordVisible = !isPasswordVisible"
              />

              <!-- remember me checkbox -->
              <div class="d-flex align-center justify-space-between flex-wrap mt-2 mb-4">
                <VCheckbox
                  v-model="form.remember"
                  label="Remember me"
                />

                <RouterLink
                  class="text-primary ms-2 mb-1"
                  :to="{ name: 'index' }"
                >
                  Forgot Password?
                </RouterLink>
              </div>

              <!-- login button -->
              <VBtn
                block
                @click="submit"
                :loading="loading"
                :disabled="loading"
              >
                Login
              </VBtn>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </div>
  </div>

  <VSnackbar
    v-model="snackbar.active"
    :color="snackbar.color"
    :timeout="2000"
    location="top end"
    variant="flat"
  >
    {{ snackbar.text }}
  </VSnackbar>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
</route>
