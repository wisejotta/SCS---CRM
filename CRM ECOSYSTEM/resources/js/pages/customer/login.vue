<script setup>
import family from '@images/family.jpg'
import flag from '@images/flag.png'
import { themeConfig } from '@themeConfig'

themeConfig.app.title = 'Visas Canada'
localStorage.setItem('lightThemePrimaryColor', '#cf2e2e')

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

const isPasswordVisible = ref(false)

const submit = () => {
  if(form.value.email.trim() == '' || form.value.password.trim() == '') {
    snackbar.value.active = true
    snackbar.value.text = 'Email and password are required!'
    snackbar.value.color = 'warning'
    return
  }
  
  loading.value = true
  axios.post('/user-login', form.value)
    .then((d) => {
      localStorage.setItem('user', JSON.stringify(d.data))
      if(d.data.role == 'CUSTOMER') {
          window.location.href = '/customer'
      } else {
        window.location.href = '/';
      }
    })
    .catch((e) => {
      snackbar.value.active = true
      snackbar.value.text = 'Email or password incorrect!'
      snackbar.value.color = 'error'
    })
    .then(() => loading.value = false)
}

</script>

<template>
  <VRow
    no-gutters
    class="auth-wrapper"
  >
    <VCol
      md="8"
      class="d-none d-md-flex"
    >
      <div
        class="position-relative auth-bg rounded-lg w-100 ma-8 me-0"
        :style="`background-image: url(${family});opacity: 0.9;`"
      >
        <!-- <div class="d-flex align-center justify-center w-100 h-100">
          <VImg
            max-width="505"
            :src="authV2LoginIllustrationLight"
            class="auth-illustration mt-16 mb-2"
          />
        </div>

        <VImg
          class="auth-footer-mask"
          :src="authV2MaskLight"
        /> -->
      </div>
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-4"
      >
        <VCardText>
          <VImg
            max-width="100"
            :src="flag"
          />
          <h5 class="text-h5 font-weight-semibold mb-1">
            Welcome to your Dashboard.
          </h5>
          <p class="mb-0">
            Please sign-in to your account and start the adventure
          </p>
        </VCardText>
        <VCardText>
          <VForm @submit.prevent="() => {}">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <VTextField
                  v-model="form.email"
                  label="Email"
                  type="email"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  label="Password"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div class="d-flex align-center flex-wrap justify-space-between mt-2 mb-4">
                  <VCheckbox
                    v-model="form.remember"
                    label="Remember me"
                  />
                </div>

                <VBtn
                  block
                  type="submit"
                  color="error"
                  :loading="loading"
                  :disabled="loading"
                  @click="submit"
                >
                  Login
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>

    <VSnackbar
      v-model="snackbar.active"
      :color="snackbar.color"
      :timeout="2000"
      location="top end"
      variant="flat"
    >
      {{ snackbar.text }}
    </VSnackbar>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
</route>
